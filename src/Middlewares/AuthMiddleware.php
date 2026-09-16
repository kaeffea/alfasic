<?php

declare(strict_types=1);

namespace Alfasic\Middlewares;

/**
 * Middleware de Autenticação
 * Garante que apenas usuários com sessão ativa possam acessar a rota.
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function handle(array $params = []): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Expira sessão por ociosidade (8h) ou tempo absoluto (24h)
        if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            $now = time();
            $lastActivity = (int) ($_SESSION['last_activity'] ?? $now);
            $loginTime = (int) ($_SESSION['login_time'] ?? $now);
            if (($now - $lastActivity) > 8 * 3600 || ($now - $loginTime) > 24 * 3600) {
                $_SESSION = ['flash_error' => 'sessao_expirada'];
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }
            } else {
                $_SESSION['last_activity'] = $now;
            }
        }

        // Se não houver usuário na sessão
        if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));

            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Sessão expirada. Faça login novamente.', 'redirect' => '/login']);
                return false;
            }

            // Salva a URL original para redirecionar após o login (só interno)
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $path = parse_url($uri, PHP_URL_PATH) ?? '/';
            if ($path !== '/login' && $path !== '/logout') {
                $_SESSION['redirect_after_login'] = \Alfasic\Core\Validator::safeRedirectPath($path, '/clients');
            }

            header('Location: /login');
            exit;
        }

        return true;
    }
}
