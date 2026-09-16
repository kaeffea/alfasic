<?php

declare(strict_types=1);

namespace Alfasic\Controllers;

use Alfasic\Core\Csrf;
use Alfasic\Core\View;
use Alfasic\Core\Validator;
use Alfasic\Models\Employee;
use Alfasic\Models\AuditLog;

class EmployeeController
{
    /**
     * Exibe a listagem geral de funcionários
     * URL: GET /employees
     */
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');

        if ($search !== '') {
            $employees = Employee::search($search);
        } else {
            $employees = Employee::all();
        }

        $totalCount = Employee::count();

        View::render('employees/index', [
            'title' => 'Gestão de Funcionários - Alfagás',
            'employees' => $employees,
            'totalCount' => $totalCount,
            'search' => $search,
        ]);
    }

    /**
     * Exibe a ficha cadastral detalhada do funcionário
     * URL: GET /employees/{id}
     */
    public function show(int $id): void
    {
        $employeeId = $id;
        $employee = Employee::find($employeeId);

        if (!$employee) {
            header('Location: /employees');
            exit;
        }

        View::render('employees/show', [
            'title' => 'Funcionário: ' . $employee['name'] . ' - Alfagás',
            'employee' => $employee,
        ]);
    }

    /**
     * Processa o cadastro de um novo funcionário
     * URL: POST /employees/store
     */
    public function store(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            die('Token CSRF inválido ou expirado.');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $role = trim((string)($_POST['role_title'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $document = trim((string)($_POST['document'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'O Nome Completo é obrigatório.';
        }
        if (empty($role)) {
            $errors['role'] = 'O Cargo / Função é obrigatório.';
        }
        if (empty($phone) || !Validator::validatePhone($phone)) {
            $errors['phone'] = 'O Telefone / WhatsApp é obrigatório e deve conter DDD válido.';
        }

        if ($document !== '') {
            if (!Validator::validateCpf($document)) {
                $errors['document'] = 'O CPF informado é matematicamente inválido.';
            } elseif (!Validator::isUnique('employees', 'document', $document) || !Validator::isUnique('employees', 'document', Validator::cleanDigits($document))) {
                $errors['document'] = 'Este CPF já está cadastrado no sistema.';
            }
        }

        if ($email !== '') {
            if (!Validator::validateEmail($email)) {
                $errors['email'] = 'O formato do e-mail informado é inválido.';
            } elseif (!Validator::isUnique('employees', 'email', $email)) {
                $errors['email'] = 'Este endereço de e-mail já está em uso no sistema.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: /employees');
            exit;
        }

        $newId = Employee::create($_POST);
        $created = Employee::find($newId);
        AuditLog::record(
            entity: 'employees',
            entityId: $newId,
            action: 'create',
            newValues: $created,
            recordLabel: $created['name'] ?? null,
            contextModule: 'Funcionários'
        );

        $_SESSION['flash_success'] = 'Funcionário cadastrado com sucesso!';

        header('Location: /employees/' . $newId);
        exit;
    }

    /**
     * Processa a atualização de um funcionário existente
     * URL: POST /employees/update
     */
    public function update(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            die('Token CSRF inválido ou expirado.');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /employees');
            exit;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $role = trim((string)($_POST['role_title'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $document = trim((string)($_POST['document'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'O Nome Completo é obrigatório.';
        }
        if (empty($role)) {
            $errors['role'] = 'O Cargo / Função é obrigatório.';
        }
        if (empty($phone) || !Validator::validatePhone($phone)) {
            $errors['phone'] = 'O Telefone / WhatsApp é obrigatório e deve conter DDD válido.';
        }

        if ($document !== '') {
            if (!Validator::validateCpf($document)) {
                $errors['document'] = 'O CPF informado é matematicamente inválido.';
            } elseif (!Validator::isUnique('employees', 'document', $document, $id) || !Validator::isUnique('employees', 'document', Validator::cleanDigits($document), $id)) {
                $errors['document'] = 'Este CPF já está cadastrado no sistema.';
            }
        }

        if ($email !== '') {
            if (!Validator::validateEmail($email)) {
                $errors['email'] = 'O formato do e-mail informado é inválido.';
            } elseif (!Validator::isUnique('employees', 'email', $email, $id)) {
                $errors['email'] = 'Este endereço de e-mail já está em uso no sistema.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: /employees/' . $id);
            exit;
        }

        $oldValues = Employee::find($id);
        Employee::update($id, $_POST);
        $newValues = Employee::find($id);

        AuditLog::record(
            entity: 'employees',
            entityId: $id,
            action: 'update',
            oldValues: $oldValues,
            newValues: $newValues,
            recordLabel: $newValues['name'] ?? null,
            contextModule: 'Funcionários'
        );

        $_SESSION['flash_success'] = 'Funcionário atualizado com sucesso.';

        header('Location: /employees/' . $id);
        exit;
    }

    /**
     * Desliga/arquiva um funcionário (Soft Delete)
     * URL: POST /employees/delete
     */
    public function delete(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            die('Token CSRF inválido ou expirado.');
        }

        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($id > 0) {
            $oldValues = Employee::find($id);
            Employee::delete($id);
            $newValues = Employee::find($id, withTrashed: true);

            AuditLog::record(
                entity: 'employees',
                entityId: $id,
                action: 'soft_delete',
                reason: $reason ?: 'Desligamento/arquivamento de colaborador',
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $oldValues['name'] ?? null,
                contextModule: 'Funcionários'
            );

            $_SESSION['flash_success'] = 'Colaborador arquivado com sucesso!';
        }

        header('Location: /employees');
        exit;
    }

    /**
     * Alterna Status Ativo / Inativo
     */
    public function toggleActive(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            die('Token CSRF inválido ou expirado.');
        }

        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($id > 0) {
            $oldValues = Employee::find($id, withTrashed: true);
            Employee::toggleActive($id);
            $newValues = Employee::find($id, withTrashed: true);
            $action = ($newValues['is_active'] ?? 0) ? 'activate' : 'deactivate';

            AuditLog::record(
                entity: 'employees',
                entityId: $id,
                action: $action,
                reason: $reason ?: (($action === 'activate') ? 'Ativação de colaborador' : 'Inativação de colaborador'),
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $newValues['name'] ?? null,
                contextModule: 'Funcionários'
            );

            $_SESSION['flash_success'] = 'Status do colaborador alterado com sucesso!';
        }

        $redirect = \Alfasic\Core\Validator::safeRedirectPath($_POST['redirect_to'] ?? '/employees', '/employees');
        header("Location: {$redirect}");
        exit;
    }

    /**
     * Endpoint API para busca instantânea em tempo real via Fetch/AJAX
     * URL: GET /api/employees/search?q=termo
     */
    public function searchApi(): void
    {
        $term = trim($_GET['q'] ?? '');
        $employees = Employee::search($term, limit: 20);

        $safe = array_map(fn($e) => [
            'id' => (int) $e['id'],
            'name' => $e['name'] ?? '',
            'role_title' => $e['role_title'] ?? null,
            'branch' => $e['branch'] ?? null,
            'department' => $e['department'] ?? null,
        ], $employees);

        View::json($safe);
    }
}
