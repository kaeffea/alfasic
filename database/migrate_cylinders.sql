-- =============================================================================
-- ALFASIC - Migration tipos de cilindro + 1:1 com produtos gas (rodar 1x)
-- =============================================================================
CREATE TABLE IF NOT EXISTS cylinder_types (
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

ALTER TABLE products
    ADD COLUMN cylinder_type_id INT NULL UNIQUE AFTER ncm,
    ADD CONSTRAINT fk_products_cyltype FOREIGN KEY (cylinder_type_id) REFERENCES cylinder_types(id) ON DELETE RESTRICT;

INSERT INTO permissions (module, action, code, name, description) VALUES
('cylinders', 'view', 'cylinders.view', 'Visualizar Cilindros', 'Consultar tipos de cilindro e vínculo com gases'),
('cylinders', 'create', 'cylinders.create', 'Cadastrar Cilindros', 'Cadastrar novos tipos de cilindro'),
('cylinders', 'edit', 'cylinders.edit', 'Editar Cilindros', 'Alterar especificações de cilindros'),
('cylinders', 'delete', 'cylinders.delete', 'Excluir Cilindros', 'Inativar/excluir tipos de cilindro');

-- Concede as novas permissoes ao Administrador Geral existente
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r
JOIN permissions p ON p.code LIKE 'cylinders.%'
WHERE r.slug = 'admin'
ON DUPLICATE KEY UPDATE role_id = role_id;
