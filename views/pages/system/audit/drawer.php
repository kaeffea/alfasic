<!-- Overlay e Slide-Over Drawer Lateral Direito para Detalhes e Diff de Auditoria -->
<div id="audit-drawer-overlay" class="drawer-overlay" data-action="close-audit-drawer"></div>

<div id="audit-drawer" class="drawer-panel" style="max-width: 640px; width: 100%;">
    <div class="drawer-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px; background: var(--bg-card); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 class="drawer-title" id="audit-drawer-title" style="margin: 0; font-size: 15px; font-weight: 700;">Detalhes da Operação</h3>
            <p style="font-size: 11px; color: var(--text-muted); margin: 2px 0 0 0;" id="audit-drawer-subtitle">-</p>
        </div>
        <button type="button" class="drawer-close" data-action="close-audit-drawer">&times;</button>
    </div>

    <div class="drawer-body" style="padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px;">
        
        <!-- 1. Metadados Forenses da Sessão & Origem (Clean & Harmonioso) -->
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px 16px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-dim); margin-bottom: 10px;">
                Sessão & Origem
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 11px;">Autor:</span>
                    <strong id="audit-drawer-user" style="color: var(--text-main); font-weight: 600;">-</strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 11px;">Módulo:</span>
                    <strong id="audit-drawer-module" style="color: var(--text-main); font-weight: 600;">-</strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 11px;">Endereço IP:</span>
                    <span id="audit-drawer-ip" style="font-family: var(--font-mono); color: var(--text-main);">-</span>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 11px;">Data & Horário:</span>
                    <span id="audit-drawer-date-exact" style="font-family: var(--font-mono); color: var(--text-main);">-</span>
                </div>
                <div style="grid-column: span 2;">
                    <span style="color: var(--text-muted); display: block; font-size: 11px;">Navegador / Dispositivo:</span>
                    <span id="audit-drawer-user-agent" style="font-size: 11px; color: var(--text-muted); word-break: break-all; font-family: var(--font-mono);">-</span>
                </div>
            </div>
        </div>

        <!-- 2. Motivo da Operação (Clean, Sem Cores Berrantes) -->
        <div id="audit-drawer-reason-box" style="display: none; background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px 14px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); letter-spacing: 0.04em; margin-bottom: 4px;">
                Motivo
            </div>
            <div id="audit-drawer-reason-text" style="font-size: 12px; color: var(--text-main); line-height: 1.4;">
                -
            </div>
        </div>

        <!-- 3. Comparador de Dados (Exibe Todos os Dados Sempre) -->
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-dim);">
                Comparativo de Dados
            </div>

            <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; background: #ffffff;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                    <thead>
                        <tr style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); color: var(--text-dim); font-size: 11px; text-transform: uppercase;">
                            <th style="padding: 8px 12px; width: 30%;">Campo</th>
                            <th style="padding: 8px 12px; width: 35%;">Antes</th>
                            <th style="padding: 8px 12px; width: 35%;">Depois</th>
                        </tr>
                    </thead>
                    <tbody id="audit-diff-tbody">
                        <!-- Linhas geradas via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="drawer-footer" style="padding: 14px 20px; border-top: 1px solid var(--border-color); background: var(--bg-card); display: flex; justify-content: flex-end; gap: 8px;">
        <button type="button" class="btn btn-outline btn-sm" data-action="close-audit-drawer">Fechar</button>
    </div>
</div>
