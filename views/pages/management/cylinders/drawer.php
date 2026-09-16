<!-- Overlay e Slide-Over Drawer Lateral Direito para Cadastro e Edição de Tipo de Cilindro -->
<div id="cylinder-drawer-overlay" class="drawer-overlay" data-action="close-cylinder-drawer"></div>

<div id="cylinder-drawer" class="drawer-panel">
    <div class="drawer-header">
        <div>
            <h3 class="drawer-title" id="cylinder-drawer-heading">Novo Tipo de Cilindro</h3>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 1px;" id="cylinder-drawer-subheading">Especificações do vasilhame e vínculo 1:1 com o gás</p>
        </div>
        <button type="button" class="drawer-close" data-action="close-cylinder-drawer">&times;</button>
    </div>

    <form method="POST" action="/cylinders/store" id="cylinder-drawer-form" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
        <?= \Alfasic\Core\Csrf::input() ?>
        <input type="hidden" name="id" id="cylinder-drawer-id" value="">

        <div class="drawer-body">
            <div class="form-group">
                <label class="form-label">Nome do Tipo <span class="required">*</span></label>
                <input type="text" name="name" id="cylinder-drawer-name" required placeholder="Ex: Cilindro Aço 10 m³ Oxigênio" class="form-control">
            </div>

            <div class="form-grid-2">
                <div>
                    <label class="form-label">Código Interno</label>
                    <input type="text" name="code" id="cylinder-drawer-code" placeholder="Ex: CIL-10-O2" class="form-control font-mono">
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="is_active" id="cylinder-drawer-status" class="form-select">
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
            </div>

            <div class="form-section-divider">
                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Capacidade</label>
                        <input type="number" step="0.01" min="0" name="capacity" id="cylinder-drawer-capacity" placeholder="10.00" class="form-control font-mono">
                    </div>
                    <div>
                        <label class="form-label">Unidade</label>
                        <select name="unit" id="cylinder-drawer-unit" class="form-select">
                            <option value="m³">m³</option>
                            <option value="kg">kg</option>
                            <option value="un">un</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Pressão de Trabalho (bar)</label>
                        <input type="number" step="0.1" min="0" name="working_pressure_bar" id="cylinder-drawer-pressure" placeholder="200" class="form-control font-mono">
                    </div>
                    <div>
                        <label class="form-label">Tara (kg)</label>
                        <input type="number" step="0.01" min="0" name="tare_weight_kg" id="cylinder-drawer-tare" placeholder="15.00" class="form-control font-mono">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Valor de Reposição do Casco (R$)</label>
                    <input type="number" step="0.01" min="0" name="replacement_value" id="cylinder-drawer-value" placeholder="2500.00" class="form-control font-mono" style="font-weight: 700;">
                </div>
            </div>

            <!-- Vínculo 1:1 com o gás -->
            <div class="form-section-divider">
                <div class="form-group">
                    <label class="form-label">Gás Vinculado (1:1)</label>
                    <select name="product_id" id="cylinder-drawer-product" class="form-select">
                        <option value="">Sem vínculo por enquanto...</option>
                        <?php if (!empty($unlinkedGases)): ?>
                            <?php foreach ($unlinkedGases as $g): ?>
                                <option value="<?= (int) $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div id="cylinder-drawer-current" style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: none;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">...ou criar o gás junto</label>
                    <input type="text" name="new_product_name" id="cylinder-drawer-new-product" placeholder="Ex: Oxigênio Medicinal 10 m³" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Observações Técnicas</label>
                    <textarea name="notes" id="cylinder-drawer-notes" rows="3" placeholder="Normas, rosca de válvula, testes aplicáveis..." class="form-textarea"></textarea>
                </div>
            </div>
        </div>

        <div class="drawer-footer">
            <button type="button" class="btn btn-outline" data-action="close-cylinder-drawer" data-cancel="1">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="cylinder-drawer-submit-btn" style="padding: 8px 18px; font-weight: 700;">Salvar</button>
        </div>
    </form>
</div>

