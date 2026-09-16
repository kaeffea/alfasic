<!-- Overlay e Slide-Over Drawer Lateral Direito para Cadastro e Edição de Produto -->
<div id="product-drawer-overlay" class="drawer-overlay" data-action="close-product-drawer"></div>

<div id="product-drawer" class="drawer-panel">
    <div class="drawer-header">
        <div>
            <h3 class="drawer-title" id="product-drawer-heading">Novo Cadastro de Produto</h3>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 1px;" id="product-drawer-subheading">Preencha as informações do produto, gás ou equipamento</p>
        </div>
        <button type="button" class="drawer-close" data-action="close-product-drawer">&times;</button>
    </div>

    <form method="POST" action="/products/store" id="product-drawer-form" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
        <?= \Alfasic\Core\Csrf::input() ?>
        <input type="hidden" name="id" id="drawer-product-id" value="">

        <div class="drawer-body">
            <!-- 1. Classificação: Tipo e Segmento (Apenas Medicinal e Industrial) -->
            <div class="form-grid-2">
                <div>
                    <label class="form-label">Tipo de Item <span class="required">*</span></label>
                    <select name="product_type" id="drawer-product-type" required data-action-change="product-type-change" class="form-select">
                        <option value="gas">Gás Comprimido / Criogênico</option>
                        <option value="equipment">Equipamento / Aparelho</option>
                        <option value="accessory">Acessório / Peça / EPI</option>
                        <option value="service">Serviço / Locação</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Segmento de Uso <span class="required">*</span></label>
                    <select name="usage_segment" id="drawer-product-usage" required class="form-select">
                        <option value="medicinal">Medicinal / Hospitalar</option>
                        <option value="industrial">Industrial / Solda / Corte</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nome Comercial do Produto <span class="required">*</span></label>
                <input type="text" name="name" id="drawer-product-name" required placeholder="Ex: Oxigênio Medicinal 10 m³ ou Regulador de Pressão Oxigênio" class="form-control">
            </div>

            <div class="form-group" id="drawer-product-cyltype-wrapper">
                <label class="form-label">Tipo de Cilindro Vinculado <span class="required">*</span></label>
                <select name="cylinder_type_id" id="drawer-product-cyltype" class="form-select">
                    <option value="">Selecione o tipo (obrigatório p/ gás)...</option>
                    <?php if (!empty($cylinderTypes)): ?>
                        <?php foreach ($cylinderTypes as $ct): ?>
                            <option value="<?= (int) $ct['id'] ?>"><?= htmlspecialchars($ct['name']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div style="font-size: 11px; color: var(--text-dim); margin-top: 4px;">Gás exige vínculo 1:1. Não-gás ignora este campo. <a href="/cylinders">Gerenciar tipos</a></div>
            </div>

            <!-- 2. Unidade, Capacidade & Precificação Dinâmica (Por m³/kg ou Cheio) -->
            <div class="form-section-divider">
                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Unidade de Medida <span class="required">*</span></label>
                        <select name="unit" id="drawer-product-unit" required data-action-change="product-unit-change" class="form-select">
                            <option value="m³">m³</option>
                            <option value="kg">kg</option>
                            <option value="un">un</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Capacidade Nominal</label>
                        <input type="number" step="0.01" min="0" name="capacity" id="drawer-product-capacity" data-action-input="product-price-input" placeholder="10.00" class="form-control font-mono">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div id="wrapper-unit-price">
                        <label id="label-unit-price" class="form-label">Preço Unitário (R$ / m³ ou kg)</label>
                        <input type="number" step="0.01" min="0" name="unit_price" id="drawer-product-unit-price" data-action-input="product-price-input" placeholder="10.50" class="form-control font-mono">
                    </div>
                    <div>
                        <label class="form-label">Preço de Tabela Total (R$) <span class="required">*</span></label>
                        <input type="number" step="0.01" min="0" name="standard_price" id="drawer-product-price" required placeholder="105.00" class="form-control font-mono" style="font-weight: 700;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Código NCM (Classificação Fiscal)</label>
                    <input type="text" name="ncm" id="drawer-product-ncm" data-mask="ncm" placeholder="Ex: 2804.40.00 (Oxigênio) ou 9019.20.10 (Oxigenoterapia)" class="form-control font-mono">
                </div>
            </div>

            <!-- 3. Status & Observações -->
            <div class="form-section-divider">
                <div class="form-grid-2">
                    <div>
                        <label class="form-label">Status do Item</label>
                        <select name="is_active" id="drawer-product-status" class="form-select">
                            <option value="1">Ativo (Disponível no Catálogo)</option>
                            <option value="0">Inativo (Indisponível)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Observações Técnicas / Aplicação</label>
                    <textarea name="notes" id="drawer-product-notes" rows="3" placeholder="Especificações técnicas, pressão de trabalho, compatibilidade de válvulas ou normas..." class="form-textarea"></textarea>
                </div>
            </div>
        </div>

        <div class="drawer-footer">
            <button type="button" class="btn btn-outline" data-action="close-product-drawer" data-cancel="1">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="drawer-product-submit-btn" style="padding: 8px 18px; font-weight: 700;">Salvar</button>
        </div>
    </form>
</div>

<script src="<?= \Alfasic\Core\View::asset('/js/pages/products-drawer.js') ?>" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES, 'UTF-8') ?>"></script>
