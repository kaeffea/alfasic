<?php

declare(strict_types=1);

namespace Alfasic\Core;

class Env
{
    private static bool $loaded = false;
    private static array $variables = [];

    /**
     * Carrega as variáveis do arquivo .env para o ambiente PHP
     */
    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = $path ?? dirname(__DIR__, 2) . '/.env';

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            // Ignora comentários
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Divide em Chave = Valor
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove aspas simples ou duplas envolventes
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                self::$variables[$key] = $value;
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    /**
     * Obtém o valor de uma variável de ambiente com valor padrão opcional
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$variables[$key] ?? getenv($key) ?: $default;
    }
}
