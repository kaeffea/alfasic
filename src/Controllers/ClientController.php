<?php

namespace Alfasic\Controllers;

use Alfasic\Core\View;
use Alfasic\Models\Client;
use Alfasic\Core\Csrf;
use Alfasic\Core\Validator;
use Alfasic\Models\Product;
use Alfasic\Models\AuditLog;

class ClientController
{
    /**
     * Tela Principal de Listagem de Clientes
     */
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');

        if ($search !== '') {
            $clients = Client::search($search);
        } else {
            $clients = Client::all(limit: 50);
        }

        $totalCount = Client::count();

        View::render('clients/index', [
            'title' => 'Gestão de Clientes - Alfagás',
            'clients' => $clients,
            'search' => $search,
            'totalCount' => $totalCount,
        ]);
    }

    /**
     * Tela Dedicada de Detalhes do Cliente (Perfil Cadastral, Setores, Endereço e Resumo)
     */
    public function show(int $id): void
    {
        $clientId = $id;
        $client = Client::find($clientId);

        if (!$client) {
            http_response_code(404);
            echo "Cliente não encontrado.";
            return;
        }

        $contacts = Client::getContacts($clientId);
        $addresses = Client::getAddresses($clientId);
        $negotiatedPrices = Client::getAppliedPrices($clientId);

        View::render('clients/show', [
            'title' => "Cliente #{$clientId} - Alfagás",
            'client' => $client,
            'contacts' => $contacts,
            'addresses' => $addresses,
            'negotiatedPrices' => $negotiatedPrices,
        ]);
    }

    /**
     * Tela de Detalhes dos Preços Aplicados do Cliente
     */
    public function prices(int $id): void
    {
        $clientId = $id;
        $client = Client::find($clientId);

        if (!$client) {
            http_response_code(404);
            echo "Cliente não encontrado.";
            return;
        }

        $prices = Client::getAppliedPrices($clientId);
        $appliedIds = array_map('intval', array_column($prices, 'product_id'));
        $allProducts = Product::all(limit: 300);
        $availableProducts = array_values(array_filter($allProducts, function($g) use ($appliedIds) {
            return !in_array((int)$g['id'], $appliedIds, true);
        }));
        $contacts = Client::getContacts($clientId);

        View::render('clients/prices', [
            'title' => "Cliente #{$clientId} - Alfagás",
            'client' => $client,
            'prices' => $prices,
            'allProducts' => $availableProducts,
            'contacts' => $contacts,
        ]);
    }

    /**
     * Endpoint API para busca instantânea em tempo real via Fetch/AJAX
     */
    public function searchApi(): void
    {
        $term = trim($_GET['q'] ?? '');

        if ($term === '') {
            $clients = Client::all(limit: 20);
        } else {
            $clients = Client::search($term, limit: 20);
        }

        // Expõe só o necessário p/ autocomplete (sem CPF/CNPJ, e-mail, telefone)
        $safe = array_map(fn($c) => [
            'id' => (int) $c['id'],
            'name' => $c['name'] ?? '',
            'trade_name' => $c['trade_name'] ?? null,
            'city' => $c['city'] ?? null,
            'state' => $c['state'] ?? null,
        ], $clients);

        View::json($safe);
    }

    public function create(): void
    {
        View::render('clients/form', [
            'title' => 'Novo Cliente - Alfagás',
            'client' => [],
            'errors' => [],
        ]);
    }

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
            } elseif (!Validator::isUnique('clients', 'document', $document) || !Validator::isUnique('clients', 'document', Validator::cleanDigits($document))) {
                $errors['document'] = 'Este CNPJ / CPF já está cadastrado no sistema.';
            }
        }

        if ($email !== '') {
            if (!Validator::validateEmail($email)) {
                $errors['email'] = 'O formato do e-mail informado é inválido.';
            } elseif (!Validator::isUnique('clients', 'email', $email)) {
                $errors['email'] = 'Este endereço de e-mail já está em uso no sistema.';
            }
        }

        if ($phone !== '' && !Validator::validatePhone($phone)) {
            $errors['phone'] = 'O telefone informado é inválido (deve conter DDD).';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $referer = \Alfasic\Core\Validator::safeRedirectPath(parse_url($_SERVER['HTTP_REFERER'] ?? '/clients', PHP_URL_PATH) ?? '/clients', '/clients');
            header("Location: {$referer}");
            exit;
        }

        $newId = Client::create($_POST);
        $created = Client::find($newId);
        AuditLog::record(
            entity: 'clients',
            entityId: $newId,
            action: 'create',
            newValues: $created,
            recordLabel: $created['name'] ?? "Cliente #{$newId}",
            contextModule: 'Clientes'
        );

        $_SESSION['flash_success'] = 'Cliente cadastrado com sucesso!';
        header("Location: /clients/{$newId}/prices");
        exit;
    }

    public function edit(): void
    {
        $clientId = (int) ($_GET['id'] ?? 0);
        $client = Client::find($clientId);

        if (!$client) {
            http_response_code(404);
            echo "Cliente não encontrado.";
            return;
        }

        View::render('clients/form', [
            'title' => "Cliente #{$clientId} - Alfagás",
            'client' => $client,
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $clientId = (int) ($_POST['id'] ?? 0);
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
            } elseif (!Validator::isUnique('clients', 'document', $document, $clientId) || !Validator::isUnique('clients', 'document', Validator::cleanDigits($document), $clientId)) {
                $errors['document'] = 'Este CNPJ / CPF já está cadastrado no sistema.';
            }
        }

        if ($email !== '') {
            if (!Validator::validateEmail($email)) {
                $errors['email'] = 'O formato do e-mail informado é inválido.';
            } elseif (!Validator::isUnique('clients', 'email', $email, $clientId)) {
                $errors['email'] = 'Este endereço de e-mail já está em uso no sistema.';
            }
        }

        if ($phone !== '' && !Validator::validatePhone($phone)) {
            $errors['phone'] = 'O telefone informado é inválido (deve conter DDD).';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $referer = \Alfasic\Core\Validator::safeRedirectPath(parse_url($_SERVER['HTTP_REFERER'] ?? "/clients/{$clientId}", PHP_URL_PATH) ?? "/clients/{$clientId}", '/clients');
            header("Location: {$referer}");
            exit;
        }

        $oldValues = Client::find($clientId);
        Client::update($clientId, $_POST);
        $newValues = Client::find($clientId);

        AuditLog::record(
            entity: 'clients',
            entityId: $clientId,
            action: 'update',
            oldValues: $oldValues,
            newValues: $newValues,
            recordLabel: $newValues['name'] ?? "Cliente #{$clientId}",
            contextModule: 'Clientes'
        );

        $_SESSION['flash_success'] = 'Dados do cliente atualizados com sucesso!';

        $referer = \Alfasic\Core\Validator::safeRedirectPath(parse_url($_SERVER['HTTP_REFERER'] ?? "/clients/{$clientId}", PHP_URL_PATH) ?? "/clients/{$clientId}", '/clients');
        header("Location: {$referer}");
        exit;
    }

    public function savePrice(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $clientId = (int) ($_POST['client_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        $price = (float) str_replace(',', '.', $_POST['price'] ?? 0.0);
        $shippingFee = (float) str_replace(',', '.', $_POST['shipping_fee'] ?? 0.0);
        $rentalFee = (float) str_replace(',', '.', $_POST['rental_fee'] ?? 0.0);

        if ($clientId > 0 && $productId > 0) {
            Client::savePrice($clientId, $productId, $price, $shippingFee, $rentalFee);
            $_SESSION['flash_success'] = 'Preço salvo com sucesso!';
        }

        header("Location: /clients/{$clientId}/prices");
        exit;
    }

    public function deletePrice(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $clientId = (int) ($_POST['client_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($clientId > 0 && $productId > 0) {
            Client::deletePrice($clientId, $productId);
            $_SESSION['flash_success'] = 'Preço removido com sucesso!';
        }

        header("Location: /clients/{$clientId}/prices");
        exit;
    }

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
            $oldValues = Client::find($id);
            Client::delete($id);
            $newValues = Client::find($id, withTrashed: true);

            AuditLog::record(
                entity: 'clients',
                entityId: $id,
                action: 'soft_delete',
                reason: $reason ?: 'Exclusão lógica realizada pelo operador.',
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $oldValues['name'] ?? "Cliente #{$id}",
                contextModule: 'Clientes'
            );

            $_SESSION['flash_success'] = 'Cliente arquivado com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Operação inválida.';
        }

        header('Location: /clients');
        exit;
    }

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
            $oldValues = Client::find($id, withTrashed: true);
            Client::toggleActive($id);
            $newValues = Client::find($id, withTrashed: true);
            $action = ($newValues['is_active'] ?? 0) ? 'activate' : 'deactivate';

            AuditLog::record(
                entity: 'clients',
                entityId: $id,
                action: $action,
                reason: $reason ?: (($action === 'activate') ? 'Ativação cadastral' : 'Inativação cadastral'),
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $newValues['name'] ?? "Cliente #{$id}",
                contextModule: 'Clientes'
            );

            $_SESSION['flash_success'] = 'Status do cliente alterado com sucesso!';
        }

        $redirect = \Alfasic\Core\Validator::safeRedirectPath($_POST['redirect_to'] ?? '/clients', '/clients');
        header("Location: {$redirect}");
        exit;
    }
}
