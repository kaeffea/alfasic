<?php

declare(strict_types=1);

namespace Alfasic\Models;

use Alfasic\Core\Database;
use PDO;
use InvalidArgumentException;

/**
 * Classe Base Abstrata para Mini-ORM Alfasic
 * Fornece operações seguras de CRUD, Soft Delete, Relacionamentos e Consultas Parametrizadas.
 */
abstract class Model
{
    /**
     * Nome da tabela no banco de dados (deve ser sobrescrito nas subclasses)
     */
    protected static string $table = '';

    /**
     * Colunas onde o método search() deve buscar com LIKE
     */
    protected static array $searchable = ['name'];

    /**
     * Campos de controle a serem sempre ignorados no INSERT e UPDATE (Mass Assignment Protection)
     */
    protected static array $guarded = ['csrf_token', 'id', '_method', 'created_at', 'updated_at'];

    /**
     * Lista de campos permitidos para inserção/atualização (se vazio, usa todos exceto $guarded)
     */
    protected static array $fillable = [];

    /**
     * Valida e sanitiza o nome de coluna para prevenir SQL Injection em identificadores
     */
    protected static function sanitizeColumn(string $column): string
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new InvalidArgumentException("Nome de coluna inválido: {$column}");
        }
        return $column;
    }

    /**
     * Retorna todos os registros da tabela
     */
    public static function all(int $limit = 500, int $offset = 0, bool $withTrashed = false): array
    {
        $pdo = Database::getConnection();
        $table = static::$table;

        $where = $withTrashed ? '' : 'WHERE deleted_at IS NULL';
        $sql = "SELECT * FROM `{$table}` {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Busca um registro por Chave Primária (ID)
     */
    public static function find(int $id, bool $withTrashed = false): ?array
    {
        return static::findBy('id', $id, $withTrashed);
    }

    /**
     * Busca um único registro por qualquer coluna
     */
    public static function findBy(string $column, mixed $value, bool $withTrashed = false): ?array
    {
        $column = static::sanitizeColumn($column);
        $pdo = Database::getConnection();
        $table = static::$table;

        $where = $withTrashed ? "`{$column}` = :val" : "`{$column}` = :val AND deleted_at IS NULL";
        $sql = "SELECT * FROM `{$table}` WHERE {$where} LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':val', $value);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca múltiplos registros por condições associativas
     * Exemplo: Model::where(['is_active' => 1, 'role_id' => 2])
     */
    public static function where(array $conditions, int $limit = 500, int $offset = 0, bool $withTrashed = false): array
    {
        $pdo = Database::getConnection();
        $table = static::$table;

        $clauses = [];
        $params = [];

        if (!$withTrashed) {
            $clauses[] = 'deleted_at IS NULL';
        }

        $i = 0;
        foreach ($conditions as $col => $val) {
            $sanitizedCol = static::sanitizeColumn((string)$col);
            if ($val === null) {
                $clauses[] = "`{$sanitizedCol}` IS NULL";
            } else {
                $paramKey = ":w_{$i}";
                $clauses[] = "`{$sanitizedCol}` = {$paramKey}";
                $params[$paramKey] = $val;
                $i++;
            }
        }

        $whereSql = !empty($clauses) ? 'WHERE ' . implode(' AND ', $clauses) : '';
        $sql = "SELECT * FROM `{$table}` {$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Verifica se já existe algum registro com determinado valor (útil para validações de unicidade)
     */
    public static function exists(string $column, mixed $value, ?int $exceptId = null, bool $withTrashed = false): bool
    {
        $column = static::sanitizeColumn($column);
        $pdo = Database::getConnection();
        $table = static::$table;

        $where = ["`{$column}` = :val"];
        if (!$withTrashed) {
            $where[] = "deleted_at IS NULL";
        }
        if ($exceptId !== null) {
            $where[] = "id != :except_id";
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE {$whereSql}";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':val', $value);
        if ($exceptId !== null) {
            $stmt->bindValue(':except_id', $exceptId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return ((int) $stmt->fetchColumn()) > 0;
    }

    /**
     * Busca rápida em múltiplas colunas com filtro LIKE
     */
    public static function search(string $term, int $limit = 500, int $offset = 0, bool $withTrashed = false): array
    {
        $term = trim($term);
        if ($term === '') {
            return static::all($limit, $offset, $withTrashed);
        }

        $pdo = Database::getConnection();
        $table = static::$table;
        $searchable = static::$searchable;

        if (empty($searchable)) {
            $searchable = ['name'];
        }

        $whereClauses = [];
        $params = [];
        foreach ($searchable as $i => $col) {
            $sanitizedCol = static::sanitizeColumn((string)$col);
            $paramKey = ":term_{$i}";
            $whereClauses[] = "`{$sanitizedCol}` LIKE {$paramKey}";
            $params[$paramKey] = "%{$term}%";
        }

        $searchSql = implode(' OR ', $whereClauses);
        $deletedSql = $withTrashed ? '' : 'deleted_at IS NULL AND ';
        $sql = "SELECT * FROM `{$table}` WHERE {$deletedSql}({$searchSql}) ORDER BY id DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * INSERT genérico protegido contra Mass Assignment
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $table = static::$table;

        $cleanData = static::filterData($data);
        if (empty($cleanData)) {
            return 0;
        }

        $columns = array_keys($cleanData);
        $columnsSql = implode(', ', array_map(fn($col) => "`" . static::sanitizeColumn($col) . "`", $columns));
        $placeholders = implode(', ', array_map(fn($col) => ":{$col}", $columns));

        $sql = "INSERT INTO `{$table}` ({$columnsSql}) VALUES ({$placeholders})";
        $stmt = $pdo->prepare($sql);

        foreach ($cleanData as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    /**
     * UPDATE genérico protegido contra Mass Assignment
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $table = static::$table;

        $cleanData = static::filterData($data);
        if (empty($cleanData)) {
            return false;
        }

        $fields = [];
        foreach (array_keys($cleanData) as $key) {
            $sanitized = static::sanitizeColumn($key);
            $fields[] = "`{$sanitized}` = :{$key}";
        }

        $fieldsSql = implode(', ', $fields);
        $sql = "UPDATE `{$table}` SET {$fieldsSql} WHERE id = :id AND deleted_at IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        foreach ($cleanData as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        return $stmt->execute();
    }

    /**
     * Soft delete genérico (marca deleted_at = NOW() e is_active = 0)
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $table = static::$table;

        $sql = "UPDATE `{$table}` SET deleted_at = NOW(), is_active = 0 WHERE id = :id AND deleted_at IS NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Alterna o status is_active entre 1 e 0 (Ativo / Inativo)
     */
    public static function toggleActive(int $id): bool
    {
        $pdo = Database::getConnection();
        $table = static::$table;

        $sql = "UPDATE `{$table}` SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = :id AND deleted_at IS NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Reversão de soft delete (restaura registro para active e limpa deleted_at)
     */
    public static function restore(int $id): bool
    {
        $pdo = Database::getConnection();
        $table = static::$table;

        $sql = "UPDATE `{$table}` SET deleted_at = NULL, is_active = 1 WHERE id = :id AND deleted_at IS NOT NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Hard delete (exclusão física do banco - reservado para limpezas de manutenção)
     */
    public static function forceDelete(int $id): bool
    {
        $pdo = Database::getConnection();
        $table = static::$table;

        $sql = "DELETE FROM `{$table}` WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Conta o total de registros (com suporte a filtros e soft delete)
     */
    public static function count(array $conditions = [], bool $withTrashed = false): int
    {
        $pdo = Database::getConnection();
        $table = static::$table;

        $clauses = [];
        $params = [];

        if (!$withTrashed) {
            $clauses[] = 'deleted_at IS NULL';
        }

        $i = 0;
        foreach ($conditions as $col => $val) {
            $sanitized = static::sanitizeColumn((string)$col);
            if ($val === null) {
                $clauses[] = "`{$sanitized}` IS NULL";
            } else {
                $paramKey = ":c_{$i}";
                $clauses[] = "`{$sanitized}` = {$paramKey}";
                $params[$paramKey] = $val;
                $i++;
            }
        }

        $whereSql = !empty($clauses) ? 'WHERE ' . implode(' AND ', $clauses) : '';
        $sql = "SELECT COUNT(*) FROM `{$table}` {$whereSql}";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Retorna todos os registros que foram soft-deleted (para auditoria e recuperação)
     */
    public static function withTrashed(int $limit = 500, int $offset = 0): array
    {
        return static::all($limit, $offset, true);
    }

    /**
     * Filtra e sanitiza dados para prevenir Mass Assignment
     */
    protected static function filterData(array $data): array
    {
        $clean = [];
        $hasFillable = !empty(static::$fillable);

        foreach ($data as $key => $value) {
            $key = (string)$key;

            // Se tem $fillable definido, só aceita o que está explícito
            if ($hasFillable && !in_array($key, static::$fillable, true)) {
                continue;
            }

            // Se está em $guarded, rejeita
            if (in_array($key, static::$guarded, true)) {
                continue;
            }

            // Sanitiza strings vazias como null
            $clean[$key] = ($value === '') ? null : $value;
        }

        return $clean;
    }
}