# 🗺️ Roteiro de Aprendizado e Desenvolvimento (Learning Roadmap)

**Projeto:** Alfasic (Distribuidora de Gases Alfagás)  
**Metodologia:** Aprendizado Ativo Passo a Passo com PHP 8.5 Puro, CSS Puro e Vanilla JS Puro  

---

## 🧭 Módulos do Sistema & Status de Conclusão

| Módulo | Descrição do Conteúdo | Status |
| :--- | :--- | :---: |
| **Módulo 1: Setup & Autoload PSR-4** | Estrutura de pastas, `Autoloader.php` nativo, namespaces | ✅ Concluído |
| **Módulo 2: Roteamento HTTP & Middlewares** | `Router.php` com suporte a GET/POST e pipeline de Middlewares (Auth, Guest, Permission) | ✅ Concluído |
| **Módulo 3: Front Controller** | `public/index.php` e ciclo de vida completo de requisição/resposta | ✅ Concluído |
| **Módulo 4: Engine de Views & Componentes** | `View.php` modular, layouts mestres, componentização e fallback de caminhos | ✅ Concluído |
| **Módulo 5: Conexão PDO & Singleton** | `Database.php`, conexões seguras com Oracle MySQL HeatWave na VCN | ✅ Concluído |
| **Módulo 6: Modelagem Relacional & Migração** | 12 tabelas 3NF, migração transacional, seeds e soft deletes | ✅ Concluído |
| **Módulo 7: Mini-ORM & Late Static Binding** | `Model.php` abstrato, métodos genéricos, mass assignment e consultas relacionais | ✅ Concluído |
| **Módulo 8: Interface ERP & Vanilla JS** | Design system corporativo, busca instantânea, abas e slide-over drawers | ✅ Concluído |
| **Módulo 9: Autenticação & Níveis de Acesso (RBAC)** | Login seguro, rate limiting, sessões com hash custo 12 e matriz de 20 permissões | ✅ Concluído |
| **Módulo 10: Máscaras & Validações Reativas** | `InputMasks` fluído, `FormValidator` matemático, unicidade assíncrona e `Validator.php` | ✅ Concluído |
| **Módulo 11: Trilha de Auditoria & Motivo Obrigatório** | Log de alterações imutável `audit_logs`, justificativa de inativação/exclusão e Restore | ✅ Concluído |
| **Módulo 11b: Limpeza pré-v1** | Remove `orders/cylinders/biddings` fantasmas, REST puro, `product_*`, rollback seguro | ✅ Concluído (08/09/2026) |
| **Módulo 12: Preços base + fornecedores** | Edição tabela base em produtos, `supplier_prices` (o que cada fornecedor vende) | ⏳ **Próximo** |
| **Módulo 13: Vasilhames & Comodato** | Modelar do zero: tipos, saldo em posse, comodato/locação | ⏹️ A Fazer |
| **Módulo 14: Licitações & ARP** | Modelar do zero: cotas, empenhos, órgãos públicos | ⏹️ A Fazer |
| **Módulo 15: Pedidos (motor, por último)** | 4 tipos + CFOPs de `BUSINESS_MODEL.md`, depende de tudo acima | ⏹️ A Fazer |
| **Módulo 16: Relatórios & Notas** | Fechamentos, espelho de pedido, geração NF-e após pedidos | ⏹️ A Fazer |

---

## 🎯 Próximo Foco: Módulo 12 (Preços + Fornecedores)
1. **Produtos:** validar edição da tabela base (`standard_price`) no drawer, sem página separada.
2. **Fornecedores:** criar `supplier_prices(supplier_id, product_id, price)` + tela “quais produtos vende”.
3. Depois: cilindros → licitações → pedidos (motor por último).

