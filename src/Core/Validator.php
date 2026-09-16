<?php

declare(strict_types=1);

namespace Alfasic\Core;

use Alfasic\Core\Database;
use PDO;

/**
 * Motor Central de Validações & Sanitização de Dados do Alfasic
 * Regras matemáticas oficiais da Receita Federal (CPF/CNPJ), telefones, e-mails e integridade.
 */
class Validator
{
    /**
     * Remove todos os caracteres não numéricos de uma string
     */
    public static function cleanDigits(?string $val): string
    {
        if ($val === null) {
            return '';
        }
        return (string)preg_replace('/\D/', '', $val);
    }

    /**
     * Converte strings monetárias ('R$ 1.250,50' ou '1250,50' ou '1250.50') em float limpo
     */
    public static function cleanMoney(mixed $val): float
    {
        if (is_numeric($val)) {
            return (float)$val;
        }
        if (!is_string($val) || trim($val) === '') {
            return 0.0;
        }

        $clean = trim($val);
        $clean = (string)preg_replace('/[^\d,\.]/', '', $clean);

        if (str_contains($clean, ',') && str_contains($clean, '.')) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (str_contains($clean, ',')) {
            $clean = str_replace(',', '.', $clean);
        }

        return (float)$clean;
    }

    /**
     * Valida e normaliza datas no formato brasileiro (d/m/Y) ou ISO (Y-m-d)
     * Retorna a data no formato ISO 'Y-m-d' ou null se inválida
     */
    public static function cleanDate(?string $date): ?string
    {
        if ($date === null || trim($date) === '') {
            return null;
        }

        $date = trim($date);

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $m)) {
            $day = (int)$m[1];
            $month = (int)$m[2];
            $year = (int)$m[3];

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) {
            $year = (int)$m[1];
            $month = (int)$m[2];
            $day = (int)$m[3];

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
            return null;
        }

        return null;
    }

    /**
     * Valida se um CPF é matematicamente válido com base no algoritmo oficial dos 2 dígitos verificadores
     */
    public static function validateCpf(string $cpf): bool
    {
        $cpf = self::cleanDigits($cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int)$cpf[$i]) * (10 - $i);
        }
        $rem = $sum % 11;
        $digit1 = $rem < 2 ? 0 : 11 - $rem;

        if ((int)$cpf[9] !== $digit1) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ((int)$cpf[$i]) * (11 - $i);
        }
        $rem = $sum % 11;
        $digit2 = $rem < 2 ? 0 : 11 - $rem;

        return (int)$cpf[10] === $digit2;
    }

    /**
     * Valida se um CNPJ é matematicamente válido com base no algoritmo oficial dos 2 dígitos verificadores
     */
    public static function validateCnpj(string $cnpj): bool
    {
        $cnpj = self::cleanDigits($cnpj);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += ((int)$cnpj[$i]) * $w1[$i];
        }
        $rem = $sum % 11;
        $digit1 = $rem < 2 ? 0 : 11 - $rem;

        if ((int)$cnpj[12] !== $digit1) {
            return false;
        }

        $w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += ((int)$cnpj[$i]) * $w2[$i];
        }
        $rem = $sum % 11;
        $digit2 = $rem < 2 ? 0 : 11 - $rem;

        return (int)$cnpj[13] === $digit2;
    }

    /**
     * Valida um documento genérico (se preenchido, deve ser CPF ou CNPJ válido)
     */
    public static function validateDocument(?string $doc, bool $required = false): bool
    {
        $clean = self::cleanDigits($doc);

        if ($clean === '') {
            return !$required;
        }

        if (strlen($clean) === 11) {
            return self::validateCpf($clean);
        }

        if (strlen($clean) === 14) {
            return self::validateCnpj($clean);
        }

        return false;
    }

    /**
     * Valida se um e-mail possui formato e sintaxe RFC válidos
     */
    public static function validateEmail(?string $email, bool $required = false): bool
    {
        $email = trim((string)$email);

        if ($email === '') {
            return !$required;
        }

        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valida se um telefone/WhatsApp possui formato aceitável (10 ou 11 dígitos numéricos com DDD válido)
     */
    public static function validatePhone(?string $phone, bool $required = false): bool
    {
        $clean = self::cleanDigits($phone);

        if ($clean === '') {
            return !$required;
        }

        $len = strlen($clean);
        if ($len !== 10 && $len !== 11) {
            return false;
        }

        $ddd = (int)substr($clean, 0, 2);
        return $ddd >= 11 && $ddd <= 99;
    }

    /**
     * Checa se um valor é único na tabela do MySQL (ignora registros com soft delete)
     * Retorna true se for ÚNICO (válido para cadastro), ou false se já existir registro com esse valor.
     */
    public static function isUnique(string $table, string $column, mixed $value, ?int $excludeId = null): bool
    {
        if ($value === null || trim((string)$value) === '') {
            return true;
        }

        $pdo = Database::getConnection();

        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val AND `deleted_at` IS NULL";
        if ($excludeId !== null && $excludeId > 0) {
            $sql .= " AND `id` != :exclude_id";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':val', trim((string)$value));
        if ($excludeId !== null && $excludeId > 0) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return ((int)$stmt->fetchColumn()) === 0;
    }

    /**
     * Valida redirect interno (anti open-redirect): só permite caminho relativo /...
     */
    public static function safeRedirectPath(?string $url, string $fallback = '/clients'): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return $fallback;
        }
        // Bloqueia absoluto, protocol-relative, backslash e CRLF
        if (str_contains($url, "\n") || str_contains($url, "\r") || str_contains($url, '\\')) {
            return $fallback;
        }
        if (!str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return $fallback;
        }
        if (preg_match('#^/(login|clients|suppliers|employees|products|users|roles|audit|workspace)(/.*)?$#', $url) !== 1) {
            // Permite apenas rotas conhecidas; resto cai no fallback
            return $fallback;
        }
        return $url;
    }
}

