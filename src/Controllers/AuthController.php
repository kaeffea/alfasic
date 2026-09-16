<?php

declare(strict_types=1);

namespace Alfasic\Controllers;

use Alfasic\Core\Csrf;
use Alfasic\Core\View;
use Alfasic\Models\User;

/**
 * Controller de Autenticação & Sessões Seguras
 */
class AuthController
{
    /**
     * Exibe o formulário de login (URL sempre limpa com mensagens Flash)
     */
    public function loginForm(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Se já está logado, vai direto para o sistema
        if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            header('Location: /clients');
            exit;
        }

        // Recupera mensagens flash de erro ou logout e limpa imediatamente da sessão
        $error = $_SESSION['flash_error'] ?? null;
        $loggedOut = !empty($_SESSION['flash_logged_out']);
        unset($_SESSION['flash_error'], $_SESSION['flash_logged_out']);

        View::render('auth/login', [
            'error' => $error,
            'loggedOut' => $loggedOut,
        ], null);
    }

    /**
     * Processa a autenticação de credenciais
     */
    public function login(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'csrf_invalido';
            header('Location: /login');
            exit;
        }

        // Proteção contra ataques de força bruta (Rate Limiting por Sessão/IP)
        $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'first_attempt' => time()];
        if ($attempts['count'] >= 5 && (time() - $attempts['first_attempt']) < 900) {
            $_SESSION['flash_error'] = 'muitas_tentativas';
            header('Location: /login');
            exit;
        }

        $username = trim($_POST['username'] ?? $_POST['login'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $_SESSION['flash_error'] = 'campos_vazios';
            header('Location: /login');
            exit;
        }

        $user = User::authenticate($username, $password);

        if (!$user) {
            // Incrementa contador de tentativas falhas
            if ($attempts['count'] === 0 || (time() - $attempts['first_attempt']) >= 900) {
                $_SESSION['login_attempts'] = ['count' => 1, 'first_attempt' => time()];
            } else {
                $_SESSION['login_attempts']['count']++;
            }

            $_SESSION['flash_error'] = 'credenciais_invalidas';
            header('Location: /login');
            exit;
        }

        // Senha OK: limpa contador e regenera o Session ID (anti-fixation)
        unset($_SESSION['login_attempts'], $_SESSION['flash_error'], $_SESSION['csrf_token']);
        session_regenerate_id(true);

        $userId = (int) $user['id'];

        // Segundo fator: dispositivo confiavel dispensa o codigo neste navegador
        $trustedUser = self::checkDeviceCookie($userId);
        if ($trustedUser !== null) {
            self::completeLogin($trustedUser);
            return;
        }

        // Segundo fator sempre por e-mail (sem escolha de canal)
        if (!self::startEmailChallenge($user)) {
            $_SESSION['flash_error'] = 'email_falhou';
            header('Location: /login');
            exit;
        }
        header('Location: /2fa');
        exit;
    }

    /**
     * Tela do segundo fator (codigo enviado ao e-mail corporativo).
     */
    public function twofaForm(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            header('Location: /clients');
            exit;
        }

        $pending = $_SESSION['pending_2fa'] ?? null;
        if (empty($pending['user_id'])) {
            unset($_SESSION['pending_2fa']);
            $_SESSION['flash_error'] = 'sessao_expirada';
            header('Location: /login');
            exit;
        }

        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        $user = User::find((int) $pending['user_id']);
        if (!$user || empty($pending['code_hash'])) {
            unset($_SESSION['pending_2fa']);
            $_SESSION['flash_error'] = 'sessao_expirada';
            header('Location: /login');
            exit;
        }

        View::render('auth/twofa', [
            'error' => $error,
            'masked_email' => self::maskEmail($user['email'] ?? ''),
        ], null);
    }

    /**
     * Verifica o codigo do e-mail (6 digitos, 10 min, 5 tentativas).
     */
    public function twofaVerify(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'csrf_invalido';
            header('Location: /2fa');
            exit;
        }

        $pending = $_SESSION['pending_2fa'] ?? null;
        if (empty($pending['user_id']) || empty($pending['code_hash'])) {
            unset($_SESSION['pending_2fa']);
            $_SESSION['flash_error'] = 'sessao_expirada';
            header('Location: /login');
            exit;
        }

        $pending['attempts'] = (int) ($pending['attempts'] ?? 0) + 1;
        $_SESSION['pending_2fa'] = $pending;
        if ($pending['attempts'] > 5 || time() > (int) ($pending['expires_at'] ?? 0)) {
            unset($_SESSION['pending_2fa']);
            $_SESSION['flash_error'] = 'codigo_invalido';
            header('Location: /login');
            exit;
        }

        $code = preg_replace('/\D/', '', $_POST['code'] ?? '') ?? '';
        if (!password_verify($code, $pending['code_hash'])) {
            $_SESSION['flash_error'] = 'codigo_invalido';
            header('Location: /2fa');
            exit;
        }

        $userId = (int) $pending['user_id'];
        $user = User::authenticate_by_id($userId);
        if (!$user) {
            unset($_SESSION['pending_2fa']);
            header('Location: /login');
            exit;
        }

        if (!empty($_POST['trust_device'])) {
            self::setDeviceCookie($userId);
        }
        unset($_SESSION['pending_2fa']);
        self::completeLogin($user);
    }

    /**
     * Reenvia o codigo (throttle 60s, zera tentativas).
     */
    public function twofaResend(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            header('Location: /login');
            exit;
        }

        $pending = $_SESSION['pending_2fa'] ?? null;
        if (empty($pending['user_id'])) {
            header('Location: /login');
            exit;
        }

        if (time() < (int) ($pending['resend_at'] ?? 0)) {
            header('Location: /2fa');
            exit;
        }

        $user = User::find((int) $pending['user_id']);
        if (!$user || !self::startEmailChallenge($user)) {
            $_SESSION['flash_error'] = 'email_falhou';
            header('Location: /login');
            exit;
        }
        header('Location: /2fa');
        exit;
    }

    /**
     * Gera o desafio por e-mail (codigo -> hash na sessao, texto so no e-mail).
     */
    private static function startEmailChallenge(array $user): bool
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $result = \Alfasic\Core\Mailer::sendLoginCode(
            $user['email'] ?? '',
            $user['username'] ?? 'usuário',
            $code,
            10,
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null
        );
        if (!$result['ok']) {
            $_SESSION['flash_error'] = 'email_falhou';
            return false;
        }
        $_SESSION['pending_2fa'] = [
            'user_id' => (int) $user['id'],
            'code_hash' => password_hash($code, PASSWORD_DEFAULT),
            'expires_at' => time() + 600,
            'resend_at' => time() + 60,
            'attempts' => 0,
            'created_at' => time(),
        ];
        return true;
    }

    private static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return 'seu e-mail';
        }
        $name = $parts[0];
        $shown = strlen($name) <= 3 ? str_repeat('*', strlen($name)) : substr($name, 0, 3) . str_repeat('*', min(strlen($name) - 3, 5));
        return $shown . '@' . $parts[1];
    }

    private static function completeLogin(array $user): void
    {
        unset($_SESSION['csrf_token']);
        session_regenerate_id(true);

        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_role'] = $user['role_name'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        $redirect = \Alfasic\Core\Validator::safeRedirectPath($_SESSION['redirect_after_login'] ?? '/clients', '/clients');
        unset($_SESSION['redirect_after_login']);

        header("Location: {$redirect}");
        exit;
    }

    private static function checkDeviceCookie(int $userId): ?array
    {
        $raw = $_COOKIE['alfasic_device'] ?? '';
        if (!str_contains($raw, ':')) {
            return null;
        }
        [$selector, $validator] = explode(':', $raw, 2);
        if (!ctype_xdigit($selector . $validator)) {
            return null;
        }
        $matchedId = User::verifyTrustedDevice($selector, $validator);
        if ($matchedId === null || $matchedId !== $userId) {
            return null;
        }
        if ($rotated = User::takeRotatedValidator()) {
            self::writeDeviceCookie($rotated[0], $rotated[1]);
        }
        return User::authenticate_by_id($userId);
    }

    private static function setDeviceCookie(int $userId): void
    {
        [$selector, $validator] = User::trustDevice(
            $userId,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null
        );
        self::writeDeviceCookie($selector, $validator);
    }

    private static function writeDeviceCookie(string $selector, string $validator): void
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        setcookie('alfasic_device', $selector . ':' . $validator, [
            'expires' => time() + 30 * 86400,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Encerra a sessão do usuário (Logout)
     */
    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!\Alfasic\Core\Csrf::validate($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo 'Erro de validação de segurança CSRF.';
            return;
        }

        $_SESSION = [
            'flash_logged_out' => true,
        ];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        header('Location: /login');
        exit;
    }
}
