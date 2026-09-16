<?php

declare(strict_types=1);

namespace Alfasic\Controllers;

use Alfasic\Core\Csrf;
use Alfasic\Core\View;
use Alfasic\Core\Validator;
use Alfasic\Models\Employee;
use Alfasic\Models\Role;
use Alfasic\Models\User;
use Alfasic\Models\AuditLog;

/**
 * Controller de Gestão de Usuários do Sistema
 */
class UserController
{
    /**
     * Listagem geral de usuários com dados agregados de Perfil e Funcionário
     */
    public function index(): void
    {
        $users = User::allWithDetails();
        $roles = Role::where(['is_active' => 1]);
        $employees = Employee::all(limit: 500);

        View::render('users/index', [
            'pageTitle' => 'Usuários',
            'title' => 'Gestão de Usuários - Alfagás',
            'users' => $users,
            'roles' => $roles,
            'employees' => $employees,
            'totalCount' => count($users),
        ]);
    }

    /**
     * Processa a criação ou edição de um usuário
     */
    public function store(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo 'Erro de validação de segurança CSRF.';
            return;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $passwordConfirm = trim($_POST['password_confirm'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);
        $employeeId = !empty($_POST['employee_id']) ? (int)$_POST['employee_id'] : null;
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        $errors = [];

        if ($username === '') {
            $errors['username'] = 'O nome de usuário (login) é obrigatório.';
        } elseif (!Validator::isUnique('users', 'username', $username, $id)) {
            $errors['username'] = 'Este nome de usuário (login) já está em uso.';
        }

        if ($email === '') {
            $errors['email'] = 'O e-mail corporativo é obrigatório.';
        } elseif (!Validator::validateEmail($email)) {
            $errors['email'] = 'O formato do e-mail informado é inválido.';
        } elseif (!Validator::isUnique('users', 'email', $email, $id)) {
            $errors['email'] = 'Este endereço de e-mail já está em uso no sistema.';
        }

        if ($roleId <= 0) {
            $errors['role_id'] = 'Selecione um perfil de permissão.';
        }

        if (!$id) {
            // Novo Usuário: senha obrigatória
            if ($password === '' || strlen($password) < 8) {
                $errors['password'] = 'A senha é obrigatória e deve conter no mínimo 8 caracteres.';
            } elseif ($password !== $passwordConfirm) {
                $errors['password_confirm'] = 'A confirmação de senha não confere.';
            }
        } else {
            // Edição: se senha foi digitada
            if ($password !== '') {
                if (strlen($password) < 8) {
                    $errors['password'] = 'A nova senha deve conter no mínimo 8 caracteres.';
                } elseif ($password !== $passwordConfirm) {
                    $errors['password_confirm'] = 'A confirmação de senha não confere.';
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: /users');
            exit;
        }

        $data = [
            'username' => $username,
            'email' => $email,

            'role_id' => $roleId,
            'employee_id' => $employeeId,
            'is_active' => $isActive,
        ];

        if ($id) {
            // Modo Edição
            $oldValues = User::find($id);
            User::updateWithPassword($id, $data, $password !== '' ? $password : null);
            $newValues = User::find($id);

            AuditLog::record(
                entity: 'users',
                entityId: $id,
                action: 'update',
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $newValues['username'] ?? null,
                contextModule: 'Usuários'
            );

            $_SESSION['flash_success'] = 'Usuário atualizado com sucesso!';
        } else {
            // Modo Novo Usuário
            $newId = User::createWithPassword($data, $password);
            $created = User::find($newId);

            AuditLog::record(
                entity: 'users',
                entityId: $newId,
                action: 'create',
                newValues: $created,
                recordLabel: $created['username'] ?? null,
                contextModule: 'Usuários'
            );

            $_SESSION['flash_success'] = 'Usuário cadastrado com sucesso!';
        }

        header('Location: /users');
        exit;
    }

    /**
     * Revoga os dispositivos confiáveis de um usuário (perdeu acesso ao e-mail
     * ou suspeita de sessão estranha). Exige users.manage. No próximo login
     * ele recebe o código por e-mail normalmente.
     */
    public function reset2fa(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo 'Erro de validação CSRF.';
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /users');
            exit;
        }

        $oldValues = User::find($id);
        User::revokeTrustedDevices($id);

        AuditLog::record(
            entity: 'users',
            entityId: $id,
            action: 'update',
            reason: 'Revogação administrativa dos dispositivos confiáveis (2FA e-mail).',
            oldValues: $oldValues,
            newValues: User::find($id),
            recordLabel: $oldValues['username'] ?? null,
            contextModule: 'Usuários'
        );

        $_SESSION['flash_success'] = 'Dispositivos confiáveis revogados.';
        header('Location: /users');
        exit;
    }

    /**
     * Exclui (soft delete) um usuário
     */
    public function delete(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo 'Erro de validação CSRF.';
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($id <= 0) {
            header('Location: /users');
            exit;
        }

        // Impede que o usuário exclua a própria conta logada
        if (isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === $id) {
            header('Location: /users?error=auto_exclusao_proibida');
            exit;
        }

        $oldValues = User::find($id);
        User::delete($id);
        $newValues = User::find($id, withTrashed: true);

        AuditLog::record(
            entity: 'users',
            entityId: $id,
            action: 'soft_delete',
            reason: $reason ?: 'Exclusão de conta de usuário',
            oldValues: $oldValues,
            newValues: $newValues,
            recordLabel: $oldValues['username'] ?? null,
            contextModule: 'Usuários'
        );

        header('Location: /users?deleted=1');
        exit;
    }
}
