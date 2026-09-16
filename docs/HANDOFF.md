# 🤝 Documento Oficial de Handoff & Continuação de Sessão

**Projeto:** Alfasic (ERP Comercial, Operacional, Logístico e Financeiro da Alfagás)  
**Desenvolvedor:** Kauê Ferreira ([@kaeffea](https://github.com/kaeffea))  
**Data da Última Atualização:** 07 de Setembro de 2026  
**Ambiente:** Deploy de Produção Ativo em **[https://alfagas.kaeffea.me](https://alfagas.kaeffea.me)**

---

## ☁️ 1. Infraestrutura & Produção Atual

* **Domínio Oficial:** **[https://alfagas.kaeffea.me](https://alfagas.kaeffea.me)**
* **Camada de Aplicação (App Tier):** Oracle Cloud Compute VM (Ubuntu Linux, Nginx, PHP 8.5-FPM, SSL Let's Encrypt e 2 GB de memória Swap).
* **Camada de Banco de Dados (Database Tier):** **Oracle MySQL HeatWave DB System** operando 100% isolado na rede privada interna da VCN (sem IP público, latência $< 0.1\text{ ms}$).
* **Deploy Automatizado:** Git direto na VM via SSH (`git pull origin main && sudo systemctl reload php8.5-fpm`).
* **Segurança de Acesso (sem segredos neste repo — ver cofre/Nginx/`.env` na VM):**
  * **HTTP Basic Auth na Borda (Nginx):** credenciais guardadas apenas no servidor (não versionar).
  * **Sistema de Autenticação Interna do ERP (RBAC):** conta `Administrador Geral` criada via `database/setup_admin.php` (trocar senha inicial no primeiro acesso).

---

## ✂️ 2. Prompt de Retomada (Copie e Cole para Iniciar a Próxima Sessão)

```text
Olá! Estou continuando o desenvolvimento do Alfasic (ERP da Alfagás em PHP 8.5 Puro, CSS Puro e Vanilla JS Puro).
O repositório está atualizado na branch main e em produção na Oracle Cloud (https://alfagas.kaeffea.me).

Leia a documentação em docs/ para se situar:
1. docs/HANDOFF.md (Histórico do que foi feito e próximos passos planejados)
2. docs/LEARNING_ROADMAP.md (Roteiro e metodologia de desenvolvimento)
3. docs/ARCHITECTURE.md (Arquitetura MVC, roteador, engine de views e mini-ORM)
4. docs/BUSINESS_MODEL.md (Regras de negócio oficiais da Alfagás e códigos fiscais)
5. docs/DATABASE_MAP.md (Dicionário de dados das tabelas do MySQL)

Diretrizes mandatórias:
- 100% PHP 8.5 Puro, CSS Puro e Vanilla JS Puro (Zero frameworks pesados).
- Código (classes, métodos, variáveis) sempre em inglês e interface corporativa sóbria.
- Máscaras com digitação livre da direita para esquerda e sem zeros engessados.
- Validação de unicidade e matemática (CPF/CNPJ) sem exposição de dados do titular.

Pode me apresentar o resumo do estado atual e iniciarmos o próximo passo?
```

---

## 📝 3. Resumo dos Trabalhos Concluídos na Sessão

### 1. 🛡️ Motor Universal de Trilha de Auditoria & Governança (100% Completo)
* **Banco de Dados (`database/schema.sql`):**
  * Tabela `audit_logs` com índices otimizados (`idx_audit_entity`, `idx_audit_user`, `idx_audit_action`, `idx_audit_created`).
  * Permissões granulares adicionadas à matriz RBAC: `audit.view`, `audit.rollback`, `audit.force_delete`.
* **Model Universal ([`src/Models/AuditLog.php`](file:///z:/home/kaeffea/code/alfasic/src/Models/AuditLog.php)):**
  * Método `record()`: captura automática de IP, User-Agent, operador, cálculo de diff JSON e fuso de Alagoas (`America/Maceio`).
  * Régua de tempo relativo corporativa ($\le 60\text{s} \rightarrow$ segundos, $\le 60\text{min} \rightarrow$ minutos, $\le 24\text{h} \rightarrow$ horas, $\le 7\text{dias} \rightarrow$ dias, $\le 4\text{sem} \rightarrow$ semanas, $\le 12\text{meses} \rightarrow$ meses, $>12\text{meses} \rightarrow$ anos).
  * Motor de **Rollback Seguro (Time-Travel)**: desfaz alterações recriando o snapshot exato dos dados sem criar paradoxos cronológicos.
  * Motor de **Exclusão Permanente (`forceDeleteRecord`)**: checagem prévia de integridade relacional em tabelas dependentes (pedidos, endereços, contatos, compras) antes de remover fisicamente do disco.
* **Controlador & Rotas ([`src/Controllers/AuditController.php`](file:///z:/home/kaeffea/code/alfasic/src/Controllers/AuditController.php)):**
  * Rotas `GET /audit`, `POST /audit/rollback`, `POST /audit/force-delete` e `POST /audit/restore` integradas com verificação de CSRF e RBAC.
* **Integração CRUD nos Módulos:**
  * Disparos automáticos de auditoria integrados em `ClientController`, `SupplierController`, `EmployeeController`, `ProductController`, `UserController` e `RoleController`.
  * **Política de Justificativa:** Edição e Cadastro não exigem motivo; Ativação, Inativação, Soft Delete, Force Delete e Rollback exigem motivo textual obrigatório.
* **Interface Visual da Linha do Tempo ([`views/pages/system/audit/index.php`](file:///z:/home/kaeffea/code/alfasic/views/pages/system/audit/index.php)):**
  * Timeline sem tabelas, respeitando o padrão visual sóbrio (cores sólidas e discretas para cada tipo de ação, sem neon/badges).
  * Painel de filtros de pesquisa com máscara de data reativa.
  * Drawer lateral de detalhes com card limpo de metadados e visualizador de diff completo.
  * Menu dropdown de ações com fechamento automático e estilos em negrito.
  * Altura fixa padronizada e paginação consistente com as métricas do topo.

### 2. 🎨 Correções de UX e Layout na Tabela de Perfis ([`views/pages/system/roles/index.php`](file:///z:/home/kaeffea/code/alfasic/views/pages/system/roles/index.php))
* **Layout Fixo & Anti-Overflow (`table-layout: fixed; width: 100%`):** Colunas distribuídas proporcionalmente sem estourar a lateral da tela.
* **Desobstrução dos Popovers de Hover:** Removido o `overflow: hidden` das células `<td>`, permitindo que os cards de descrição e permissões flutuem livremente sobre as linhas adjacentes (`z-index: 9999`).
* **Abertura Inteligente:** Linhas 1 a 5 abrem para baixo, linhas finais abrem para cima.
* **Alinhamento do Contador (`e +X`):** Posicionado imediatamente adjacente ao texto das permissões.
* **Remoção de Tooltips Nativos Duplicados:** Removidos atributos `title="..."` que causavam sobreposição com o tooltip nativo do browser.

### 3. 🖱️ Estabilidade de Scroll Vertical no F5 (Recarregar)
* **Identificação e Correção:** Removido o atributo `autofocus` dos campos de busca de todas as telas de listagem e da Command Palette. O foco automático causava saltos bruscos no scroll para o meio da tela ao dar F5.
* **Sincronização de Abas do Workspace:** Rota `/audit` adicionada à verificação de aba ativa no renderizador de abas (`public/js/app.js`).

---

## 🧹 4. Limpeza pré-v1 — Executada em 08/09/2026

Código 100% sem legado de pedidos/cilindros/licitações (serão refeitos do zero após as páginas base):
* Deletados: `src/Models/Order.php, Bidding.php, CylinderRental.php, CylinderType.php`, `views/.../clients/orders.php, rentals.php, biddings.php`, `database/migrate_mvp_fixes.sql`.
* `database/schema.sql`: removidos `orders/order_items`; mantidos `client_addresses.is_primary`, `supplier_contacts.role_title/department/is_primary`.
* `Client`: só `clients+client_prices`, `product_*` puro, `getAppliedPrices` sem histórico de pedidos (sem linha = `standard_price`).
* Rotas REST puras em `public/index.php` (sem `/show`, `/details`, `/prices?...`, `/suppliers/create|edit` quebradas); clientes `POST /store|/update` como fornecedores/funcionários/produtos; `show/prices` com `int $id`.
* `views/.../clients/profile_header.php` só `show+prices`; `prices.php` sem seção `cylinder`, `equipment/accessory/service` agrupados em “Demais”.
* `public/js/app.js`: `initProductPriceCombobox` + IDs `product_*` (sem fallback `gas_*`).
* `HomeController` + `views/home.php`: só contadores de cadastros (pedidos virão no motor comercial).
* `AuditLog`: `checkDependencies` completa (clients→addresses/contacts/prices, suppliers→contacts, employees→users, roles→users, products→prices, users→self) com mensagens do que apagar antes; `rollback` bloqueia reversão antiga com drift e conflito UNIQUE, com ids dos logs mais recentes.
* Docs: `DATABASE_MAP.md` limpo (12 tabelas + futuro `supplier_prices/orders/cylinders/biddings`); credenciais fora daqui.

## 🎯 5. Próximos passos (na ordem)

1. **Produtos:** edição inline da tabela base (`standard_price`) no drawer já existe — validar UX, sem página separada.
2. **Fornecedores:** nova `supplier_prices(supplier_id, product_id, price)` + “quais produtos vende”.
3. **Cilindros, licitações e pedidos:** modelar do zero (pedidos por último, motor do sistema, depende de tudo). CFOPs e 4 tipos de pedido em `docs/BUSINESS_MODEL.md:97-135`.
4. **Auditoria:** leitura/download auditável depois; hoje só mutação + rollback seguro acima.
5. **Pentest:** CSRF, SQLi, bypass RBAC.
