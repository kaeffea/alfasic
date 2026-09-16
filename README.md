# Alfasic 🧪

Sistema Integrado de Gestão Comercial, Operacional, Logística e Financeira para Distribuição de Gases Industriais, Medicinais e Rastreamento de Cilindros/Vasilhames (**Alfagás**).

Desenvolvido em **PHP 8.5+ Puro**, **CSS Puro** e **Vanilla JS Puro** com arquitetura MVC modular, Mini-ORM nativo e sem dependências externas.

---

## 🌐 Deploy de Produção

* **URL Oficial:** [https://alfagas.kaeffea.me](https://alfagas.kaeffea.me)
* **Infraestrutura:** Oracle Cloud Compute VM (Ubuntu Linux + Nginx + PHP 8.5-FPM + SSL Let's Encrypt) + **Oracle MySQL HeatWave DB System** na VCN privada interna.

---

## 📚 Documentação do Sistema

Toda a documentação técnica, regras de negócio e planejamento estão centralizados na pasta [`docs/`](docs/):

* 🤝 **[Documento de Handoff & Prompt de Continuação](docs/HANDOFF.md):** Status atualizado do projeto, módulos concluídos e prompt oficial de retomada de sessões.
* 🏛️ **[Arquitetura do Sistema](docs/ARCHITECTURE.md):** Filosofia PHP puro, Front Controller, Autoloader PSR-4, Router com Middlewares, Views e ciclo de vida HTTP.
* 🧪 **[Modelo de Negócio Alfagás](docs/BUSINESS_MODEL.md):** Catálogo de gases, locação, comodato de vasilhames, clientes industriais/hospitalares e licitações (ARPs).
* 🗄️ **[Mapeamento do Banco de Dados](docs/DATABASE_MAP.md):** Dicionário completo das tabelas relacionais do MySQL HeatWave.
* 🏛️ **[Sistemas Legados & Fontes de Dados](docs/LEGACY_SYSTEMS.md):** Mapeamento do Access (2004-2026), MySQL HostGator e API do TagPlus.
* 🔒 **[Segurança & Privacidade](docs/SECURITY.md):** Políticas de proteção contra CSRF, XSS, SQL Injection, sessões seguras e RBAC.
* 🎓 **[Trilha de Aprendizado & Metodologia](docs/LEARNING_ROADMAP.md):** Roteiro de aprendizado, boas práticas e planejamento dos módulos.

---

## 📁 Estrutura do Repositório

```text
alfasic/
├── public/                 # Único diretório exposto ao servidor web (Front Controller e assets)
├── src/                    # Código-fonte da aplicação (Core, Controllers, Middlewares, Models)
├── views/                  # Templates de visualização (pages/management, pages/system, components, layouts)
├── database/               # Esquema SQL (schema.sql), scripts de migração e seeds
├── alfagas/                # Fontes de dados históricas (Access, MySQL e TagPlus)
└── docs/                   # Documentação técnica e guias de aprendizado
```

---

## 🚀 Como Executar Localmente

Utilizando o servidor embutido nativo do PHP:

```bash
php -S 0.0.0.0:8000 -t public
```

Acesse no navegador: `http://localhost:8000`

