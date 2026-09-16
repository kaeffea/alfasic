<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Core/Autoloader.php';
\Alfasic\Core\Autoloader::register();

use Alfasic\Core\Database;
use Alfasic\Core\Env;

echo "=== INICIALIZANDO SCHEMA MYSQL OFICIAL DO ALFASIC ===\n\n";

try {
    Env::load();
    $pdo = Database::getConnection();
    echo "✓ [OK] Conectado com sucesso ao MySQL HeatWave / Server!\n";

    // 1. Executa Schema DDL Oficial (statement por statement para suportar DELIMITER/INSERT múltiplos)
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Arquivo schema.sql não encontrado!");
    }
    $schema = file_get_contents($schemaFile);
    // Remove comentários de linha para split seguro e executa por blocos
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    $executed = 0;
    foreach ($statements as $stmt) {
        // Ignora blocos vazios ou só comentários
        $code = trim(preg_replace('/^--[^\n]*$/m', '', $stmt));
        if ($code === '' || stripos($code, 'SET FOREIGN_KEY_CHECKS') === 0 && strlen($code) < 30) {
            if ($code !== '') {
                $pdo->exec($stmt);
                $executed++;
            }
            continue;
        }
        if ($code === '') {
            continue;
        }
        $pdo->exec($stmt);
        $executed++;
    }
    echo "✓ [OK] Schema MySQL (schema.sql) estruturado e executado com sucesso ({$executed} statements)!\n";
    echo "✓ [OK] Banco de dados alfagas_db 100% pronto e limpo para produção (só seed permissions).\n\n";

} catch (Throwable $e) {
    echo "\n❌ [ERRO] Falha ao inicializar o banco de dados:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
