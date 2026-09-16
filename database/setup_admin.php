<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Core/Autoloader.php';
\Alfasic\Core\Autoloader::register();

use Alfasic\Core\Database;
use Alfasic\Models\Role;
use Alfasic\Models\User;
use Alfasic\Models\Employee;

echo "=== Configurando Administrador Geral Corporativo no Alfasic ===\n";

$pdo = Database::getConnection();

// 1. Cria ou busca o perfil Administrador Geral
$adminRole = Role::findBy('slug', 'admin');
if (!$adminRole) {
    echo "Criando perfil Administrador Geral...\n";
    $roleId = Role::create([
        'name' => 'Administrador Geral',
        'slug' => 'admin',
        'description' => 'Acesso total e irrestrito a todos os recursos, cadastros e configurações do sistema',
        'is_active' => 1
    ]);
} else {
    $roleId = (int)$adminRole['id'];
}

// 2. Sincroniza todas as permissões para o Administrador Geral
echo "Sincronizando todas as permissões com o perfil Admin...\n";
Role::syncPermissions($roleId, ['all']);

// 3. Cria ou busca o colaborador Kauê Ferreira (CPF de teste matematicamente válido)
$employee = Employee::findBy('document', '529.982.247-25');
if (!$employee) {
    $employeeId = Employee::create([
        'name' => 'Kauê Ferreira',
        'document' => '529.982.247-25',
        'branch' => 'Matriz - São Miguel dos Campos',
        'department' => 'TI / Administração',
        'role_title' => 'Desenvolvedor / Administrador Geral',
        'work_shift' => 'Comercial',
        'contract_type' => 'CLT',
        'phone' => '(82) 99999-9999',
        'email' => 'kaeffea@gmail.com',
        'is_active' => 1
    ]);
} else {
    $employeeId = (int)$employee['id'];
}

// 4. Remove qualquer usuário que não siga o padrão corporativo
$pdo->exec("DELETE FROM users WHERE username = 'kaeffea'");

// 5. Cria a conta corporativa única 'kaue.ferreira' (senha via env, nunca hardcoded)
// Uso: ADMIN_INITIAL_PASSWORD='...' php database/setup_admin.php
// Para trocar a senha depois, use a tela de Usuários (com sessão logada), não este script.
$initialPassword = getenv('ADMIN_INITIAL_PASSWORD') ?: ($_SERVER['ADMIN_INITIAL_PASSWORD'] ?? '');
$userCorp = User::findBy('username', 'kaue.ferreira');
if (!$userCorp) {
    if ($initialPassword === '') {
        fwrite(STDERR, "Defina ADMIN_INITIAL_PASSWORD no ambiente para criar o admin inicial.\n");
        exit(1);
    }
    echo "Criando usuário corporativo 'kaue.ferreira'...\n";
    User::createWithPassword([
        'username' => 'kaue.ferreira',
        'email' => 'kaeffea@gmail.com',
        'role_id' => $roleId,
        'employee_id' => $employeeId,
        'is_active' => 1
    ], $initialPassword);
    echo "✓ Usuário 'kaue.ferreira' criado com sucesso! Troque a senha no primeiro login.\n";
} else {
    echo "Usuário 'kaue.ferreira' já existe — nada alterado (troque a senha pela tela de Usuários).\n";
}

echo "=== Configuração Concluída com Sucesso! ===\n";
