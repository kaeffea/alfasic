# 🔒 Diretrizes de Segurança & Privacidade do Alfasic

Este documento estabelece as políticas de segurança, privacidade e integridade técnica aplicadas no **Alfasic**.

---

## 1. 🛡️ Arquitetura Multi-Tier & Isolamento de Rede Privada (VCN)

* **Banco de Dados 100% Privado (Sem IP Público):** O banco de dados **Oracle MySQL HeatWave** opera em rede privada VCN interna, totalmente invisível e inacessível pela internet pública.
* **Firewall Interno (Security Lists):** Apenas a VM Web possui permissão de tráfego na porta 3306 dentro da VCN privada.
* **Acesso Administrativo Seguro (Túnel SSH):** Conexões externas de ferramentas como Navicat e DBeaver são obrigatoriamente tuneladas via SSH (porta 22 com chave criptográfica RSA/ED25519), sem abrir a porta do MySQL para o mundo.

---

## 2. 🔐 Proteção de Borda & Variáveis de Ambiente

* **HTTP Basic Auth no Nginx:** O domínio de produção [https://alfagas.kaeffea.me](https://alfagas.kaeffea.me) é protegido na borda pelo Nginx (`auth_basic`), bloqueando robôs, scanners e acessos não autorizados antes mesmo de chegar ao PHP.
* **Isolamento de Credenciais (`.env`):**
  * Todas as senhas, hosts e configurações sensíveis ficam restritas ao arquivo `.env`.
  * O `.env` está explicitamente bloqueado no [`.gitignore`](file:///z:/home/kaeffea/code/alfasic/.gitignore) e **nunca é versionado no Git**.
  * No Nginx, o acesso a arquivos com extensões `.env`, `.sql`, `.sqlite` e `.log` retorna `404 Not Found` por regra de servidor.
* **Proteção de Scripts de Automação:** O script [`deploy.sh`](file:///z:/home/kaeffea/code/alfasic/deploy.sh) é restrito à máquina local e mantido fora do repositório Git.

---

## 3. 🛡️ Proteção Contra Vulnerabilidades Web (OWASP Top 10)

### Proteção Contra SQL Injection
* **100% das consultas SQL utilizam Prepared Statements** (`PDO::prepare()`) com bind seguro de parâmetros (`:param` ou `?`).
* Nenhuma concatenação direta de variáveis de entrada do usuário em strings SQL.

### Proteção Contra CSRF (Cross-Site Request Forgery)
* Toda requisição `POST` exige validação de token CSRF (`Csrf::verify($_POST['csrf_token'])`).
* Formulários utilizam `<?= \Alfasic\Core\Csrf::input() ?>` para injeção automática de tokens criptograficamente seguros (`random_bytes(32)`).

### Proteção Contra XSS (Cross-Site Scripting)
* Todas as saídas de texto dinâmico nas Views passam obrigatoriamente por `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.

### Criptografia de Trânsito (HTTPS / TLS 1.3)
* Certificados oficiais **Let's Encrypt** com redirecionamento forçado de HTTP (porta 80) para HTTPS (porta 443) e renovação automática perpétua via Certbot.

---

## 4. 📜 Trilha de Auditoria Universal & Governança Anti-Fraude

* **Logs Imutáveis de Mutação (`audit_logs`):** Cada operação de inserção, alteração, ativação, desativação, exclusão e reversão é registrada permanentemente no banco de dados com IP de origem, User-Agent, carimbo de data/hora (fuso horário oficial de Alagoas `America/Maceio`), identificação do operador e payload integral de `old_values` e `new_values`.
* **Política de Justificativa Obrigatória:**
  * Ativações, Inativações, Exclusões Lógicas (Soft Delete), Exclusões Permanentes (Force Delete) e Reversões (Rollback) exigem obrigatoriamente um **motivo textual do operador**, bloqueando o envio em caso de campo vazio ou com menos de 3 caracteres.
* **Mecanismo Seguro de Time-Travel Rollback:**
  * O processo de reversão reconstrói o estado exato dos dados sem criar paradoxos cronológicos, gerando um novo registro de auditoria do tipo `rollback` com o diff reverso e preservando toda a linhagem histórica da entidade.
* **Integridade Referencial na Exclusão Permanente:**
  * A exclusão física definitiva (`force_delete`) passa por checagem estrita de dependências relacionais no banco (ex: pedidos vinculados, endereços, notas fiscais, vasilhames), bloqueando a destruição de dados caso existam vínculos operacionais ativos.
* **Controle de Acesso Baseado em Papéis (RBAC):**
  * O acesso à tela e às ações de auditoria é protegido pelas permissões granulares `audit.view`, `audit.rollback` e `audit.force_delete`.
