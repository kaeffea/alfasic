# 🏛️ Sistemas Legados da Alfagás & Fontes de Dados

O **Alfasic** é o resultado da consolidação de **três ecossistemas de software** utilizados pela Alfagás ao longo de mais de duas décadas de atuação no estado de Alagoas.

---

## 1. 💾 Microsoft Access (`.mdb` / `legacy_data.json` - 2004 a 2026)

* **Contexto:** Desenvolvido originalmente pelo fundador da Alfagás (Seu Cícero), o sistema em Access foi o coração operacional da empresa por mais de 20 anos.
* **Tabelas Principais:** 35 tabelas contendo todo o histórico de comodato de cilindros (`TbLocacao`), transferências São Miguel <-> Arapiraca (`TbTransferencia`), reabastecimento nas usinas (`TbPedidoGas`), licitações e empenhos municipais (`TbLicitacao`) e baixas de títulos/recibos (`TbRecibo`).
* **Volume Extraído:** 9.2 MB em `alfagas/data/legacy_data.json`.

---

## 2. 🐬 MySQL HostGator (`db_hostgator.sql` - 2021 a 2026)

* **Contexto:** Sistema web desenvolvido em PHP procedural hospedado no cPanel da HostGator (`alfaga98_alfasys`).
* **Função Principal:** Cadastro de clientes, tabela de preços negociados por cliente (`gasesap`) e **14 Contratos Master Oficiais** de licitações com vigência real (`contratos`).
* **Volume Extraído:** 154 KB em `alfagas/data/db_hostgator.sql`.

---

## 3. ☁️ TagPlus ERP Cloud (API Oficial - 2014 a 2026)

* **Contexto:** Software comercial SaaS em nuvem contratado para emissão de notas fiscais eletrônicas (NF-e) e faturamento comercial.
* **Extração Direta da API:**
  * **`vendas.json` (11.2 MB):** 13.111 vendas/notas fiscais de saída faturadas (CFOP 5102, 6102, 5949).
  * **`pedidos_full.json` (3.8 MB):** 1.303 pedidos completos baixados iterativamente via `/pedidos/{id}`, com todos os itens, fretes, observações e faturas.
  * **`fiscal_saidas_itens.csv` (5.5 MB) & `fiscal_entradas_itens.csv` (2.5 MB):** Registros fiscais detalhados com chave de 44 dígitos da NF-e, NCM, alíquotas de ICMS/PIS/COFINS e identificação dos vendedores/operadores.
  * **Produtos, Serviços, Fornecedores e Formas de Pagamento:** Mapeados e normalizados.

---

## 📁 4. Centralização Única em `alfagas/data/`

Todos os arquivos brutos e extrações originais estão centralizados exclusivamente na pasta [`alfagas/data/`](file:///z:/home/kaeffea/code/alfasic/alfagas/data), protegidos e ignorados pelo Git para garantir segurança total dos dados fiscais da empresa.
