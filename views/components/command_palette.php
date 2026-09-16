<!-- Command Palette Global (Acessível via F2 ou tecla barra [/]) -->
<div id="cmd-overlay" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; display: none; align-items: flex-start; justify-content: center; padding-top: 14vh;">
    <div style="background: #ffffff; border-radius: 10px; width: 560px; max-width: 90vw; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border: 1px solid #cbd5e1; overflow: hidden;">
        <div style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #fafafa;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #64748b;">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="text" id="cmd-input" placeholder="Buscar clientes, gases, atalhos..." style="border: none; background: transparent; width: 100%; font-size: 14px; outline: none; font-weight: 600; color: #0f172a;" autocomplete="off">
            <span style="font-size: 10px; background: #e2e8f0; color: #475569; padding: 2px 6px; border-radius: 4px; font-family: var(--font-mono); font-weight: 700;">ESC</span>
        </div>

        <div style="padding: 6px; max-height: 300px; overflow-y: auto;" id="cmd-results">
            <div style="font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; padding: 6px 10px 2px 10px; letter-spacing: 0.05em;">Ações Rápidas</div>
            <a href="/clients/create" class="cmd-item" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-radius: 5px; text-decoration: none; color: #0f172a; font-size: 13px;">
                <strong>+ Novo Cliente</strong>
                <span style="font-size: 10px; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-family: var(--font-mono);">Tecla N</span>
            </a>
            <a href="/clients" class="cmd-item" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-radius: 5px; text-decoration: none; color: #0f172a; font-size: 13px;">
                <strong>Clientes</strong>
                <span style="font-size: 10px; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-family: var(--font-mono);">1.105</span>
            </a>
        </div>

        <div style="padding: 8px 14px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 11px; color: #64748b;">
            <span>Atalho: <strong>/</strong> (Barra)</span>
            <span>Fechar: <strong>Esc</strong></span>
        </div>
    </div>
</div>

<!-- Toast Box Global -->
<div id="toast-box" style="position: fixed; bottom: 24px; right: 24px; background: #0f172a; color: #ffffff; padding: 10px 18px; border-radius: 6px; font-size: 12px; font-weight: 600; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 99999999; display: none; align-items: center; gap: 8px; border: 1px solid #334155;">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="toast-text">Notificação</span>
</div>
