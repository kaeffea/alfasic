<?php

declare(strict_types=1);

namespace Alfasic\Models;

use PDO;
use Alfasic\Core\Database;

class Supplier extends Model
{
    protected static string $table = 'suppliers';

    protected static array $searchable = [
        'name',
        'trade_name',
        'document',
        'contact_person',
        'city',
        'phone',
        'email',
        'supplier_type'
    ];

    protected static array $fillable = [
        'name', 'trade_name', 'document', 'state_registration', 'contact_person',
        'phone', 'mobile', 'email', 'address', 'address_number', 'address_complement',
        'neighborhood', 'city', 'state', 'zip_code', 'supplier_type', 'notes', 'is_active',
    ];

    /**
     * Alterna o status do fornecedor entre Ativo e Inativo
     */
    public static function toggleActive(int $id): bool
    {
        $pdo = Database::getConnection();
        $sql = "UPDATE suppliers SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = :id AND deleted_at IS NULL";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Retorna os contatos vinculados ao fornecedor
     */
    public static function getContacts(int $supplierId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT *, COALESCE(role_title, role) AS role_title, COALESCE(role_title, role) AS role FROM supplier_contacts WHERE supplier_id = :id AND deleted_at IS NULL ORDER BY is_primary DESC, name ASC");
        $stmt->execute([':id' => $supplierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Salva ou atualiza um contato vinculado ao fornecedor
     */
    public static function saveContact(int $supplierId, array $data, ?int $contactId = null): int|bool
    {
        $pdo = Database::getConnection();

        $roleTitle = trim($data['role_title'] ?? $data['role'] ?? '') ?: null;

        if ($contactId) {
            $sql = "UPDATE supplier_contacts SET
                        name = :name,
                        role = :role,
                        role_title = :role_title,
                        department = :department,
                        phone = :phone,
                        email = :email,
                        is_primary = :is_primary
                    WHERE id = :id AND supplier_id = :supplier_id";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $contactId,
                ':supplier_id' => $supplierId,
                ':name' => trim($data['name'] ?? ''),
                ':role' => $roleTitle,
                ':role_title' => $roleTitle,
                ':department' => trim($data['department'] ?? '') ?: null,
                ':phone' => trim($data['phone'] ?? '') ?: null,
                ':email' => trim($data['email'] ?? '') ?: null,
                ':is_primary' => !empty($data['is_primary']) ? 1 : 0,
            ]);
        }

        $sql = "INSERT INTO supplier_contacts (supplier_id, name, role, role_title, department, phone, email, is_primary)
                VALUES (:supplier_id, :name, :role, :role_title, :department, :phone, :email, :is_primary)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':supplier_id' => $supplierId,
            ':name' => trim($data['name'] ?? ''),
            ':role' => $roleTitle,
            ':role_title' => $roleTitle,
            ':department' => trim($data['department'] ?? '') ?: null,
            ':phone' => trim($data['phone'] ?? '') ?: null,
            ':email' => trim($data['email'] ?? '') ?: null,
            ':is_primary' => !empty($data['is_primary']) ? 1 : 0,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Deleta um contato vinculado ao fornecedor
     */
    public static function deleteContact(int $contactId, int $supplierId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM supplier_contacts WHERE id = :id AND supplier_id = :supplier_id");
        return $stmt->execute([':id' => $contactId, ':supplier_id' => $supplierId]);
    }
}
