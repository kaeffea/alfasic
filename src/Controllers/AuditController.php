<?php

declare(strict_types=1);

namespace Alfasic\Controllers;

use Alfasic\Core\View;
use Alfasic\Core\Csrf;
use Alfasic\Models\AuditLog;
use Alfasic\Models\User;

/**
 * Controller da Gestão de Auditoria & Motor Time-Travel (AuditController)
 */
class AuditController
{
    /**
     * Linha do tempo visual da Trilha de Auditoria
     */
    private function deny(string $permission): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!User::hasPermission((array) $user, $permission)) {
            http_response_code(403);
            echo '403 Acesso negado: permissão ' . htmlspecialchars($permission) . ' necessária.';
            return true;
        }
        return false;
    }

    public function index(): void
    {
        if ($this->deny('audit.view')) {
            return;
        }
        $filters = [
            'entity' => trim($_GET['entity'] ?? ''),
            'action' => trim($_GET['action'] ?? ''),
            'user_id' => trim($_GET['user_id'] ?? ''),
            'date' => trim($_GET['date'] ?? ''),
            'search' => trim($_GET['search'] ?? $_GET['q'] ?? '')
        ];

        $auditEvents = AuditLog::getTimeline($filters);
        $stats = AuditLog::getStats();

        View::render('audit/index', [
            'pageTitle' => 'Auditoria',
            'title' => 'Gestão de Auditoria - Alfagás',
            'auditEvents' => $auditEvents,
            'stats' => $stats,
            'filters' => $filters
        ]);
    }

    /**
     * Processa a Reversão (Rollback) de um Log Histórico
     */
    public function rollback(): void
    {
        if ($this->deny('audit.rollback')) {
            return;
        }
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $logId = (int) ($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($logId <= 0) {
            $_SESSION['flash_error'] = 'ID de auditoria inválido para reversão.';
            header('Location: /audit');
            exit;
        }

        if (mb_strlen($reason) < 5) {
            $_SESSION['flash_error'] = 'A justificativa para reversão é obrigatória (mínimo de 5 caracteres).';
            header('Location: /audit');
            exit;
        }

        $result = AuditLog::rollback($logId, $reason);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        $redirect = \Alfasic\Core\Validator::safeRedirectPath($_POST['redirect_to'] ?? '/audit', '/audit');
        header("Location: {$redirect}");
        exit;
    }

    /**
     * Processa a Exclusão Física Permanente (Hard Delete Seguro)
     */
    public function forceDelete(): void
    {
        if ($this->deny('audit.force_delete')) {
            return;
        }
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $entity = trim($_POST['entity'] ?? '');
        $entityId = (int) ($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($entityId <= 0 || empty($entity)) {
            $_SESSION['flash_error'] = 'Parâmetros inválidos para exclusão permanente.';
            header('Location: /audit');
            exit;
        }

        if (mb_strlen($reason) < 5) {
            $_SESSION['flash_error'] = 'A justificativa para exclusão permanente é obrigatória (mínimo de 5 caracteres).';
            header('Location: /audit');
            exit;
        }

        $result = AuditLog::forceDeleteRecord($entity, $entityId, $reason);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        $redirect = \Alfasic\Core\Validator::safeRedirectPath($_POST['redirect_to'] ?? '/audit', '/audit');
        header("Location: {$redirect}");
        exit;
    }

    /**
     * Restaura registro inativado/excluído
     */
    public function restore(): void
    {
        if ($this->deny('audit.rollback')) {
            return;
        }
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo "Erro 403: Requisição não autorizada. Token CSRF inválido.";
            return;
        }

        $entity = trim($_POST['entity'] ?? '');
        $entityId = (int) ($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        $modelClass = AuditLog::$entityModelMap[$entity] ?? null;
        if (!$modelClass || !class_exists($modelClass)) {
            $_SESSION['flash_error'] = 'Entidade inválida para restauração.';
            header('Location: /audit');
            exit;
        }

        if (mb_strlen($reason) < 5) {
            $_SESSION['flash_error'] = 'A justificativa para restauração é obrigatória (mínimo de 5 caracteres).';
            header('Location: /audit');
            exit;
        }

        $oldRecord = $modelClass::find($entityId, withTrashed: true);
        $modelClass::restore($entityId);
        $newRecord = $modelClass::find($entityId, withTrashed: true);

        AuditLog::record(
            entity: $entity,
            entityId: $entityId,
            action: 'activate',
            reason: $reason,
            oldValues: $oldRecord,
            newValues: $newRecord,
            recordLabel: $newRecord['name'] ?? null,
            contextModule: 'Auditoria'
        );

        $_SESSION['flash_success'] = 'Registro restaurado com sucesso!';
        $redirect = \Alfasic\Core\Validator::safeRedirectPath($_POST['redirect_to'] ?? '/audit', '/audit');
        header("Location: {$redirect}");
        exit;
    }
}
