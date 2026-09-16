<?php

declare(strict_types=1);

namespace Alfasic\Models;

use Alfasic\Core\Database;
use PDO;

/**
 * Model de Tipos de Cilindro (vasilhames de alta pressão).
 * Relação 1:1 com produto do tipo gás via products.cylinder_type_id (UNIQUE).
 */
class Cylinder extends Model
{
    protected static string $table = 'cylinder_types';

    protected static array $searchable = ['name', 'code', 'notes'];

    protected static array $fillable = [
        'name', 'code', 'capacity', 'unit', 'working_pressure_bar',
        'tare_weight_kg', 'replacement_value', 'notes', 'is_active',
    ];

    /**
     * Lista com o produto gás vinculado (se houver).
     */
    public static function allWithProduct(int $limit = 500, int $offset = 0): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT t.*, p.id AS product_id, p.name AS product_name
                FROM cylinder_types t
                LEFT JOIN products p ON p.cylinder_type_id = t.id AND p.deleted_at IS NULL
                WHERE t.deleted_at IS NULL
                ORDER BY t.name ASC
                LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Gases sem tipo vinculado (candidatos ao vínculo 1:1).
     */
    public static function unlinkedGases(?int $exceptProductId = null): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT id, name, capacity, unit, standard_price FROM products
                WHERE product_type = 'gas' AND cylinder_type_id IS NULL AND deleted_at IS NULL";
        $params = [];
        if ($exceptProductId !== null && $exceptProductId > 0) {
            $sql .= " OR id = :except";
            $params[':except'] = $exceptProductId;
        }
        $sql .= " ORDER BY name ASC LIMIT 300";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
