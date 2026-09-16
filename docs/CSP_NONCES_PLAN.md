# Plano CSP sem `unsafe-inline` (nonces + handlers delegados)

> Status: planejado em 08/09/2026. NÃO executar às cegas — exige teste clicado logado.
> Motivação: hoje `script-src 'self' 'unsafe-inline'` permite que qualquer `<script>`/`onclick`
> injetado execute. Mitigações ativas: `htmlspecialchars` em todo output + cookies `HttpOnly`.

## Inventário (medido em 08/09/2026)

| Tipo | Qtd | Onde |
| :--- | :---: | :--- |
| Handlers inline (`onclick/onsubmit/oninput/onchange`) em views | ~135 linhas | `audit/index`, `clients/index`, `login`, `roles`, `suppliers`, `products`, `employees`, `users`, drawers, `profile_header`, `audit_modal`, `app.php`, `table_pagination` |
| Handlers em templates gerados por JS | ~13 | `public/js/app.js` (abas `WorkspaceTabs.*`, `open*Drawer(${json})`, `onsubmit confirm`) |
| Blocos `<script>` inline (quase todos JS puro, sem PHP) | 12 | um por página de listagem + `login.php` + `layouts/app.php` — ✅ externalizados em `public/js/pages/*.js` em 08/09/2026 (byte-exato, `node --check` OK; `audit` via bloco JSON + `prices` já era JSON) |
| JSON embutido (`<script type="application/json">`) | 1 | `clients/prices.php` (`catalog-products-data`) — esse é **seguro** e fica |

## Estratégia (4 fases, nesta ordem)

### Fase 1 — Infra de nonce + Report-Only (sem quebrar nada) ✅ APLICADA
> Correção importante: nonce + `'unsafe-inline'` na MESMA policy vigente NÃO é
> canário — navegadores modernos ignoram `unsafe-inline` em scripts quando há
> nonce, ou seja, quebraria tudo de imediato. Por isso:
1. `public/index.php`: `$GLOBALS['csp_nonce']` por requisição; policy vigente
   inalterada + header `Content-Security-Policy-Report-Only: script-src 'self'
   'nonce-...'` (só observa: violações aparecem no console, nada bloqueia).
2. `Core/View.php`: `$cspNonce` disponível em todas as views.
3. `layouts/app.php` e `auth/login.php`: `nonce="..."` nos scripts (valida o
   encanamento: somem do relatório de violações).

### Fase 2 — Externalizar blocos `<script>` puros
Um arquivo por página em `public/js/pages/*.js` (ex: `products-index.js`, `login.js`),
`<script src="..." nonce>` na mesma posição do bloco (preserva ordem de execução;
`app.js` carrega depois, então nada pode chamar função dele no load — só em eventos).
Validar: `node --check` + teste clicado da página.

### Fase 3 — Handlers → delegação (o grosso)
Padrão único, sem reescrever lógica:
- `onclick="openClientDrawer({...json...})"` → `data-action="open-client-drawer"`
  + `data-payload='{...json...}'` + **um** listener delegado em `app.js`
  (`document.addEventListener('click', ...)` com mapa ação→função existente).
- `onsubmit="return confirm('...')"` → `data-confirm="..."` + listener delegado de `submit`.
- Templates gerados em `app.js`: mesma técnica na string do template.
Ordem sugerida (uma por vez, com teste): clients → suppliers → employees →
products → users → roles → audit → login → componentes.

### Fase 4 — Enforcement
1. Remover `'unsafe-inline'` de `script-src` (manter em `style-src`: risco baixo, custo alto).
2. Testar TODAS as páginas logado com console aberto (zero `Refused to execute`).
3. Conferir Page Shield (Client-Side Security) sem novos alertas.
4. Só então considerar o item encerrado.

## Status final (08/09/2026): FASES 1–4 APLICADAS ✅
- Fase 1 (nonce + Report-Only) → Fase 2 (12 blocos externalizados) →
  Fase 3 (~100 handlers → `data-action`/`data-confirm`/delegação, 1 commit por módulo) →
  Fase 4 (`unsafe-inline` removido de `script-src`; Report-Only aposentado).
- Extras no caminho: `table_pagination.php` morto deletado, `page_header` sem
  `onclick` arbitrário, payloads JSON com `escapeAttr` (corrige apóstrofos que o
  `onclick` antigo quebrava), `beacon.min.js` liberado, bug SPA pré-existente
  (`let` redeclarado ao revisitar abas) corrigido com `var` + harness double-run.
- Mantido de propósito: `'unsafe-inline'` em `style-src`.

## O que NÃO fazer

- Não usar hashes CSP (quebram a cada edição de JS).
- Não tocar em `style=""`/`style-src 'unsafe-inline'` nesta issue.
- Não fazer tudo num commit só — um módulo por commit para `git bisect` funcionar.
- `<script type="application/json">` não precisa de nonce (não executa).
