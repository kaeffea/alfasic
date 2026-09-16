<?php

declare(strict_types=1);

namespace Alfasic\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Carrega variáveis do arquivo .env
            Env::load();

            $dbHost   = Env::get('DB_HOST', '127.0.0.1');
            $dbPort   = (int) Env::get('DB_PORT', 3306);
            $dbName   = Env::get('DB_NAME', 'alfagas_db');
            $dbUser   = Env::get('DB_USER', 'alfagas_user');
            $dbPass   = Env::get('DB_PASS', 'alfagas_pass');
            $dbSsl    = (bool) Env::get('DB_SSL', false);

            $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            if ($dbSsl) {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }

            try {
                self::$connection = new PDO($dsn, $dbUser, $dbPass, $options);
            } catch (PDOException $e) {
                die("❌ [ERRO DE CONEXÃO MYSQL] Não foi possível conectar ao banco '{$dbName}' em {$dbHost}:{$dbPort}.\nDetalhe: " . $e->getMessage() . "\n");
            }
        }

        return self::$connection;
    }
}