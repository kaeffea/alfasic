<?php

declare(strict_types=1);

// 1. Sessão endurecida + headers de segurança (OWASP)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');
    session_start();
}

// Nonce por requisicao para a futura CSP sem 'unsafe-inline' (ver docs/CSP_NONCES_PLAN.md).
// IMPORTANTE: enquanto a policy vigente tiver nonce, navegadores modernos IGNORAM
// 'unsafe-inline' em scripts — por isso o nonce novo so vai em Report-Only (zero quebra).
$GLOBALS['csp_nonce'] = base64_encode(random_bytes(16));

if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    // CSP Fase 4: scripts SEM 'unsafe-inline' (so 'self' + nonce + insights da CF).
    // Mantido em style-src (risco baixo, custo alto). Ver docs/CSP_NONCES_PLAN.md.
    header("Content-Security-Policy: default-src 'self'; object-src 'none'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'nonce-{$GLOBALS['csp_nonce']}' https://static.cloudflareinsights.com; connect-src 'self' https://cloudflareinsights.com; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
}

// 2. Registra o Autoloader PSR-4 nativo
require_once dirname(__DIR__) . '/src/Core/Autoloader.php';
\Alfasic\Core\Autoloader::register();

use Alfasic\Core\Router;
use Alfasic\Controllers\HomeController;
use Alfasic\Controllers\AuthController;
use Alfasic\Controllers\ClientController;
use Alfasic\Controllers\SupplierController;
use Alfasic\Controllers\EmployeeController;
use Alfasic\Controllers\ProductController;
use Alfasic\Controllers\CylinderController;
use Alfasic\Controllers\UserController;
use Alfasic\Controllers\RoleController;
use Alfasic\Controllers\AuditController;
use Alfasic\Controllers\ValidationController;

// 3. Inicializa o Roteador HTTP
$router = new Router();

