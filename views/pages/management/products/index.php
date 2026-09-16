<?php
/**
 * View: Catálogo Geral de Produtos & Gases
 * Padrão Corporativo Oficial da Alfagás • ZERO Emojis • Faixa de Preço Min/Max Numérica
 */
$products = $products ?? [];
$allProducts = $allProducts ?? $products;
$totalCount = $totalCount ?? count($allProducts);

// Contadores para as Métricas no Fundo da Página (2 Segmentos Oficiais)
$countMedicinal = 0;
$countIndustrial = 0;
$countEquipment = 0;

foreach ($allProducts as $p) {
    $type = $p['product_type'] ?? 'gas';
    $usage = $p['usage_segment'] ?? 'industrial';

    if ($type === 'gas') {
        if ($usage === 'medicinal') {
            $countMedicinal++;
        } else {
            $countIndustrial++;
        }
    } else {
        $countEquipment++;
    }
}
?>

<style>
/* Select Estilizado com Seta SVG Espaçada da Borda */
.filter-select {
    appearance: none;
    -webkit-appearance: none;
    background-color: #ffffff;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 14px 14px;
    padding: 6px 32px 6px 10px;
    font-size: 12px;
    font-weight: 500;
    color: #1e293b;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    height: 34px;
    width: 100%;
    cursor: pointer;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.filter-input {
    background-color: #ffffff;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 500;
    color: #1e293b;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    height: 34px;
    width: 100%;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.filter-select:hover, .filter-input:hover {
    border-color: #94a3b8;
}

.filter-select:focus, .filter-input:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}

/* Paginação Estilizada ERP */
.pagination-btn {
    border: 1px solid var(--border-color);
    background: #ffffff;
    color: var(--text-main);
    padding: 6px 10px;
    min-width: 32px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.pagination-btn:hover:not(:disabled):not(.active) {
    background-color: #f1f5f9;
    border-color: #94a3b8;
}

.pagination-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.pagination-btn.active {
    background-color: var(--brand-primary);
    color: #ffffff;
    border-color: var(--brand-primary);
}

.pagination-btn.active:hover {
    background-color: #0369a1;
    color: #ffffff;
    border-color: #0369a1;
}

/* Painel de Tabela com Altura 100% Fixa e Imutável */
.fixed-table-panel {
    display: flex;
    flex-direction: column;
    background-color: var(--bg-surface);
}

.fixed-table-container {
    height: 452px;
    min-height: 452px;
    overflow: hidden;
    background-color: #ffffff;
}

.products-fixed-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.products-fixed-table th {
    height: 36px;
    padding: 6px 12px;
    box-sizing: border-box;
    background-color: var(--bg-subtle);
    border-bottom: 1px solid var(--border-color);
}

.products-fixed-table td {
    height: 52px;
    padding: 4px 12px;
    box-sizing: border-box;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.products-fixed-table tbody tr:last-child td {
    border-bottom: none;
}
</style>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
    <div>
        <h1 class="page-title">Catálogo de Produtos</h1>
        <p class="page-subtitle">Gases medicinais, industriais, equipamentos e acessórios</p>
    </div>

    <button type="button" class="btn btn-primary" data-action="open-product-drawer" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; font-size: 13px; font-weight: 700; cursor: pointer;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 4v16m8-8H4"/>
        </svg>
        <span>Novo Produto</span>
    </button>
</div>

<!-- MÉTRICAS DIRETAS NO FUNDO DA PÁGINA (FLAT STATS) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; padding: 4px 2px;">
    <div style="border-left: 3px solid #0284c7; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total de Itens</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-total"><?= $totalCount ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">No catálogo geral</div>
    </div>

    <div style="border-left: 3px solid #10b981; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">Gases Medicinais</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countMedicinal ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Hospitalar, UTIs e Resgate</div>
    </div>

    <div style="border-left: 3px solid #f59e0b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 0.05em;">Gases Industriais</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countIndustrial ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Solda, Corte, Inertes e Misturas</div>
    </div>

    <div style="border-left: 3px solid #64748b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Equipamentos & Peças</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countEquipment ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Reguladores, Carrinhos e EPIs</div>
    </div>
</div>

<!-- PAINEL ESTRUTURADO DE FILTROS DO CATÁLOGO -->
<div class="panel" style="margin-bottom: 20px;">
    <!-- Cabeçalho Limpo e Fixo com Botão de Limpar Integrado -->
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Filtros de Pesquisa</span>

        <button type="button" id="btn-reset-filters" style="background: none; border: none; font-size: 11px; font-weight: 600; color: #64748b; cursor: pointer; text-decoration: underline; visibility: hidden; opacity: 0; transition: opacity 0.15s ease;" data-action="reset-product-filters">
            Limpar filtros
        </button>
    </div>

    <!-- Corpo do Painel com os Campos de Filtro -->
    <div style="padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; background-color: var(--bg-surface);">
        <!-- Linha 1: Campo de Busca Principal -->
        <div>
            <label for="filter-search" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Busca Textual</label>
            <div class="search-wrapper" style="max-width: 100%;">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="filter-search" class="search-input" placeholder="Buscar por nome comercial, modelo, NCM ou notas técnicas..." autocomplete="off" style="height: 36px; font-size: 13px;">
            </div>
        </div>

        <!-- Linha 2: Grade de Atributos com Campos de Preço Mínimo e Máximo -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px;">
            <!-- 1. Tipo de Item -->
            <div>
                <label for="filter-type" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Tipo de Item</label>
                <select id="filter-type" class="filter-select">
                    <option value="">Todos</option>
                    <option value="gas">Gás</option>
                    <option value="equipment">Equipamento</option>
                    <option value="accessory">Acessório</option>
                </select>
            </div>

            <!-- 2. Segmento de Uso -->
            <div>
                <label for="filter-usage" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Segmento</label>
                <select id="filter-usage" class="filter-select">
                    <option value="">Todos</option>
                    <option value="medicinal">Medicinal</option>
                    <option value="industrial">Industrial</option>
                </select>
            </div>

            <!-- 3. Unidade de Medida (minúsculo) -->
            <div>
                <label for="filter-unit" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Unidade</label>
                <select id="filter-unit" class="filter-select">
                    <option value="">Todas</option>
                    <option value="m³">m³</option>
                    <option value="kg">kg</option>
                    <option value="un">un</option>
                </select>
            </div>

            <!-- 4. Capacidade Nominal (Campo Numérico) -->
            <div>
                <label for="filter-capacity" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Capacidade</label>
                <input type="number" id="filter-capacity" class="filter-input" placeholder="Ex: 10, 7, 45..." min="0" step="any">
            </div>

            <!-- 5. Faixa de Preço (Preço Mínimo e Preço Máximo) -->
            <div>
                <label style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Faixa de Preço (R$)</label>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <input type="number" id="filter-price-min" class="filter-input" placeholder="Mín" min="0" step="any" style="font-family: var(--font-mono); font-size: 12px;">
                    <span style="font-size: 11px; color: var(--text-dim);">-</span>
                    <input type="number" id="filter-price-max" class="filter-input" placeholder="Máx" min="0" step="any" style="font-family: var(--font-mono); font-size: 12px;">
                </div>
            </div>

            <!-- 6. Status Comercial -->
            <div>
                <label for="filter-status" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Status</label>
                <select id="filter-status" class="filter-select">
                    <option value="">Todos</option>
                    <option value="1">Ativos</option>
                    <option value="0">Inativos</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- PAINEL DA TABELA DE PRODUTOS COM ALTURA 100% CONSTANTE E IMUTÁVEL -->
<div class="panel fixed-table-panel">
    <!-- Cabeçalho da Lista -->
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px; height: 38px; box-sizing: border-box;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Produtos Registrados</span>

        <span id="product-result-count" style="font-size: 11px; color: var(--text-muted); font-weight: 500;">
            Mostrando <?= min(8, count($products)) ?> de <?= number_format($totalCount, 0, ',', '.') ?> produtos
        </span>
    </div>

    <!-- Tabela de Produtos em Área Flexível com Altura Fixa -->
    <div class="fixed-table-container">
        <table class="data-table products-fixed-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Produto / Descrição</th>
                    <th style="width: 110px;">Tipo</th>
                    <th style="width: 120px;">Segmento</th>
                    <th style="width: 100px;">Capacidade</th>
                    <th style="width: 160px; text-align: right;">Preço Tabela</th>
                    <th style="width: 65px; text-align: center;">Status</th>
                    <th style="text-align: center; width: 60px;">Ações</th>
                </tr>
            </thead>
            <tbody id="products-table-body">
                <?php if (empty($products)): ?>
                    <tr id="empty-row" style="height: 452px;">
                        <td colspan="8" style="text-align: center; vertical-align: middle; color: var(--text-muted);">
                            Nenhum produto cadastrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr class="product-row" 
                            data-name="<?= htmlspecialchars(strtolower($p['name'] . ' ' . ($p['notes'] ?? '') . ' ' . ($p['ncm'] ?? ''))) ?>"
                            data-type="<?= htmlspecialchars($p['product_type'] ?? 'gas') ?>"
                            data-usage="<?= htmlspecialchars($p['usage_segment'] ?? 'industrial') ?>"
                            data-unit="<?= htmlspecialchars(strtolower($p['unit'] ?? 'm³')) ?>"
                            data-capacity="<?= (float)($p['capacity'] ?? 0.0) ?>"
                            data-price="<?= (float)($p['standard_price'] ?? 0.0) ?>"
                            data-status="<?= !empty($p['is_active']) ? '1' : '0' ?>">
                            <td style="font-family: var(--font-mono); color: var(--text-dim); font-size: 11px;">#<?= $p['id'] ?></td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 13px;">
                                    <?= htmlspecialchars($p['name']) ?>
                                </div>
                                <?php if (!empty($p['notes'])): ?>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 400px;">
                                        <?= htmlspecialchars($p['notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px; color: var(--text-main);">
                                <?= htmlspecialchars($p['type_label'] ?? 'Gás') ?>
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted);">
                                <?= htmlspecialchars($p['usage_label'] ?? 'Geral') ?>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 12px; color: var(--text-main); font-weight: 600;">
                                <?= htmlspecialchars($p['capacity_display'] ?? '-') ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="font-family: var(--font-mono); font-weight: 700; color: var(--text-main); font-size: 13px;">
                                    R$ <?= number_format((float)($p['standard_price'] ?? 0.0), 2, ',', '.') ?>
                                </div>
                                <?php if (($p['product_type'] ?? '') === 'gas' && !empty($p['unit_price'])): ?>
                                    <div style="font-size: 10px; color: var(--text-muted); font-family: var(--font-mono);">
                                        R$ <?= number_format((float)$p['unit_price'], 2, ',', '.') ?> / <?= strtolower($p['unit'] ?? 'm³') ?>
                                    </div>
                                <?php else: ?>
                                    <div style="font-size: 10px; color: var(--text-dim);">
                                        unitário
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($p['is_active'])): ?>
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #10b981;" title="Ativo"></span>
                                <?php else: ?>
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #94a3b8;" title="Inativo"></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; position: relative;">
                                <div class="row-actions-dropdown">
                                    <button type="button" class="btn-row-action" title="Opções" aria-label="Opções">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                        </svg>
                                    </button>
                                    <div class="row-action-menu">
                                        <button type="button" class="action-menu-item" data-action="open-product-drawer" data-payload="<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                            <span>Editar Produto</span>
                                        </button>
                                        <form method="POST" action="/products/toggle-active" style="display: block; width: 100%;">
                                            <?= \Alfasic\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="redirect_to" value="/products">
                                            <button type="submit" class="action-menu-item">
                                                <?php if (!empty($p['is_active'])): ?>
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                    <span>Desativar Produto</span>
                                                <?php else: ?>
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                    <span>Ativar Produto</span>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                        <div class="action-menu-divider"></div>
                                        <form method="POST" action="/products/delete" style="display: block; width: 100%;" data-confirm="Deseja realmente arquivar/excluir este produto do catálogo?">
                                            <?= \Alfasic\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="action-menu-item danger">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                </svg>
                                                <span>Excluir Produto</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Rodapé de Paginação Limpo e Elegante com SVGs -->
    <div style="padding: 10px 16px; border-top: 1px solid var(--border-color); background-color: var(--bg-subtle); display: flex; justify-content: space-between; align-items: center; height: 44px; box-sizing: border-box;">
        <span style="font-size: 11px; color: var(--text-muted); font-weight: 500;">
            Página <strong id="current-page-text" style="color: var(--text-main);">1</strong> de <strong id="total-pages-text" style="color: var(--text-main);">1</strong>
        </span>

        <div style="display: flex; align-items: center; gap: 6px;" id="pagination-controls">
            <button type="button" class="pagination-btn" id="btn-prev-page" disabled data-action="change-product-page" data-delta="-1" title="Página Anterior">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div id="page-numbers" style="display: flex; gap: 4px;">
                <button type="button" class="pagination-btn active">1</button>
            </div>
            <button type="button" class="pagination-btn" id="btn-next-page" disabled data-action="change-product-page" data-delta="1" title="Próxima Página">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>
</div>

<!-- Slide-Over Drawer (Modal Lateral à Direita) para Novo/Edição de Produto -->
<?php require __DIR__ . '/drawer.php'; ?>

<script src="<?= \Alfasic\Core\View::asset('/js/pages/products-index.js') ?>" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES, 'UTF-8') ?>"></script>
