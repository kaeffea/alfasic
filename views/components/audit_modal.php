<!-- Modal Global Corporativo de Justificativa Obrigatória & Confirmações Críticas -->
<div id="audit-action-modal-overlay" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9998; align-items: center; justify-content: center; padding: 16px;">
    <div id="audit-action-modal" class="modal-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; animation: modalFadeIn 0.2s ease-out;">
        
        <!-- Modal Header -->
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: var(--bg-subtle);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div id="audit-modal-icon" style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: #fee2e2; color: #ef4444;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <h3 id="audit-modal-title" style="margin: 0; font-size: 15px; font-weight: 800; color: var(--text-main);">Confirmar Ação</h3>
            </div>
            <button type="button" data-action="close-audit-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Modal Form -->
        <form id="audit-action-form" method="POST" action="" data-action-submit="audit-form-submit" style="margin: 0;">
            <?= \Alfasic\Core\Csrf::input() ?>
            <input type="hidden" name="id" id="audit-modal-target-id" value="">
            <input type="hidden" name="entity" id="audit-modal-entity" value="">
            <input type="hidden" name="redirect_to" id="audit-modal-redirect-to" value="">
            <input type="hidden" name="action_type" id="audit-modal-action-type" value="">

            <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
                <!-- Card de Identificação do Registro -->
                <div style="background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px 16px;">
                    <span style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.05em; display: block;" id="audit-modal-entity-label">Registro Afetado:</span>
                    <div style="font-size: 14px; font-weight: 700; color: var(--text-main); margin-top: 2px;" id="audit-modal-record-title">-</div>
                    <div style="font-size: 11px; color: var(--text-dim); margin-top: 2px;" id="audit-modal-record-desc">-</div>
                </div>

                <!-- Mensagem de Contexto / Impacto -->
                <p id="audit-modal-message" style="font-size: 13px; color: var(--text-main); margin: 0; line-height: 1.5;">
                    Informe o motivo para prosseguir com esta operação.
                </p>

                <!-- Campo de Justificativa / Motivo Obrigatório -->
                <div id="audit-modal-reason-container" class="form-group" style="margin: 0;">
                    <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Motivo / Justificativa da Operação <span class="required" id="audit-modal-reason-req">*</span></span>
                        <span id="audit-modal-char-count" style="font-size: 11px; color: var(--text-dim); font-weight: 400;">Mínimo 5 caracteres</span>
                    </label>
                    <textarea name="reason" id="audit-modal-reason-input" class="form-control" rows="3" required
                        placeholder="Descreva detalhadamente o motivo da inativação, exclusão ou reversão..."
                        style="resize: vertical; font-size: 13px; line-height: 1.4;"></textarea>
                    <div id="audit-modal-reason-error" style="display: none; color: #ef4444; font-size: 11px; margin-top: 4px; font-weight: 600;">
                        A justificativa é obrigatória e deve conter pelo menos 5 caracteres.
                    </div>
                </div>

                <!-- Aviso Específico de Exclusão Permanente (quando aplicável) -->
                <div id="audit-modal-hard-delete-warning" style="display: none; background: #fef2f2; border: 1px solid #fee2e2; border-left: 4px solid #ef4444; border-radius: var(--radius-md); padding: 12px 14px;">
                    <div style="font-size: 11px; font-weight: 800; color: #991b1b; text-transform: uppercase;">⚠️ AVISO DE EXCLUSÃO DEFINITIVA</div>
                    <div style="font-size: 12px; color: #b91c1c; margin-top: 2px;">
                        Esta ação removerá o registro permanentemente do banco de dados e <strong>NÃO PODERÁ SER DESFEITA</strong>.
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 16px 24px; border-top: 1px solid var(--border-color); background: var(--bg-subtle); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline btn-sm" data-action="close-audit-modal">Cancelar</button>
                <button type="submit" id="audit-modal-submit-btn" class="btn btn-danger btn-sm" style="display: flex; align-items: center; gap: 6px;">
                    <span id="audit-modal-submit-text">Confirmar Operação</span>
                </button>
            </div>
        </form>
    </div>
</div>