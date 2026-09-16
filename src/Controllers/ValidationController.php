<?php

declare(strict_types=1);

namespace Alfasic\Controllers;

use Alfasic\Core\View;
use Alfasic\Core\Validator;

class ValidationController
{
    /**
     * Endpoint API para verificação em tempo real de unicidade no frontend
     * GET /api/validate-unique?entity=client&field=document&value=...&exclude_id=...
     */
    public function checkUnique(): void
    {
        // Throttle anti-enumeração: 60 checagens/min por sessão
        $now = time();
        $bucket = $_SESSION['validate_unique_bucket'] ?? ['count' => 0, 'reset' => $now];
        if ($now - $bucket['reset'] >= 60) {
            $bucket = ['count' => 0, 'reset' => $now];
        }
        $bucket['count']++;
        $_SESSION['validate_unique_bucket'] = $bucket;
        if ($bucket['count'] > 60) {
            View::json(['valid' => false, 'available' => true, 'error' => 'Muitas verificações. Aguarde.'], 429);
            return;
        }

        $entity = trim($_GET['entity'] ?? '');
        $field = trim($_GET['field'] ?? '');
        $value = trim($_GET['value'] ?? '');
        $excludeId = isset($_GET['exclude_id']) && is_numeric($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null;

        $tableMap = [
            'client' => 'clients',
            'supplier' => 'suppliers',
            'employee' => 'employees',
            'user' => 'users',
            'role' => 'roles',
        ];

        $allowedFields = [
            'clients' => ['document', 'email'],
            'suppliers' => ['document', 'email'],
            'employees' => ['document', 'email'],
            'users' => ['username', 'email'],
            'roles' => ['name', 'slug'],
        ];

        if (!isset($tableMap[$entity])) {
            View::json(['valid' => false, 'available' => true, 'error' => 'Entidade inválida.'], 400);
            return;
        }

        $table = $tableMap[$entity];

        if (!in_array($field, $allowedFields[$table] ?? [], true)) {
            View::json(['valid' => false, 'available' => true, 'error' => 'Campo não permitido para verificação.'], 400);
            return;
        }

        if ($value === '') {
            View::json(['valid' => true, 'available' => true]);
            return;
        }

        // Se for documento, limpa pontuação para validação no banco se estiver armazenado limpo ou formatado
        $isUnique = Validator::isUnique($table, $field, $value, $excludeId);

        // Se checando documento, também checa a versão limpa de dígitos
        if ($field === 'document' && $isUnique) {
            $digits = Validator::cleanDigits($value);
            if ($digits !== '' && $digits !== $value) {
                $isUnique = Validator::isUnique($table, $field, $digits, $excludeId);
            }
        }

        if (!$isUnique) {
            $fieldLabels = [
                'document' => 'Este CNPJ / CPF já está cadastrado no sistema.',
                'email' => 'Este endereço de e-mail já está em uso.',
                'username' => 'Este nome de usuário (login) já está em uso.',
                'name' => 'Já existe um registro com esta denominação.',
            ];

            $msg = $fieldLabels[$field] ?? 'Este valor já está em uso.';
            View::json(['valid' => true, 'available' => false, 'message' => $msg]);
            return;
        }

        View::json(['valid' => true, 'available' => true]);
    }
}
