<?php

declare(strict_types=1);

namespace Alfasic\Controllers;

use Alfasic\Core\View;
use Alfasic\Core\Csrf;
use Alfasic\Core\Validator;
use Alfasic\Models\Supplier;
use Alfasic\Models\AuditLog;

class SupplierController
{
    /**
     * Tela Principal de Listagem de Fornecedores
     */
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');

        if ($search !== '') {
            $suppliers = Supplier::search($search);
        } else {
            $suppliers = Supplier::all();
        }

        $totalCount = Supplier::count();

        View::render('suppliers/index', [
            'title' => 'Gestão de Fornecedores - Alfagás',
            'suppliers' => $suppliers,
            'totalCount' => $totalCount,
            'search' => $search,
        ]);
    }

    /**
     * Tela de Detalhes / Ficha Cadastral do Fornecedor
     */
    public function show(int $id): void
    {
        $supplierId = $id;
        $supplier = Supplier::find($supplierId);

        if (!$supplier) {
            http_response_code(404);
            echo "Fornecedor não encontrado.";
            return;
        }

        $contacts = Supplier::getContacts($supplierId);

        View::render('suppliers/show', [
            'title' => 'Fornecedor: ' . ($supplier['trade_name'] ?: $supplier['name']) . ' - Alfagás',
            'supplier' => $supplier,
            'contacts' => $contacts,
        ]);
    }

    /**
     * Processa a Inclusão de Novo Fornecedor
     */
    public function store(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $document = trim($_POST['document'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'A Razão Social / Nome Completo é obrigatória.';
        }

        if ($document !== '') {
            if (!Validator::validateDocument($document)) {
                $errors['document'] = 'O CNPJ / CPF informado é matematicamente inválido.';
            } elseif (!Validator::isUnique('suppliers', 'document', $document) || !Validator::isUnique('suppliers', 'document', Validator::cleanDigits($document))) {
                $errors['document'] = 'Este CNPJ / CPF já está cadastrado no sistema.';
            }
        }

        if ($email !== '') {
            if (!Validator::validateEmail($email)) {
                $errors['email'] = 'O formato do e-mail informado é inválido.';
            } elseif (!Validator::isUnique('suppliers', 'email', $email)) {
                $errors['email'] = 'Este endereço de e-mail já está em uso no sistema.';
            }
        }

        if ($phone !== '' && !Validator::validatePhone($phone)) {
            $errors['phone'] = 'O telefone informado é inválido (deve conter DDD).';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $referer = \Alfasic\Core\Validator::safeRedirectPath(parse_url($_SERVER['HTTP_REFERER'] ?? '/suppliers', PHP_URL_PATH) ?? '/suppliers', '/suppliers');
            header("Location: {$referer}");
            exit;
        }

        $newId = Supplier::create($_POST);
        $created = Supplier::find($newId);
        AuditLog::record(
            entity: 'suppliers',
            entityId: $newId,
            action: 'create',
            newValues: $created,
            recordLabel: $created['trade_name'] ?: $created['name'],
            contextModule: 'Fornecedores'
        );

        $_SESSION['flash_success'] = 'Fornecedor cadastrado com sucesso!';

        header("Location: /suppliers/{$newId}");
        exit;
    }

    /**
     * Processa Atualização Cadastral do Fornecedor
     */
    public function update(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $document = trim($_POST['document'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $errors = [];

        if ($name === '' || $id <= 0) {
            $errors['name'] = 'A Razão Social / Nome Completo é obrigatória.';
        }

        if ($document !== '') {
            if (!Validator::validateDocument($document)) {
                $errors['document'] = 'O CNPJ / CPF informado é matematicamente inválido.';
            } elseif (!Validator::isUnique('suppliers', 'document', $document, $id) || !Validator::isUnique('suppliers', 'document', Validator::cleanDigits($document), $id)) {
                $errors['document'] = 'Este CNPJ / CPF já está cadastrado no sistema.';
            }
        }

        if ($email !== '') {
            if (!Validator::validateEmail($email)) {
                $errors['email'] = 'O formato do e-mail informado é inválido.';
            } elseif (!Validator::isUnique('suppliers', 'email', $email, $id)) {
                $errors['email'] = 'Este endereço de e-mail já está em uso no sistema.';
            }
        }

        if ($phone !== '' && !Validator::validatePhone($phone)) {
            $errors['phone'] = 'O telefone informado é inválido (deve conter DDD).';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $referer = \Alfasic\Core\Validator::safeRedirectPath(parse_url($_SERVER['HTTP_REFERER'] ?? "/suppliers/{$id}", PHP_URL_PATH) ?? "/suppliers/{$id}", '/suppliers');
            header("Location: {$referer}");
            exit;
        }

        $oldValues = Supplier::find($id);
        Supplier::update($id, $_POST);
        $newValues = Supplier::find($id);

        AuditLog::record(
            entity: 'suppliers',
            entityId: $id,
            action: 'update',
            oldValues: $oldValues,
            newValues: $newValues,
            recordLabel: $newValues['trade_name'] ?: $newValues['name'],
            contextModule: 'Fornecedores'
        );

        $_SESSION['flash_success'] = 'Dados do fornecedor atualizados com sucesso!';

        header("Location: /suppliers/{$id}");
        exit;
    }

    /**
     * Arquiva / Exclui Fornecedor
     */
    public function delete(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($id > 0) {
            $oldValues = Supplier::find($id);
            Supplier::delete($id);
            $newValues = Supplier::find($id, withTrashed: true);

            AuditLog::record(
                entity: 'suppliers',
                entityId: $id,
                action: 'soft_delete',
                reason: $reason ?: 'Exclusão lógica de fornecedor',
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $oldValues['trade_name'] ?: $oldValues['name'],
                contextModule: 'Fornecedores'
            );

            $_SESSION['flash_success'] = 'Fornecedor arquivado com sucesso!';
        }

        header("Location: /suppliers");
        exit;
    }

    /**
     * Alterna Status Ativo / Inativo
     */
    public function toggleActive(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($id > 0) {
            $oldValues = Supplier::find($id, withTrashed: true);
            Supplier::toggleActive($id);
            $newValues = Supplier::find($id, withTrashed: true);
            $action = ($newValues['is_active'] ?? 0) ? 'activate' : 'deactivate';

            AuditLog::record(
                entity: 'suppliers',
                entityId: $id,
                action: $action,
                reason: $reason ?: (($action === 'activate') ? 'Ativação de fornecedor' : 'Inativação de fornecedor'),
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $newValues['trade_name'] ?: $newValues['name'],
                contextModule: 'Fornecedores'
            );

            $_SESSION['flash_success'] = 'Status do fornecedor alterado com sucesso!';
        }

        $redirect = \Alfasic\Core\Validator::safeRedirectPath($_POST['redirect_to'] ?? '/suppliers', '/suppliers');
        header("Location: {$redirect}");
        exit;
    }

    /**
     * Endpoint API para busca instantânea via AJAX / Fetch
     */
    public function searchApi(): void
    {
        $term = trim($_GET['q'] ?? '');

        if ($term === '') {
            $suppliers = Supplier::all(limit: 20);
        } else {
            $suppliers = Supplier::search($term, limit: 20);
        }

        $safe = array_map(fn($s) => [
            'id' => (int) $s['id'],
            'name' => $s['name'] ?? '',
            'trade_name' => $s['trade_name'] ?? null,
            'city' => $s['city'] ?? null,
            'supplier_type' => $s['supplier_type'] ?? null,
        ], $suppliers);

        View::json($safe);
    }
}
