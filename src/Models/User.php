<?php

declare(strict_types=1);

namespace Alfasic\Models;

use Alfasic\Core\Database;
use PDO;

/**
 * Model de Usuários & Autenticação Segura
 * Gerencia credenciais hasheadas, vínculos institucionais e permissões ativas.
 */
class User extends Model
{
    /**
     * Tabela associada no MySQL
     */
    protected static string $table = 'users';

    /**
     * Colunas pesquisáveis na busca rápida com LIKE
     */
    protected static array $searchable = ['username', 'email'];

    protected static array $fillable = [
        'username', 'email', 'password_hash', 'role_id', 'employee_id', 'is_active',
    ];

    /**
     * Retorna a lista de usuários com dados agregados de Perfil e Funcionário
     */
    public static function allWithDetails(int $limit = 500, int $offset = 0): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT 
                    u.id,
                    u.username,
                    u.email,
                    u.role_id,
                    r.name AS role_name,
                    r.slug AS role_slug,
                    u.employee_id,
                    e.name AS employee_name,
                    e.role_title AS employee_role,
                    u.is_active,
                    u.last_login_at,
                    (SELECT COUNT(*) FROM trusted_devices td WHERE td.user_id = u.id AND td.expires_at > NOW()) AS trusted_devices,
                    u.created_at
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                LEFT JOIN employees e ON e.id = u.employee_id AND e.deleted_at IS NULL
                WHERE u.deleted_at IS NULL
                ORDER BY u.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Busca um usuário detalhado pelo ID
     */
    public static function findWithDetails(int $id): ?array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT 
                    u.id,
                    u.username,
                    u.email,
                    u.role_id,
                    r.name AS role_name,
                    r.slug AS role_slug,
                    u.employee_id,
                    e.name AS employee_name,
                    e.role_title AS employee_role,
                    u.is_active,
                    u.last_login_at,
                    (SELECT COUNT(*) FROM trusted_devices td WHERE td.user_id = u.id AND td.expires_at > NOW()) AS trusted_devices,
                    u.created_at
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                LEFT JOIN employees e ON e.id = u.employee_id AND e.deleted_at IS NULL
                WHERE u.id = :id AND u.deleted_at IS NULL
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Cria um novo usuário criptografando a senha com custo seguro (Bcrypt / Argon2id)
     */
    public static function createWithPassword(array $data, string $plainPassword): int
    {
        $data['password_hash'] = password_hash($plainPassword, PASSWORD_DEFAULT, ['cost' => 12]);
        return self::create($data);
    }

    /**
     * Atualiza dados de um usuário e opcionalmente sua senha
     */
    public static function updateWithPassword(int $id, array $data, ?string $plainPassword = null): bool
    {
        if (!empty($plainPassword)) {
            $data['password_hash'] = password_hash($plainPassword, PASSWORD_DEFAULT, ['cost' => 12]);
        }
        return self::update($id, $data);
    }

    /**
     * Autentica um usuário por Username ou E-mail e Senha
     * Atualiza o timestamp de último login e carrega as permissões vigentes.
     */
    public static function authenticate(string $usernameOrEmail, string $password): ?array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT 
                    u.*,
                    r.name AS role_name,
                    r.slug AS role_slug,
                    r.is_active AS role_is_active
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE (u.username = :login1 OR u.email = :login2)
                  AND u.deleted_at IS NULL
                LIMIT 1";

