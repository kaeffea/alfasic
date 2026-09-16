<?php

declare(strict_types=1);

namespace Alfasic\Controllers;

use Alfasic\Core\View;
use Alfasic\Core\Csrf;
use Alfasic\Core\Validator;
use Alfasic\Models\Product;
use Alfasic\Models\AuditLog;

class ProductController
{
    /**
     * Tela Principal de Listagem do Catálogo de Produtos
     */
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');

        if ($search !== '') {
            $rawProducts = Product::search($search);
        } else {
            $rawProducts = Product::all(limit: 500);
        }

        // Formata os rótulos de exibição para a tabela e interface
        $products = array_map(function ($p) {
            $types = [
                'gas' => 'Gás',
                'equipment' => 'Equipamento',
                'accessory' => 'Acessório',
                'service' => 'Serviço',
            ];

            $usages = [
                'medicinal' => 'Medicinal',
                'industrial' => 'Industrial',
            ];

            $type = $p['product_type'] ?? 'gas';
            $usage = $p['usage_segment'] ?? 'industrial';
            $cap = (float)($p['capacity'] ?? 0);
            $unit = strtolower($p['unit'] ?? 'm³');

            $p['type_label'] = $types[$type] ?? 'Gás';
            $p['usage_label'] = $usages[$usage] ?? 'Geral';
            $p['capacity_display'] = $cap > 0 ? "{$cap} {$unit}" : '-';

            return $p;
        }, $rawProducts);

        $totalCount = Product::count();

        View::render('products/index', [
            'title' => 'Catálogo de Produtos - Alfagás',
            'products' => $products,
            'allProducts' => $products,
            'totalCount' => $totalCount,
            'search' => $search,
            'cylinderTypes' => \Alfasic\Models\Cylinder::all(limit: 300),
        ]);
    }

    /**
     * Processa a Inclusão de Novo Produto ou Atualização via Drawer
     */
    public function store(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            $_SESSION['flash_error'] = 'O nome comercial do produto é obrigatório.';
            header("Location: /products");
            exit;
        }

        // Gás exige tipo de cilindro (1:1); demais tipos nunca vinculam
        $productType = $_POST['product_type'] ?? 'gas';
        $cylTypeId = (int) ($_POST['cylinder_type_id'] ?? 0);
        if ($productType === 'gas' && $cylTypeId <= 0) {
            $_SESSION['flash_error'] = 'Todo gás precisa de um tipo de cilindro vinculado (1:1).';
            header('Location: /products');
            exit;
        }
        if ($cylTypeId > 0) {
            $ct = \Alfasic\Models\Cylinder::find($cylTypeId);
            if (!$ct) {
                $_SESSION['flash_error'] = 'Tipo de cilindro vinculado não encontrado.';
                header('Location: /products');
                exit;
            }
            $holder = \Alfasic\Models\Product::findBy('cylinder_type_id', $cylTypeId);
            if ($holder && (int) $holder['id'] !== $id) {
                $_SESSION['flash_error'] = 'Este tipo já está vinculado a outro gás (1:1).';
                header('Location: /products');
                exit;
            }
        }

        // Sanitização e formatação dos campos numéricos
        $data = $_POST;
        $data['capacity'] = !empty($data['capacity']) ? (float)$data['capacity'] : null;
        $data['unit_price'] = !empty($data['unit_price']) ? Validator::cleanMoney($data['unit_price']) : null;
        $data['standard_price'] = !empty($data['standard_price']) ? Validator::cleanMoney($data['standard_price']) : 0.00;
        $data['unit'] = strtolower(trim($data['unit'] ?? 'm³'));
        $data['cylinder_type_id'] = ($productType === 'gas' && $cylTypeId > 0) ? $cylTypeId : null;

        if ($id > 0) {
            $oldValues = Product::find($id);
            Product::update($id, $data);
            $newValues = Product::find($id);

            AuditLog::record(
                entity: 'products',
                entityId: $id,
                action: 'update',
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $newValues['name'] ?? null,
                contextModule: 'Produtos'
            );

            $_SESSION['flash_success'] = 'Produto atualizado com sucesso!';
        } else {
            $newId = Product::create($data);
            $created = Product::find($newId);

            AuditLog::record(
                entity: 'products',
                entityId: $newId,
                action: 'create',
                newValues: $created,
                recordLabel: $created['name'] ?? null,
                contextModule: 'Produtos'
            );

            $_SESSION['flash_success'] = 'Produto cadastrado com sucesso!';
        }

        header("Location: /products");
        exit;
    }

    /**
     * Processa a Exclusão / Desativação do Produto (Soft Delete Seguro)
     */
    public function delete(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($id > 0) {
            $oldValues = Product::find($id);
            Product::delete($id);
            $newValues = Product::find($id, withTrashed: true);

            AuditLog::record(
                entity: 'products',
                entityId: $id,
                action: 'soft_delete',
                reason: $reason ?: 'Exclusão lógica de produto',
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $oldValues['name'] ?? null,
                contextModule: 'Produtos'
            );

            $_SESSION['flash_success'] = 'Produto arquivado com sucesso!';
        }

        header("Location: /products");
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

        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($id > 0) {
            $oldValues = Product::find($id, withTrashed: true);
            Product::toggleActive($id);
            $newValues = Product::find($id, withTrashed: true);
            $action = ($newValues['is_active'] ?? 0) ? 'activate' : 'deactivate';

            AuditLog::record(
                entity: 'products',
                entityId: $id,
                action: $action,
                reason: $reason ?: (($action === 'activate') ? 'Ativação de produto' : 'Inativação de produto'),
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $newValues['name'] ?? null,
                contextModule: 'Produtos'
            );

            $_SESSION['flash_success'] = 'Status do produto alterado com sucesso!';
        }

        $redirect = \Alfasic\Core\Validator::safeRedirectPath($_POST['redirect_to'] ?? '/products', '/products');
        header("Location: {$redirect}");
        exit;
    }

    /**
     * API JSON para busca rápida de produtos (auto-complete em pedidos e orçamentos)
     */
    public function searchApi(): void
    {
        $q = trim($_GET['q'] ?? '');

        if ($q === '') {
            View::json([]);
            return;
        }

        $products = Product::search($q, limit: 20);
        $safe = array_map(fn($p) => [
            'id' => (int) $p['id'],
            'name' => $p['name'] ?? '',
            'product_type' => $p['product_type'] ?? null,
            'usage_segment' => $p['usage_segment'] ?? null,
            'capacity' => $p['capacity'] ?? null,
            'unit' => $p['unit'] ?? null,
            'standard_price' => $p['standard_price'] ?? null,
        ], $products);

        View::json($safe);
    }
}