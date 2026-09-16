<?php

declare(strict_types=1);

namespace Alfasic\Models;

use Alfasic\Core\Database;
use PDO;
use Throwable;

/**
 * Model de Perfis de Acesso & Controle RBAC (Role-Based Access Control)
 * Gerencia grupos de privilégios e permissões granulares por ação no sistema.
 */
class Role extends Model
{
    /**
     * Tabela associada no MySQL
     */
    protected static string $table = 'roles';

    /**
     * Colunas pesquisáveis na busca com LIKE
     */
    protected static array $searchable = ['name', 'slug', 'description'];

    protected static array $fillable = [
        'name', 'slug', 'description', 'is_active',
    ];

    /**
     * Retorna a lista de códigos de permissão associados a um perfil
     * Exemplo de retorno: ['clients.view', 'clients.create', 'products.view']
     */
    public static function getPermissions(int $roleId): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT p.code 
                FROM permissions p
                INNER JOIN role_permissions rp ON rp.permission_id = p.id
                WHERE rp.role_id = :role_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':role_id', $roleId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    /**
     * Sincroniza a lista de permissões de um perfil dentro de uma transação atômica
     */
    public static function syncPermissions(int $roleId, array $permissionCodes): void
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            // 1. Remove permissões anteriores deste perfil
            $stmtDelete = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
            $stmtDelete->bindValue(':role_id', $roleId, PDO::PARAM_INT);
            $stmtDelete->execute();

            // 2. Insere as novas permissões se houver alguma marcada
            if (!empty($permissionCodes)) {
                // Se 'all' estiver marcado, seleciona todos os IDs da tabela permissions
                if (in_array('all', $permissionCodes, true)) {
                    $stmtAll = $pdo->query("SELECT id FROM permissions");
                    $permissionIds = $stmtAll->fetchAll(PDO::FETCH_COLUMN) ?: [];
                } else {
                    $placeholders = implode(', ', array_fill(0, count($permissionCodes), '?'));
                    $stmtPerms = $pdo->prepare("SELECT id FROM permissions WHERE code IN ({$placeholders})");
                    $stmtPerms->execute(array_values($permissionCodes));
                    $permissionIds = $stmtPerms->fetchAll(PDO::FETCH_COLUMN) ?: [];
                }

                if (!empty($permissionIds)) {
                    $stmtInsert = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
                    foreach ($permissionIds as $permId) {
                        $stmtInsert->bindValue(':role_id', $roleId, PDO::PARAM_INT);
                        $stmtInsert->bindValue(':permission_id', (int)$permId, PDO::PARAM_INT);
                        $stmtInsert->execute();
                    }
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Retorna todos os perfis ativos com contagem de usuários e resumo textual de permissões
     */
    public static function allWithStats(): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT 
                    r.id,
                    r.name,
                    r.slug,
                    r.description,
                    r.is_active,
                    r.created_at,
                    COUNT(DISTINCT u.id) AS users_count,
                    GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ', ') AS permissions_summary
                FROM roles r
                LEFT JOIN users u ON u.role_id = r.id AND u.deleted_at IS NULL
                LEFT JOIN role_permissions rp ON rp.role_id = r.id
                LEFT JOIN permissions p ON p.id = rp.permission_id
                WHERE r.deleted_at IS NULL
                GROUP BY r.id
                ORDER BY r.name ASC";

        $stmt = $pdo->query($sql);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($roles as &$role) {
            $role['permissions'] = self::getPermissions((int)$role['id']);
            if (empty($role['permissions_summary'])) {
                $role['permissions_summary'] = 'Nenhuma permissão configurada';
            }
        }
        unset($role);

        return $roles;
    }
}