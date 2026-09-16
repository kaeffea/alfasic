# 🗄️ Mapeamento do Banco de Dados Alfasic (MySQL 8.4)

> Fonte da verdade: `database/schema.sql`.
> Atualizado em 08/09/2026 após limpeza pré-v1. Banco intencionalmente vazio (só seed `permissions`).

Modelo greenfield normalizado, InnoDB `utf8mb4_unicode_ci`, soft delete (`deleted_at`) + `is_active` onde aplicável.
Páginas existentes: Gestão (clientes, produtos, fornecedores, funcionários) + Sistema (usuários, perfis, auditoria).

---

## 1. Acesso & RBAC
* **`roles`**: `id, name UNIQUE, slug UNIQUE, description, is_active, deleted_at, created_at, updated_at`.
* **`permissions`**: catálogo fixo via `INSERT` (23 códigos): `clients.view/create/edit/delete/prices_view/prices_edit`, `suppliers.*`, `employees.*`, `products.*`, `users.manage`, `roles.manage`, `audit.view/rollback/force_delete`. Sem `deleted_at` (não usar `Model::delete()` aqui).
* **`role_permissions`**: PK composta `(role_id, permission_id)`, FK cascade ambos lados.

## 2. RH
* **`employees`**: identificação (`name, document UNIQUE, rg, birth_date/city/state`), lotação (`branch, department, role_title, work_shift, hire_date, contract_type`), CNH/MOPP (`driver_license, category, expiry, has_mopp, certifications`), custos (`base_salary, has_peril_bonus, benefits_cost, pix_key`), contato/emergência, endereço, `notes, is_active, deleted_at`.

## 3. Usuários
* **`users`**: `employee_id NULL → SET NULL`, `role_id → RESTRICT`, `username/email UNIQUE`, `password_hash`, `is_active, last_login_at, remember_token, deleted_at` (o e-mail corporativo é o segundo fator: código de 6 dígitos, 10 min).
* **`trusted_devices`**: cookie `alfasic_device` 30 dias (seletor + hash com rotação); dispensa o código no mesmo navegador.

## 4. Clientes
* **`clients`**: `name, trade_name, document UNIQUE, state_registration, municipal_registration, client_type, contact_person, phone, mobile, email`, endereço sede, comercial (`payment_terms, billing_method, credit_limit, has_rental_charge, last_order_date`), `notes, is_active, deleted_at`.
* **`client_addresses`**: N endereços (`entrega/faturamento/cobranca`): `logradouro, numero, complemento, bairro, cidade, estado, cep, ponto_referencia, is_primary`, FK `client_id → CASCADE`.
* **`client_contacts`**: `name, role, phone, email`, FK `client_id → CASCADE`.
* **`client_prices`**: `client_id + product_id UNIQUE (uq_client_product)`, `applied_price, freight_price, rental_price, notes`, FKs `CASCADE`. Sem linha = vale `products.standard_price`.

## 5. Fornecedores
* **`suppliers`**: `name, trade_name, document UNIQUE, state_registration, contact_person, phone, mobile, email`, endereço, `supplier_type`, `notes, is_active, deleted_at`.
* **`supplier_contacts`**: `name, role (legado espelhado), role_title, department, phone, email, is_primary`, FK `supplier_id → CASCADE`.
* Futuro (não existe): `supplier_prices(supplier_id, product_id, price)` — quais produtos cada fornecedor vende e por quanto.

## 6. Catálogo (produtos; gás é um tipo)
* **`products`**: `name, product_type (gas/equipment/accessory/service), usage_segment (medicinal/industrial), unit, capacity, unit_price, standard_price (tabela base), ncm, cylinder_type_id UNIQUE NULL, notes, is_active, deleted_at`. Não existe `gases/gas_families` — recarga de gás é `product_type='gas'` e **exige** `cylinder_type_id`. Preço base editado no drawer de produtos, sem página separada.
* **`cylinder_types`**: `name, code UNIQUE, capacity, unit, working_pressure_bar, tare_weight_kg, replacement_value (patrimônio do casco), notes, is_active, deleted_at`. Vínculo 1:1 com o gás (FK em `products`, `RESTRICT` nos dois sentidos via aplicação).

## 7. Auditoria
* **`audit_logs`** (imutável, sem `deleted_at`): `user_id → SET NULL, user_name, user_role, entity (clients/suppliers/employees/products/users/roles), entity_id, action (create/update/activate/deactivate/soft_delete/restore/force_delete/rollback), record_label, reason, old_values JSON, new_values JSON, diff_fields JSON, diff_summary TEXT, context_module, ip_address, user_agent, is_reversible, reverted_at, reverted_by_log_id, created_at`.

## 8. Ainda NÃO existem (serão refeitos do zero)
Motor comercial por último (depende das páginas acima): `orders/order_items` (pedidos, 4 tipos em `BUSINESS_MODEL.md`), `cylinder_*` (vasilhames/comodato), `biddings/bidding_items` (ARP), `supplier_prices`. Regras fiscais/CFOP em `docs/BUSINESS_MODEL.md:97-117`.
