<?php
/**
 * View: Tabela de Preços & Condições Comerciais do Cliente
 * Regra: se não houver linha em client_prices, vale products.standard_price.
 */
$activeTab = 'prices';
require __DIR__ . '/profile_header.php';
?>

<!-- Painel de Cadastro / Ajuste de Preço Negociado -->
<div class="panel" style="margin-bottom: 24px; padding: 20px; overflow: visible;">
    <h2 class="panel-title" style="margin-bottom: 14px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
        + Definir ou Atualizar Preço de Produto para este Cliente
    </h2>

    <form method="POST" action="/clients/prices/save" style="display: grid; grid-template-columns: 2.5fr 1fr 140px; gap: 14px; align-items: flex-end;">
        <?= \Alfasic\Core\Csrf::input() ?>
        <input type="hidden" name="client_id" value="<?= (int) $client['id'] ?>">

        <div class="combobox-wrapper">
            <label class="info-item-label" for="product_search_input">Selecione o Produto do Catálogo *</label>
            <input type="text" id="product_search_input" class="search-input" style="width: 100%; padding: 8px 12px; height: 38px;" placeholder="Digite para buscar produto (ex: Oxigênio, Acetileno, Argônio)..." autocomplete="off" required>
            <input type="hidden" name="product_id" id="product_id" required>
            <div id="product-combobox-dropdown" class="combobox-dropdown"></div>
        </div>

        <div>
            <label class="info-item-label" for="price">Preço Negociado (R$) *</label>
            <input type="text" name="price" id="price" class="search-input" style="width: 100%; padding: 8px 12px; height: 38px;" placeholder="0,00" required>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="width: 100%; height: 38px; justify-content: center;">
                Salvar Preço
            </button>
        </div>
    </form>
</div>

<?php
$gasPrices = array_filter($prices, fn($p) => ($p['product_type'] ?? 'gas') === 'gas');
$otherPrices = array_filter($prices, fn($p) => ($p['product_type'] ?? 'gas') !== 'gas');
?>

<!-- 1. Tabela de Recargas de Gases (product_type = gas, principal) -->
<div class="panel" style="margin-bottom: 24px;">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="panel-title">
            Recargas de Gases (<?= count($gasPrices) ?> itens)
        </h2>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Gás / Especificação</th>
                    <th style="width: 15%;">Categoria</th>
                    <th style="width: 15%; text-align: right;">Capacidade</th>
                    <th style="width: 12%; text-align: right;">Preço Cheio Balcão</th>
                    <th style="width: 13%; text-align: right;">Preço Praticado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($gasPrices)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 24px 16px; color: var(--text-muted);">
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); margin-bottom: 4px;">
                                Nenhum produto com condição especial.
                            </div>
                            <div style="font-size: 12px; color: var(--text-dim);">
                                Sem preço negociado, vale a tabela base de produtos.
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($gasPrices as $p): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?= htmlspecialchars($p['product_name']) ?></div>
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted);">
                                <?= ($p['usage_segment'] ?? '') === 'medicinal' ? 'Medicinal' : 'Industrial' ?>
                            </td>
                            <td style="text-align: right; font-family: var(--font-mono); font-size: 11px;">
                                <?= (float) $p['capacity'] > 0 ? round((float) $p['capacity'], 2) . ' ' . htmlspecialchars($p['unit']) : '-' ?>
                            </td>
                            <td style="text-align: right; color: var(--text-muted); font-family: var(--font-mono);">
                                R$ <?= number_format((float) $p['base_price'], 2, ',', '.') ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; font-family: var(--font-mono); color: var(--text-main); font-size: 13px;">
                                R$ <?= number_format((float) $p['applied_price'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 2. Demais Produtos (equipment/accessory/service) -->
<?php if (!empty($otherPrices)): ?>
<div class="panel">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="panel-title">
            Equipamentos, Acessórios & Serviços (<?= count($otherPrices) ?> itens)
        </h2>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 75%;">Produto</th>
                    <th style="width: 12%; text-align: right;">Preço Base</th>
                    <th style="width: 13%; text-align: right;">Preço Praticado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($otherPrices as $p): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600;"><?= htmlspecialchars($p['product_name']) ?></div>
                        </td>
                        <td style="text-align: right; color: var(--text-muted); font-family: var(--font-mono);">
                            R$ <?= number_format((float) $p['base_price'], 2, ',', '.') ?>
                        </td>
                        <td style="text-align: right; font-weight: 700; font-family: var(--font-mono); color: var(--text-main); font-size: 13px;">
                            R$ <?= number_format((float) $p['applied_price'], 2, ',', '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script type="application/json" id="catalog-products-data">
<?= json_encode(array_map(function($g) {
    return [
        'id' => (int)$g['id'],
        'name' => $g['name'],
        'capacity' => (float)$g['capacity'] > 0 ? round((float)$g['capacity'], 2) . ' ' . $g['unit'] : '',
        'base_price' => (float)$g['standard_price'],
        'base_price_formatted' => number_format((float)$g['standard_price'], 2, ',', '.')
    ];
}, $allProducts ?? []), JSON_UNESCAPED_UNICODE) ?>
</script>
