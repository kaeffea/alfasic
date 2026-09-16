<?php

declare(strict_types=1);

namespace Alfasic\Controllers;

use Alfasic\Core\View;
use Alfasic\Core\Database;
use PDO;

class HomeController
{
    public function index(): void
    {
        $db = Database::getConnection();

        $count = function (string $table) use ($db): int {
            try {
                return (int) $db->query("SELECT COUNT(*) FROM `{$table}` WHERE deleted_at IS NULL")->fetchColumn();
            } catch (\Throwable $e) {
                return 0;
            }
        };

        $metrics = [
            'total_clients' => $count('clients'),
            'total_products' => $count('products'),
            'total_suppliers' => $count('suppliers'),
            'total_employees' => $count('employees'),
        ];

        try {
            $stmtClients = $db->query("
                SELECT id, name, trade_name, document, city, state, created_at
                FROM clients
                WHERE deleted_at IS NULL
                ORDER BY id DESC
                LIMIT 6
            ");
            $recentClients = $stmtClients->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            $recentClients = [];
        }

        View::render('home', [
            'title' => 'Painel Operacional - Alfagás',
            'metrics' => $metrics,
            'recentClients' => $recentClients,
            'totalClients' => $metrics['total_clients'],
        ]);
    }

    public function emptyWorkspace(): void
    {
        View::render('workspace/empty', [
            'title' => 'Espaço de Trabalho - Alfagás',
        ]);
    }
}