// -----------------------------------------------------------------------------
// 4. ROTAS PÚBLICAS / AUTENTICAÇÃO
// -----------------------------------------------------------------------------
$router->get('/login', [AuthController::class, 'loginForm'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest']);
$router->get('/2fa', [AuthController::class, 'twofaForm'], ['guest']);
$router->post('/2fa/verify', [AuthController::class, 'twofaVerify'], ['guest']);
$router->post('/2fa/resend', [AuthController::class, 'twofaResend'], ['guest']);
$router->post('/logout', [AuthController::class, 'logout']);

// -----------------------------------------------------------------------------
// 5. ROTAS PROTEGIDAS POR AUTENTICAÇÃO & RBAC
// -----------------------------------------------------------------------------
$router->get('/', [HomeController::class, 'index'], ['auth']);
$router->get('/workspace/empty', [HomeController::class, 'emptyWorkspace'], ['auth']);
$router->get('/api/validate-unique', [ValidationController::class, 'checkUnique'], ['auth']);

// 5.1. Módulo Clientes (REST puro: sem aliases ?id=)
$router->get('/clients', [ClientController::class, 'index'], ['auth', 'permission:clients.view']);
$router->get('/api/clients/search', [ClientController::class, 'searchApi'], ['auth', 'permission:clients.view']);
$router->get('/clients/create', [ClientController::class, 'create'], ['auth', 'permission:clients.create']);
$router->post('/clients/store', [ClientController::class, 'store'], ['auth', 'permission:clients.create']);
$router->get('/clients/edit', [ClientController::class, 'edit'], ['auth', 'permission:clients.edit']);
$router->post('/clients/update', [ClientController::class, 'update'], ['auth', 'permission:clients.edit']);
$router->post('/clients/prices/save', [ClientController::class, 'savePrice'], ['auth', 'permission:clients.prices_edit']);
$router->post('/clients/prices/delete', [ClientController::class, 'deletePrice'], ['auth', 'permission:clients.prices_edit']);
$router->post('/clients/delete', [ClientController::class, 'delete'], ['auth', 'permission:clients.delete']);
$router->post('/clients/toggle-active', [ClientController::class, 'toggleActive'], ['auth', 'permission:clients.edit']);
$router->get('/clients/{id}', [ClientController::class, 'show'], ['auth', 'permission:clients.view']);
$router->get('/clients/{id}/prices', [ClientController::class, 'prices'], ['auth', 'permission:clients.prices_view']);

// 5.2. Módulo Fornecedores (drawer: store novo, update edição)
$router->get('/suppliers', [SupplierController::class, 'index'], ['auth', 'permission:suppliers.view']);
$router->get('/api/suppliers/search', [SupplierController::class, 'searchApi'], ['auth', 'permission:suppliers.view']);
$router->post('/suppliers/store', [SupplierController::class, 'store'], ['auth', 'permission:suppliers.create']);
$router->post('/suppliers/update', [SupplierController::class, 'update'], ['auth', 'permission:suppliers.edit']);
$router->post('/suppliers/delete', [SupplierController::class, 'delete'], ['auth', 'permission:suppliers.delete']);
$router->post('/suppliers/toggle-active', [SupplierController::class, 'toggleActive'], ['auth', 'permission:suppliers.edit']);
$router->get('/suppliers/{id}', [SupplierController::class, 'show'], ['auth', 'permission:suppliers.view']);

// 5.3. Módulo Funcionários (drawer: store novo, update edição)
$router->get('/employees', [EmployeeController::class, 'index'], ['auth', 'permission:employees.view']);
$router->post('/employees/store', [EmployeeController::class, 'store'], ['auth', 'permission:employees.create']);
$router->post('/employees/update', [EmployeeController::class, 'update'], ['auth', 'permission:employees.edit']);
$router->post('/employees/delete', [EmployeeController::class, 'delete'], ['auth', 'permission:employees.delete']);
$router->post('/employees/toggle-active', [EmployeeController::class, 'toggleActive'], ['auth', 'permission:employees.edit']);
$router->get('/employees/{id}', [EmployeeController::class, 'show'], ['auth', 'permission:employees.view']);

// 5.4. Módulo Produtos & Catálogo
$router->get('/products', [ProductController::class, 'index'], ['auth', 'permission:products.view']);
$router->post('/products/store', [ProductController::class, 'store'], ['auth', 'permission:products.create']);
$router->post('/products/delete', [ProductController::class, 'delete'], ['auth', 'permission:products.delete']);
$router->post('/products/toggle-active', [ProductController::class, 'toggleActive'], ['auth', 'permission:products.edit']);
$router->get('/api/products/search', [ProductController::class, 'searchApi'], ['auth', 'permission:products.view']);

// 5.4b. Módulo Tipos de Cilindro (1:1 com gases)
$router->get('/cylinders', [CylinderController::class, 'index'], ['auth', 'permission:cylinders.view']);
$router->post('/cylinders/store', [CylinderController::class, 'store'], ['auth', 'permission:cylinders.create']);
$router->post('/cylinders/delete', [CylinderController::class, 'delete'], ['auth', 'permission:cylinders.delete']);
$router->post('/cylinders/toggle-active', [CylinderController::class, 'toggleActive'], ['auth', 'permission:cylinders.edit']);
$router->get('/api/cylinders/search', [CylinderController::class, 'searchApi'], ['auth', 'permission:cylinders.view']);

// 5.5. Módulo Gestão de Usuários
$router->get('/users', [UserController::class, 'index'], ['auth', 'permission:users.manage']);
$router->post('/users/store', [UserController::class, 'store'], ['auth', 'permission:users.manage']);
$router->post('/users/delete', [UserController::class, 'delete'], ['auth', 'permission:users.manage']);
$router->post('/users/reset-2fa', [UserController::class, 'reset2fa'], ['auth', 'permission:users.manage']);

// 5.6. Módulo Gestão de Perfis & Permissões (RBAC)
$router->get('/roles', [RoleController::class, 'index'], ['auth', 'permission:roles.manage']);
$router->post('/roles/store', [RoleController::class, 'store'], ['auth', 'permission:roles.manage']);
$router->post('/roles/delete', [RoleController::class, 'delete'], ['auth', 'permission:roles.manage']);

// 5.7. Módulo Gestão de Auditoria & Governança (Audit Trail & Time-Travel)
$router->get('/audit', [AuditController::class, 'index'], ['auth', 'permission:audit.view']);
$router->post('/audit/rollback', [AuditController::class, 'rollback'], ['auth', 'permission:audit.rollback']);
$router->post('/audit/force-delete', [AuditController::class, 'forceDelete'], ['auth', 'permission:audit.force_delete']);
$router->post('/audit/restore', [AuditController::class, 'restore'], ['auth', 'permission:audit.rollback']);

// 6. Processa e Despacha a Requisição Atual
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