        $cleanLogin = trim($usernameOrEmail);
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':login1', $cleanLogin);
        $stmt->bindValue(':login2', $cleanLogin);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return null; // Usuário não existe
        }

        // Verifica se a conta do usuário ou o perfil estão inativos
        if (empty($user['is_active']) || empty($user['role_is_active'])) {
            return null; // Acesso suspenso
        }

        // Validação criptográfica com resistência a timing attacks
        if (!password_verify($password, $user['password_hash'])) {
            return null; // Senha incorreta
        }

        // Re-hash automático se o algoritmo padrão do PHP for atualizado
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT, ['cost' => 12])) {
            $newHash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
            $stmtRehash = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            $stmtRehash->execute([':hash' => $newHash, ':id' => $user['id']]);
        }

        // Atualiza a data e hora do último login
        $stmtLogin = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $stmtLogin->execute([':id' => $user['id']]);

        // Carrega a matriz de permissões ativas do perfil
        $user['permissions'] = Role::getPermissions((int)$user['role_id']);

        // Remove segredos da memória antes de retornar (nunca vao para a sessao)
        unset($user['password_hash']);

        return $user;
    }

    // -------------------------------------------------------------------------
    // Segundo fator via e-mail (OTP) + dispositivos confiaveis (30 dias)
    // -------------------------------------------------------------------------

    /**
     * Registra dispositivo confiavel. Retorna [selector, validator puro] para o cookie.
     *
     * @return array{0:string,1:string}
     */
    public static function trustDevice(int $id, ?string $userAgent, ?string $ip, int $days = 30): array
    {
        $pdo = Database::getConnection();
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + $days * 86400);
        $stmt = $pdo->prepare("INSERT INTO trusted_devices (user_id, selector, validator_hash, user_agent, ip_address, expires_at) VALUES (:uid, :sel, :h, :ua, :ip, :exp)");
        $stmt->execute([
            ':uid' => $id,
            ':sel' => $selector,
            ':h' => password_hash($validator, PASSWORD_DEFAULT),
            ':ua' => $userAgent ? substr($userAgent, 0, 255) : null,
            ':ip' => $ip ? substr($ip, 0, 45) : null,
            ':exp' => $expires,
        ]);
        return [$selector, $validator];
    }

    /**
     * Valida o cookie de dispositivo (com rotacao do validador). Retorna user_id ou null.
     */
    public static function verifyTrustedDevice(string $selector, string $validator): ?int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, user_id, selector, validator_hash FROM trusted_devices WHERE selector = :sel AND expires_at > NOW() LIMIT 1");
        $stmt->execute([':sel' => $selector]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !password_verify($validator, $row['validator_hash'])) {
            return null;
        }
        // Confirma que o usuario segue ativo
        $stmtU = $pdo->prepare("SELECT id FROM users WHERE id = :id AND is_active = 1 AND deleted_at IS NULL LIMIT 1");
        $stmtU->execute([':id' => $row['user_id']]);
        if (!$stmtU->fetchColumn()) {
            return null;
        }
        // Rotaciona o validador a cada uso
        $newValidator = bin2hex(random_bytes(32));
        $upd = $pdo->prepare("UPDATE trusted_devices SET validator_hash = :h, last_used_at = NOW() WHERE id = :tid");
        $upd->execute([':h' => password_hash($newValidator, PASSWORD_DEFAULT), ':tid' => $row['id']]);
        // Devolve o novo validador via referencia estatica para o controller regravar o cookie
        self::$rotatedValidator = [$row['selector'], $newValidator];
        return (int) $row['user_id'];
    }

    private static ?array $rotatedValidator = null;

    public static function takeRotatedValidator(): ?array
    {
        $v = self::$rotatedValidator;
        self::$rotatedValidator = null;
        return $v;
    }

    public static function revokeTrustedDevices(int $id): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM trusted_devices WHERE user_id = :id")->execute([':id' => $id]);
    }

    /**
     * Recarrega o usuario pelo ID apos o segundo fator (sem senha).
     * Retorna null se inativo, suspenso ou sem perfil valido.
     */
    public static function authenticate_by_id(int $id): ?array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT
                    u.*,
                    r.name AS role_name,
                    r.slug AS role_slug,
                    r.is_active AS role_is_active
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.id = :id AND u.deleted_at IS NULL
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || empty($user['is_active']) || empty($user['role_is_active'])) {
            return null;
        }

        $stmtLogin = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $stmtLogin->execute([':id' => $id]);

        $user['permissions'] = Role::getPermissions((int) $user['role_id']);
        unset($user['password_hash']);

        return $user;
    }

    /**
     * Verifica se um usuário possui determinada permissão
     */
    public static function hasPermission(array $user, string $permissionCode): bool
    {
        if (empty($user)) {
            return false;
        }

        // Administrador Geral tem acesso irrestrito a todas as ações
        if (($user['role_slug'] ?? '') === 'admin' || in_array('all', $user['permissions'] ?? [], true)) {
            return true;
        }

        return in_array($permissionCode, $user['permissions'] ?? [], true);
    }
}
