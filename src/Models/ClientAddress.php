<?php

declare(strict_types=1);

namespace Alfasic\Models;

use Alfasic\Core\Database;
use PDO;

class ClientAddress extends Model
{
    protected static string $table = 'client_addresses';

    public static function getByClientId(int $clientId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM client_addresses WHERE client_id = :client_id AND deleted_at IS NULL ORDER BY is_primary DESC, id ASC");
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
