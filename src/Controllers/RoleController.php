<?php

declare(strict_types=1);

namespace Alfasic\Controllers;

use Alfasic\Core\Csrf;
use Alfasic\Core\View;
use Alfasic\Core\Validator;
use Alfasic\Models\Role;
use Alfasic\Models\User;
use Alfasic\Models\AuditLog;

/**
 * Controller de Gestão de Perfis de Acesso & Matriz de Permissões (RBAC)
 */
class RoleController
{
    /**
     * Listagem geral de perfis com contagem de usuários e resumo de permissões
     */
    public function index(): void
    {
        $roles = Role::allWithStats();

        View::render('roles/index', [
            'pageTitle' => 'Perfis',
            'title' => 'Gestão de Perfis - Alfagás',
            'roles' => $roles,
            'totalRoles' => count($roles),
        ]);
    }

    /**
     * Processa a criação ou edição de um perfil e sua matriz de permissões
     */
    public function store(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo 'Erro de validação de segurança CSRF.';
            return;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        $permissions = $_POST['permissions'] ?? [];

        if ($name === '') {
            $_SESSION['flash_error'] = 'O nome do perfil é obrigatório.';
            header('Location: /roles');
            exit;
        }

        if (!Validator::isUnique('roles', 'name', $name, $id)) {
            $_SESSION['flash_error'] = 'Já existe um perfil cadastrado com esta denominação.';
            header('Location: /roles');
            exit;
        }

        // Gera slug limpo a partir do nome
        $slug = strtolower(trim((string)preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));

        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description !== '' ? $description : null,
            'is_active' => $isActive,
        ];

        if ($id) {
            // Modo Edição
            $oldValues = Role::find($id);
            Role::update($id, $data);
            Role::syncPermissions($id, $permissions);
            $newValues = Role::find($id);

            AuditLog::record(
                entity: 'roles',
                entityId: $id,
                action: 'update',
                oldValues: $oldValues,
                newValues: $newValues,
                recordLabel: $newValues['name'] ?? null,
                contextModule: 'Perfis'
            );

            $_SESSION['flash_success'] = 'Perfil atualizado com sucesso!';
        } else {
            // Modo Novo Perfil
            if (Role::exists('slug', $slug)) {
                $slug .= '-' . time();
                $data['slug'] = $slug;
            }

            $roleId = Role::create($data);
            if ($roleId > 0) {
                Role::syncPermissions($roleId, $permissions);
            }
            $created = Role::find($roleId);

            AuditLog::record(
                entity: 'roles',
                entityId: $roleId,
                action: 'create',
                newValues: $created,
                recordLabel: $created['name'] ?? null,
                contextModule: 'Perfis'
            );

            $_SESSION['flash_success'] = 'Perfil cadastrado com sucesso!';
        }

        header('Location: /roles');
        exit;
    }

    /**
     * Exclui (soft delete) um perfil de acesso
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
            header('Location: /roles');
            exit;
        }

        // Não permite excluir se houver usuários ativos vinculados a este perfil
        $usersCount = User::count(['role_id' => $id]);
        if ($usersCount > 0) {
            header('Location: /roles?error=perfil_com_usuarios');
            exit;
        }

        $oldValues = Role::find($id);
        Role::delete($id);
        $newValues = Role::find($id, withTrashed: true);

        AuditLog::record(
            entity: 'roles',
            entityId: $id,
            action: 'soft_delete',
            reason: $reason ?: 'Exclusão de perfil de acesso',
            oldValues: $oldValues,
            newValues: $newValues,
            recordLabel: $oldValues['name'] ?? null,
            contextModule: 'Perfis'
        );

        header('Location: /roles?deleted=1');
        exit;
    }
}
