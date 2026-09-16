<?php

declare(strict_types=1);

namespace Alfasic\Models;

use Alfasic\Core\Database;
use PDO;

class Client extends Model
{
    protected static string $table = 'clients';
    protected static array $searchable = ['name', 'trade_name', 'document', 'city', 'contact_person', 'email', 'phone'];

    public static function all(int $limit = 500, int $offset = 0, bool $withTrashed = false): array
    {
        $pdo = Database::getConnection();
        $where = $withTrashed ? '1=1' : 'c.deleted_at IS NULL';

        $sql = "SELECT
                    c.*,
                    (SELECT COUNT(*) FROM client_prices WHERE client_id = c.id AND deleted_at IS NULL) AS prices_count
                FROM clients c
                WHERE {$where}
                ORDER BY c.name ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function search(string $term, int $limit = 500, int $offset = 0, bool $withTrashed = false): array
    {
        $pdo = Database::getConnection();
        $where = $withTrashed ? '1=1' : 'c.deleted_at IS NULL';

        $sql = "SELECT
                    c.*,
                    (SELECT COUNT(*) FROM client_prices WHERE client_id = c.id AND deleted_at IS NULL) AS prices_count
                FROM clients c
                WHERE {$where}
                  AND (c.name LIKE :t1 OR c.trade_name LIKE :t2 OR c.document LIKE :t3 OR c.city LIKE :t4)
                ORDER BY c.name ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $likeTerm = "%{$term}%";
        $stmt->bindValue(':t1', $likeTerm);
        $stmt->bindValue(':t2', $likeTerm);
        $stmt->bindValue(':t3', $likeTerm);
        $stmt->bindValue(':t4', $likeTerm);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Preços negociados do cliente. Se não houver linha em client_prices,
     * o cliente paga standard_price (tabela base de products).
     */
    public static function getAppliedPrices(int $clientId): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT
                    p.id AS product_id,
                    p.name AS product_name,
                    p.usage_segment,
                    p.capacity,
                    p.unit,
                    p.standard_price AS base_price,
                    cp.applied_price AS applied_price,
                    COALESCE(cp.freight_price, 0.0) AS freight_price,
                    COALESCE(cp.rental_price, 0.0) AS rental_price,
                    cp.notes,
                    cp.updated_at AS applied_at,
                    p.product_type AS product_type
                FROM products p
                INNER JOIN client_prices cp ON cp.product_id = p.id AND cp.client_id = :client_id AND cp.deleted_at IS NULL
                WHERE p.deleted_at IS NULL
                ORDER BY p.name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();

        $sql = "INSERT INTO clients (
                    name, trade_name, document, state_registration, municipal_registration, client_type,
                    contact_person, phone, mobile, email, address, address_number,
                    address_complement, neighborhood, city, state, zip_code,
                    payment_terms, billing_method, credit_limit, has_rental_charge, notes
                ) VALUES (
                    :name, :trade_name, :document, :state_registration, :municipal_registration, :client_type,
                    :contact_person, :phone, :mobile, :email, :address, :address_number,
                    :address_complement, :neighborhood, :city, :state, :zip_code,
                    :payment_terms, :billing_method, :credit_limit, :has_rental_charge, :notes
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => trim($data['name'] ?? ''),
            ':trade_name' => trim($data['trade_name'] ?? '') ?: null,
            ':document' => trim($data['document'] ?? '') ?: null,
            ':state_registration' => trim($data['state_registration'] ?? '') ?: null,
            ':municipal_registration' => trim($data['municipal_registration'] ?? '') ?: null,
            ':client_type' => $data['client_type'] ?? 'company',
            ':contact_person' => trim($data['contact_person'] ?? '') ?: null,
            ':phone' => trim($data['phone'] ?? '') ?: null,
            ':mobile' => trim($data['mobile'] ?? '') ?: null,
            ':email' => trim($data['email'] ?? '') ?: null,
            ':address' => trim($data['address'] ?? '') ?: null,
            ':address_number' => trim($data['address_number'] ?? '') ?: null,
            ':address_complement' => trim($data['address_complement'] ?? '') ?: null,
            ':neighborhood' => trim($data['neighborhood'] ?? '') ?: null,
            ':city' => trim($data['city'] ?? 'São Miguel dos Campos'),
            ':state' => trim($data['state'] ?? 'AL'),
            ':zip_code' => trim($data['zip_code'] ?? '') ?: null,
            ':payment_terms' => trim($data['payment_terms'] ?? 'A Vista'),
            ':billing_method' => trim($data['billing_method'] ?? 'Boleto'),
            ':credit_limit' => isset($data['credit_limit']) && $data['credit_limit'] !== '' ? (float) $data['credit_limit'] : 0.00,
            ':has_rental_charge' => !empty($data['has_rental_charge']) ? 1 : 0,
            ':notes' => trim($data['notes'] ?? '') ?: null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();

        $sql = "UPDATE clients SET
                    name = :name,
                    trade_name = :trade_name,
                    document = :document,
                    state_registration = :state_registration,
                    municipal_registration = :municipal_registration,
                    client_type = :client_type,
                    contact_person = :contact_person,
                    phone = :phone,
                    mobile = :mobile,
                    email = :email,
                    address = :address,
                    address_number = :address_number,
                    address_complement = :address_complement,
                    neighborhood = :neighborhood,
                    city = :city,
                    state = :state,
                    zip_code = :zip_code,
                    payment_terms = :payment_terms,
                    billing_method = :billing_method,
                    credit_limit = :credit_limit,
                    has_rental_charge = :has_rental_charge,
                    notes = :notes
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':name' => trim($data['name'] ?? ''),
            ':trade_name' => trim($data['trade_name'] ?? '') ?: null,
            ':document' => trim($data['document'] ?? '') ?: null,
            ':state_registration' => trim($data['state_registration'] ?? '') ?: null,
            ':municipal_registration' => trim($data['municipal_registration'] ?? '') ?: null,
            ':client_type' => $data['client_type'] ?? 'company',
            ':contact_person' => trim($data['contact_person'] ?? '') ?: null,
            ':phone' => trim($data['phone'] ?? '') ?: null,
            ':mobile' => trim($data['mobile'] ?? '') ?: null,
            ':email' => trim($data['email'] ?? '') ?: null,
            ':address' => trim($data['address'] ?? '') ?: null,
            ':address_number' => trim($data['address_number'] ?? '') ?: null,
            ':address_complement' => trim($data['address_complement'] ?? '') ?: null,
            ':neighborhood' => trim($data['neighborhood'] ?? '') ?: null,
            ':city' => trim($data['city'] ?? 'São Miguel dos Campos'),
            ':state' => trim($data['state'] ?? 'AL'),
            ':zip_code' => trim($data['zip_code'] ?? '') ?: null,
            ':payment_terms' => trim($data['payment_terms'] ?? 'A Vista'),
            ':billing_method' => trim($data['billing_method'] ?? 'Boleto'),
            ':credit_limit' => isset($data['credit_limit']) && $data['credit_limit'] !== '' ? (float) $data['credit_limit'] : 0.00,
            ':has_rental_charge' => !empty($data['has_rental_charge']) ? 1 : 0,
            ':notes' => trim($data['notes'] ?? '') ?: null,
        ]);
    }

    public static function savePrice(
        int $clientId,
        int $productId,
        float $price,
        float $shippingFee = 0.0,
        float $rentalFee = 0.0
    ): bool {
        $pdo = Database::getConnection();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $sql = "INSERT INTO client_prices (client_id, product_id, applied_price, freight_price, rental_price)
                    VALUES (:client_id, :product_id, :price, :shipping_fee, :rental_fee)
                    ON DUPLICATE KEY UPDATE
                        applied_price = VALUES(applied_price),
                        freight_price = VALUES(freight_price),
                        rental_price = VALUES(rental_price)";
        } else {
            $sql = "INSERT INTO client_prices (client_id, product_id, applied_price, freight_price, rental_price, updated_at)
                    VALUES (:client_id, :product_id, :price, :shipping_fee, :rental_fee, datetime('now', 'localtime'))
                    ON CONFLICT(client_id, product_id) DO UPDATE SET
                        applied_price = excluded.applied_price,
                        freight_price = excluded.freight_price,
                        rental_price = excluded.rental_price,
                        updated_at = datetime('now', 'localtime')";
        }

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':client_id' => $clientId,
            ':product_id' => $productId,
            ':price' => $price,
            ':shipping_fee' => $shippingFee,
            ':rental_fee' => $rentalFee,
        ]);
    }

    public static function deletePrice(int $clientId, int $productId): bool
    {
        $pdo = Database::getConnection();
        $sql = "DELETE FROM client_prices WHERE client_id = :client_id AND product_id = :product_id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':client_id' => $clientId,
            ':product_id' => $productId,
        ]);
    }



    public static function getContacts(int $clientId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM client_contacts WHERE client_id = :client_id AND deleted_at IS NULL ORDER BY id ASC");
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function getAddresses(int $clientId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM client_addresses WHERE client_id = :client_id AND deleted_at IS NULL ORDER BY is_primary DESC, id ASC");
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function getStats(int $clientId): array
    {
        $pdo = Database::getConnection();

        $stmtPrices = $pdo->prepare("SELECT COUNT(*) FROM client_prices WHERE client_id = :id AND deleted_at IS NULL");
        $stmtPrices->execute([':id' => $clientId]);
        $pricesCount = (int) $stmtPrices->fetchColumn();

        return [
            'prices' => $pricesCount,
        ];
    }
}