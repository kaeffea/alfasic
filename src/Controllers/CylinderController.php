<?php

declare(strict_types=1);

namespace Alfasic\Controllers;

use Alfasic\Core\View;
use Alfasic\Core\Csrf;
use Alfasic\Core\Validator;
use Alfasic\Core\Database;
use Alfasic\Models\Cylinder;
use Alfasic\Models\Product;
use Alfasic\Models\AuditLog;
use PDO;

class CylinderController
{
    /**
     * Tela Principal de Tipos de Cilindro
     */
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');

        if ($search !== '') {
            $rawTypes = Cylinder::search($search);
            // Enriquece com produto vinculado
            $types = array_map(function ($t) {
                $p = Product::findBy('cylinder_type_id', (int) $t['id']);
                $t['product_id'] = $p['id'] ?? null;
                $t['product_name'] = $p['name'] ?? null;
                return $t;
            }, $rawTypes);
        } else {
            $types = Cylinder::allWithProduct(limit: 500);
        }

        $totalCount = Cylinder::count();
        $unlinkedGases = Cylinder::unlinkedGases();

        View::render('cylinders/index', [
            'title' => 'Tipos de Cilindro - Alfagás',
            'types' => $types,
            'allTypes' => $types,
            'totalCount' => $totalCount,
            'unlinkedCount' => count($unlinkedGases),
            'unlinkedGases' => $unlinkedGases,
            'search' => $search,
        ]);
    }

    /**
     * Processa a Inclusão/Atualização via Drawer (com vínculo 1:1 opcional).
     * Para fechar o par sem ovo-e-galinha: informa new_product_name e o gás nasce junto.
     */
    public function store(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $productId = (int) ($_POST['product_id'] ?? 0);
        $newProductName = trim($_POST['new_product_name'] ?? '');

        if ($name === '') {
            $_SESSION['flash_error'] = 'O nome do tipo de cilindro é obrigatório.';
            header('Location: /cylinders');
            exit;
        }

        if ($productId > 0) {
            $prod = Product::find($productId);
            if (!$prod) {
                $_SESSION['flash_error'] = 'Produto vinculado não encontrado.';
                header('Location: /cylinders');
                exit;
            }
            if (($prod['product_type'] ?? '') !== 'gas') {
                $_SESSION['flash_error'] = 'Só produto do tipo gás vincula a cilindro.';
                header('Location: /cylinders');
                exit;
            }
            if (!empty($prod['cylinder_type_id']) && (int) $prod['cylinder_type_id'] !== $id) {
                $_SESSION['flash_error'] = 'Este gás já está vinculado a outro tipo (1:1).';
                header('Location: /cylinders');
                exit;
            }
        }

        $data = $_POST;
        $data['capacity'] = !empty($data['capacity']) ? (float) $data['capacity'] : null;
        $data['working_pressure_bar'] = !empty($data['working_pressure_bar']) ? (float) $data['working_pressure_bar'] : null;
        $data['tare_weight_kg'] = !empty($data['tare_weight_kg']) ? (float) $data['tare_weight_kg'] : null;
        $data['replacement_value'] = isset($data['replacement_value']) && $data['replacement_value'] !== '' ? Validator::cleanMoney($data['replacement_value']) : 0.00;
        $data['unit'] = strtolower(trim($data['unit'] ?? 'm³'));
        unset($data['product_id'], $data['new_product_name']);

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            if ($id > 0) {
                $oldValues = Cylinder::find($id);
                Cylinder::update($id, $data);
                $newId = $id;
                $action = 'update';
            } else {
                $newId = Cylinder::create($data);
                $oldValues = null;
                $action = 'create';
            }

            // Fecha o par: cria o gás junto ou vincula o existente
            if ($newProductName !== '' && $productId <= 0) {
                $productId = Product::create([
                    'name' => $newProductName,
                    'product_type' => 'gas',
                    'usage_segment' => $_POST['usage_segment'] ?? 'industrial',
                    'unit' => $data['unit'],
                    'capacity' => $data['capacity'],
                    'standard_price' => 0.00,
                    'cylinder_type_id' => $newId,
                ]);
                AuditLog::record(
                    entity: 'products',
                    entityId: $productId,
                    action: 'create',
                    newValues: Product::find($productId),
                    recordLabel: $newProductName,
                    contextModule: 'Cilindros'
                );
            } elseif ($productId > 0) {
                // Troca de vínculo: desvincula qualquer outro gás deste tipo antes (1:1)
                $stmtUnlink = $pdo->prepare("UPDATE products SET cylinder_type_id = NULL WHERE cylinder_type_id = :tid AND id != :pid");
                $stmtUnlink->execute([':tid' => $newId, ':pid' => $productId]);
                $stmtLink = $pdo->prepare("UPDATE products SET cylinder_type_id = :tid WHERE id = :pid AND deleted_at IS NULL");
                $stmtLink->execute([':tid' => $newId, ':pid' => $productId]);
            }

            $newValues = Cylinder::find($newId);
            AuditLog::record(
                entity: 'cylinders',
                entityId: $newId,
                action: $action,
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $newValues['name'] ?? null,
                contextModule: 'Cilindros'
            );

            $pdo->commit();
            $_SESSION['flash_success'] = $action === 'create' ? 'Tipo de cilindro cadastrado com sucesso!' : 'Tipo de cilindro atualizado com sucesso!';
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Falha ao salvar: ' . $e->getMessage();
        }

        header('Location: /cylinders');
        exit;
    }

    /**
     * Exclusão lógica (bloqueada se houver gás vinculado — ver AuditLog::checkDependencies).
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
            $check = AuditLog::checkDependencies('cylinders', $id);
            if (!$check['can_force_delete']) {
                $_SESSION['flash_error'] = implode(' ', $check['blocking_reasons']);
                header('Location: /cylinders');
                exit;
            }
            $oldValues = Cylinder::find($id);
            Cylinder::delete($id);
            $newValues = Cylinder::find($id, withTrashed: true);

            AuditLog::record(
                entity: 'cylinders',
                entityId: $id,
                action: 'soft_delete',
                reason: $reason ?: 'Exclusão lógica de tipo de cilindro',
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $oldValues['name'] ?? null,
                contextModule: 'Cilindros'
            );

            $_SESSION['flash_success'] = 'Tipo de cilindro arquivado com sucesso!';
        }

        header('Location: /cylinders');
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
            $oldValues = Cylinder::find($id, withTrashed: true);
            Cylinder::toggleActive($id);
            $newValues = Cylinder::find($id, withTrashed: true);
            $action = ($newValues['is_active'] ?? 0) ? 'activate' : 'deactivate';

            AuditLog::record(
                entity: 'cylinders',
                entityId: $id,
                action: $action,
                reason: $reason ?: (($action === 'activate') ? 'Ativação de cilindro' : 'Inativação de cilindro'),
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $newValues['name'] ?? null,
                contextModule: 'Cilindros'
            );

            $_SESSION['flash_success'] = 'Status do cilindro alterado com sucesso!';
        }

        $redirect = \Alfasic\Core\Validator::safeRedirectPath($_POST['redirect_to'] ?? '/cylinders', '/cylinders');
        header("Location: {$redirect}");
        exit;
    }

    /**
     * API JSON para busca rápida (combobox de vínculo no drawer de produtos).
     */
    public function searchApi(): void
    {
        $q = trim($_GET['q'] ?? '');

        if ($q === '') {
            $types = Cylinder::all(limit: 20);
        } else {
            $types = Cylinder::search($q, limit: 20);
        }

        $safe = array_map(fn($t) => [
            'id' => (int) $t['id'],
            'name' => $t['name'] ?? '',
            'capacity' => $t['capacity'] ?? null,
            'unit' => $t['unit'] ?? null,
        ], $types);

        View::json($safe);
    }
}
