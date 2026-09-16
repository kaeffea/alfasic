-- =============================================================================
-- ALFASIC - Sistema Integrado de Gestão Comercial, Operacional & Financeira
-- Schema MySQL 8.0 / 8.4 Normalizado (3FN) • InnoDB • utf8mb4_unicode_ci
-- Padrão Estrito de Soft Delete (deleted_at) em Todas as Entidades
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. CONTROLE DE ACESSO & SEGURANÇA (RBAC)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_roles_slug (slug),
    INDEX idx_roles_active (is_active),
    INDEX idx_roles_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    code VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_permissions_module (module),
    INDEX idx_permissions_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. QUADRO DE FUNCIONÁRIOS & RH
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS employees;

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    document VARCHAR(30) NULL UNIQUE,
    rg VARCHAR(30) NULL,
    birth_date DATE NULL,
    birth_city VARCHAR(100) NULL,
    birth_state VARCHAR(2) NULL,
    branch VARCHAR(100) NOT NULL DEFAULT 'Matriz - São Miguel dos Campos',
    department VARCHAR(100) NOT NULL DEFAULT 'Operacional',
    role_title VARCHAR(100) NOT NULL,
    work_shift VARCHAR(50) NOT NULL DEFAULT 'Comercial',
    hire_date DATE NULL,
    contract_type VARCHAR(30) NOT NULL DEFAULT 'CLT',
    base_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    has_peril_bonus TINYINT NOT NULL DEFAULT 0,
    benefits_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    pix_key VARCHAR(100) NULL,
    driver_license VARCHAR(30) NULL,
    driver_license_category VARCHAR(10) NULL,
    driver_license_expiry DATE NULL,
    has_mopp TINYINT NOT NULL DEFAULT 0,
    certifications VARCHAR(255) NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255) NULL,
    emergency_contact_name VARCHAR(150) NULL,
    emergency_contact_phone VARCHAR(50) NULL,
    address VARCHAR(255) NULL,
    address_number VARCHAR(50) NULL,
    neighborhood VARCHAR(100) NULL,
    city VARCHAR(100) NOT NULL DEFAULT 'São Miguel dos Campos',
    state VARCHAR(2) NOT NULL DEFAULT 'AL',
    zip_code VARCHAR(20) NULL,
    notes TEXT NULL,
    is_active TINYINT NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employees_branch (branch),
    INDEX idx_employees_department (department),
    INDEX idx_employees_role (role_title),
    INDEX idx_employees_active (is_active),
    INDEX idx_employees_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. CONTAS DE USUÁRIOS DO SISTEMA
-- -----------------------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NULL,
    role_id INT NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    remember_token VARCHAR(100) NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_users_username (username),
    INDEX idx_users_email (email),
    INDEX idx_users_active (is_active),
    INDEX idx_users_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. CLIENTES & CONDICIONAMENTO COMERCIAL
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS client_contacts;
DROP TABLE IF EXISTS client_addresses;
DROP TABLE IF EXISTS client_prices;
DROP TABLE IF EXISTS clients;

CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    trade_name VARCHAR(255) NULL,
    document VARCHAR(30) NULL UNIQUE,
    state_registration VARCHAR(30) NULL,
    municipal_registration VARCHAR(30) NULL,
    client_type VARCHAR(30) NOT NULL DEFAULT 'company',
    contact_person VARCHAR(100) NULL,
    phone VARCHAR(50) NULL,
    mobile VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    address VARCHAR(255) NULL,
    address_number VARCHAR(20) NULL,
    address_complement VARCHAR(100) NULL,
    neighborhood VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(2) NOT NULL DEFAULT 'AL',
    zip_code VARCHAR(15) NULL,
    payment_terms VARCHAR(50) DEFAULT 'A Vista',
    billing_method VARCHAR(50) DEFAULT 'Boleto',
    credit_limit DECIMAL(12,2) DEFAULT 0.00,
    has_rental_charge TINYINT NOT NULL DEFAULT 0,
    last_order_date DATE NULL,
    notes TEXT NULL,
    is_active TINYINT NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clients_document (document),
    INDEX idx_clients_city (city),
    INDEX idx_clients_active (is_active),
    INDEX idx_clients_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE client_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    address_type VARCHAR(30) NOT NULL DEFAULT 'entrega',
    description VARCHAR(255) NULL,
    logradouro VARCHAR(255) NOT NULL,
    numero VARCHAR(20) NULL,
    complemento VARCHAR(100) NULL,
    bairro VARCHAR(100) NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(2) NOT NULL DEFAULT 'AL',
    cep VARCHAR(15) NULL,
    ponto_referencia VARCHAR(255) NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_client_addresses_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_client_addresses_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE client_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100) NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_client_contacts_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_client_contacts_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. FORNECEDORES & USINAS DE REABASTECIMENTO
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS supplier_contacts;
DROP TABLE IF EXISTS suppliers;

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    trade_name VARCHAR(255) NULL,
    document VARCHAR(30) NULL UNIQUE,
    state_registration VARCHAR(30) NULL,
    contact_person VARCHAR(100) NULL,
    phone VARCHAR(50) NULL,
    mobile VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    address VARCHAR(255) NULL,
    address_number VARCHAR(20) NULL,
    address_complement VARCHAR(100) NULL,
    neighborhood VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(2) NOT NULL DEFAULT 'AL',
    zip_code VARCHAR(15) NULL,
    supplier_type VARCHAR(50) NOT NULL DEFAULT 'usina',
    notes TEXT NULL,
    is_active TINYINT NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_suppliers_document (document),
    INDEX idx_suppliers_type (supplier_type),
    INDEX idx_suppliers_active (is_active),
    INDEX idx_suppliers_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE supplier_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100) NULL,
    role_title VARCHAR(100) NULL,
    department VARCHAR(100) NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_supplier_contacts_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    INDEX idx_supplier_contacts_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. CATÁLOGO GERAL DE PRODUTOS, GASES & EQUIPAMENTOS
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS gases;
DROP TABLE IF EXISTS gas_families;
DROP TABLE IF EXISTS products;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    product_type VARCHAR(30) NOT NULL DEFAULT 'gas',
    usage_segment VARCHAR(30) NOT NULL DEFAULT 'industrial',
    unit VARCHAR(10) NOT NULL DEFAULT 'm³',
    capacity DECIMAL(10,2) NULL,
    unit_price DECIMAL(10,2) NULL,
    standard_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ncm VARCHAR(20) NULL,
    notes TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cylinder_type_id INT NULL UNIQUE,
    INDEX idx_products_type_segment (product_type, usage_segment),
    INDEX idx_products_ncm (ncm),
    INDEX idx_products_active (is_active),
    INDEX idx_products_deleted (deleted_at),
    CONSTRAINT fk_products_cyltype FOREIGN KEY (cylinder_type_id) REFERENCES cylinder_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6b. TIPOS DE CILINDRO (1:1 com produto do tipo gás)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS cylinder_types;

CREATE TABLE cylinder_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NULL UNIQUE,
    capacity DECIMAL(10,2) NULL,
    unit VARCHAR(10) NOT NULL DEFAULT 'm³',
    working_pressure_bar DECIMAL(10,2) NULL,
    tare_weight_kg DECIMAL(10,2) NULL,
    replacement_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    notes TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cyltypes_code (code),
    INDEX idx_cyltypes_active (is_active),
    INDEX idx_cyltypes_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. TABELA DE PREÇOS NEGOCIADOS POR CLIENTE
-- -----------------------------------------------------------------------------
CREATE TABLE client_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    product_id INT NOT NULL,
    applied_price DECIMAL(10,2) NOT NULL,
    freight_price DECIMAL(10,2) DEFAULT 0.00,
    rental_price DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_client_product (client_id, product_id),
    CONSTRAINT fk_client_prices_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    CONSTRAINT fk_client_prices_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_client_prices_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 8. SEGUNDO FATOR POR E-MAIL (OTP) & DISPOSITIVOS CONFIÁVEIS (30 dias)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS trusted_devices;

CREATE TABLE trusted_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector VARCHAR(32) NOT NULL UNIQUE,
    validator_hash VARCHAR(255) NOT NULL,
    user_agent VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    expires_at DATETIME NOT NULL,
    last_used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_td_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_td_selector (selector),
    INDEX idx_td_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 9. TRILHA DE AUDITORIA UNIVERSAL & GOVERNANÇA (Audit Logs & Time-Travel)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS audit_logs;

CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    user_name VARCHAR(150) NOT NULL,
    user_role VARCHAR(100) NULL,
    entity VARCHAR(50) NOT NULL,          -- clients, suppliers, employees, products, users, roles
    entity_id INT NOT NULL,               -- ID do registro
    action VARCHAR(50) NOT NULL,          -- create, update, activate, deactivate, soft_delete, restore, force_delete, rollback
    record_label VARCHAR(255) NULL,       -- Rótulo legível (ex: "Hospital Santa Rita", "Oxigênio 10m³")
    reason TEXT NULL,                     -- Justificativa (obrigatória em inativações, exclusões e reversões)
    old_values JSON NULL,                 -- Snapshot anterior (para diff e reversão)
    new_values JSON NULL,                 -- Snapshot atual
    diff_fields JSON NULL,                -- Lista de campos alterados: ["phone", "credit_limit"]
    diff_summary TEXT NULL,               -- Resumo legível dos campos alterados
    context_module VARCHAR(100) NOT NULL DEFAULT 'web', -- Clientes, Fornecedores, Auditoria, etc.
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    is_reversible TINYINT(1) NOT NULL DEFAULT 1,
    reverted_at DATETIME NULL,
    reverted_by_log_id BIGINT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_entity_record (entity, entity_id),
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_context (context_module),
    INDEX idx_audit_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 10. CATÁLOGO MASTER DE PERMISSÕES DO SISTEMA
-- -----------------------------------------------------------------------------
INSERT INTO permissions (module, action, code, name, description) VALUES
('clients', 'view', 'clients.view', 'Visualizar Clientes', 'Consultar listagem e fichas de clientes'),
('clients', 'create', 'clients.create', 'Cadastrar Clientes', 'Cadastrar novos clientes'),
('clients', 'edit', 'clients.edit', 'Editar Clientes', 'Alterar dados cadastrais de clientes'),
('clients', 'delete', 'clients.delete', 'Excluir Clientes', 'Inativar/excluir cadastros de clientes'),
('clients', 'prices_view', 'clients.prices_view', 'Ver Preços', 'Consultar tabela de preços exclusivos'),
('clients', 'prices_edit', 'clients.prices_edit', 'Alterar Preços', 'Modificar valores e condições comerciais'),
('suppliers', 'view', 'suppliers.view', 'Visualizar Fornecedores', 'Consultar listagem e fichas de fornecedores'),
('suppliers', 'create', 'suppliers.create', 'Cadastrar Fornecedores', 'Cadastrar novos fornecedores'),
('suppliers', 'edit', 'suppliers.edit', 'Editar Fornecedores', 'Alterar dados de fornecedores'),
('suppliers', 'delete', 'suppliers.delete', 'Excluir Fornecedores', 'Inativar/excluir fornecedores'),
('employees', 'view', 'employees.view', 'Visualizar Funcionários', 'Consultar quadro de funcionários'),
('employees', 'create', 'employees.create', 'Cadastrar Funcionários', 'Cadastrar novos colaboradores'),
('employees', 'edit', 'employees.edit', 'Editar Funcionários', 'Alterar dados e registros de funcionários'),
('employees', 'delete', 'employees.delete', 'Excluir Funcionários', 'Inativar/excluir colaboradores'),
('products', 'view', 'products.view', 'Visualizar Produtos', 'Consultar catálogo de produtos e gases'),
('products', 'create', 'products.create', 'Cadastrar Produtos', 'Cadastrar novos produtos e gases'),
('products', 'edit', 'products.edit', 'Editar Produtos', 'Alterar especificações de produtos'),
('products', 'delete', 'products.delete', 'Excluir Produtos', 'Inativar/excluir produtos do catálogo'),
('cylinders', 'view', 'cylinders.view', 'Visualizar Cilindros', 'Consultar tipos de cilindro e vínculo com gases'),
('cylinders', 'create', 'cylinders.create', 'Cadastrar Cilindros', 'Cadastrar novos tipos de cilindro'),
('cylinders', 'edit', 'cylinders.edit', 'Editar Cilindros', 'Alterar especificações de cilindros'),
('cylinders', 'delete', 'cylinders.delete', 'Excluir Cilindros', 'Inativar/excluir tipos de cilindro'),
('users', 'manage', 'users.manage', 'Gerenciar Usuários', 'Criar, editar e revogar contas de usuários'),
('roles', 'manage', 'roles.manage', 'Gerenciar Perfis & Permissões', 'Configurar grupos de acesso e matriz RBAC'),
('audit', 'view', 'audit.view', 'Visualizar Auditoria', 'Consultar linha do tempo e histórico de auditoria'),
('audit', 'rollback', 'audit.rollback', 'Reverter Ações', 'Reverter alterações históricas na base de dados'),
('audit', 'force_delete', 'audit.force_delete', 'Excluir Permanentemente', 'Excluir registros físicos permanentemente após verificação de integridade');

SET FOREIGN_KEY_CHECKS = 1;
