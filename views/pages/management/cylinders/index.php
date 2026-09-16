<?php
/**
 * View: Tipos de Cilindro (vasilhames de alta pressão, 1:1 com gases)
 */
$types = $types ?? [];
$totalCount = $totalCount ?? count($types);
$search = $search ?? '';

$countLinked = 0;
$countOrphan = 0;
foreach ($types as $t) {
    if (!empty($t['product_id'])) $countLinked++;
    else $countOrphan++;
}
$unlinkedCount = $unlinkedCount ?? 0;
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
    <div>
        <h1 class="page-title">Tipos de Cilindro</h1>
        <p class="page-subtitle">Vasilhames de alta pressão • vínculo 1:1 com gases</p>
    </div>

    <button type="button" class="btn btn-primary" data-action="open-cylinder-drawer" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; font-size: 13px; font-weight: 700; cursor: pointer;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 4v16m8-8H4"/>
        </svg>
        <span>Novo Tipo</span>
    </button>
</div>

<!-- Métricas -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; padding: 4px 2px;">
    <div style="border-left: 3px solid #0284c7; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total de Tipos</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $totalCount ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">No catálogo</div>
    </div>

    <div style="border-left: 3px solid #10b981; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">Com Gás Vinculado</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countLinked ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Par 1:1 completo</div>
    </div>

    <div style="border-left: 3px solid #f59e0b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 0.05em;">Sem Gás (Órfãos)</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countOrphan ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Aguardando vínculo</div>
    </div>

    <div style="border-left: 3px solid #64748b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Gases sem Tipo</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= (int) $unlinkedCount ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Exigem vínculo</div>
    </div>
</div>

<!-- Busca simples (GET, sem JS) -->
<div class="panel" style="margin-bottom: 20px; padding: 14px 16px;">
    <form method="GET" action="/cylinders" style="display: flex; gap: 10px;">
        <input type="text" name="q" class="search-input" style="flex: 1; height: 36px;" placeholder="Buscar por nome, código ou notas..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
        <button type="submit" class="btn btn-outline" style="height: 36px;">Buscar</button>
        <?php if ($search !== ''): ?>
            <a href="/cylinders" class="btn btn-outline" style="height: 36px; text-decoration: none;">Limpar</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tabela -->
<div class="panel fixed-table-panel">
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px; height: 38px; box-sizing: border-box;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Tipos Registrados</span>
        <span style="font-size: 11px; color: var(--text-muted); font-weight: 500;">
            <?= number_format(count($types), 0, ',', '.') ?> tipo(s)
        </span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Tipo / Especificação</th>
                    <th style="width: 110px;">Capacidade</th>
                    <th style="width: 100px;">Pressão</th>
                    <th style="width: 130px; text-align: right;">Valor Reposição</th>
                    <th style="width: 220px;">Gás Vinculado (1:1)</th>
                    <th style="width: 65px; text-align: center;">Status</th>
                    <th style="text-align: center; width: 60px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($types)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; vertical-align: middle; color: var(--text-muted); padding: 30px;">
                            Nenhum tipo de cilindro cadastrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($types as $t): ?>
                        <tr>
                            <td style="font-family: var(--font-mono); color: var(--text-dim); font-size: 11px;">#<?= $t['id'] ?></td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 13px;">
                                    <?= htmlspecialchars($t['name']) ?>
                                </div>
                                <?php if (!empty($t['code'])): ?>
                                    <div style="font-size: 11px; color: var(--text-muted); font-family: var(--font-mono);">
                                        <?= htmlspecialchars($t['code']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 12px; font-weight: 600;">
                                <?= (float)($t['capacity'] ?? 0) > 0 ? round((float)$t['capacity'], 2) . ' ' . htmlspecialchars($t['unit'] ?? '') : '-' ?>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 12px; color: var(--text-muted);">
                                <?= !empty($t['working_pressure_bar']) ? round((float)$t['working_pressure_bar'], 1) . ' bar' : '-' ?>
                            </td>
                            <td style="text-align: right; font-family: var(--font-mono); font-weight: 700; font-size: 13px;">
                                R$ <?= number_format((float)($t['replacement_value'] ?? 0), 2, ',', '.') ?>
                            </td>
                            <td>
                                <?php if (!empty($t['product_name'])): ?>
                                    <span style="font-size: 12px; font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($t['product_name']) ?></span>
                                <?php else: ?>
                                    <span style="font-size: 11px; font-weight: 700; color: #d97706;">SEM GÁS</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($t['is_active'])): ?>
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
                                        <button type="button" class="action-menu-item" data-action="open-cylinder-drawer" data-payload="<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                            <span>Editar Tipo</span>
                                        </button>
                                        <form method="POST" action="/cylinders/toggle-active" style="display: block; width: 100%;">
                                            <?= \Alfasic\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                            <input type="hidden" name="redirect_to" value="/cylinders">
                                            <button type="submit" class="action-menu-item">
                                                <?php if (!empty($t['is_active'])): ?>
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                    <span>Desativar Tipo</span>
                                                <?php else: ?>
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                    <span>Ativar Tipo</span>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                        <div class="action-menu-divider"></div>
                                        <form method="POST" action="/cylinders/delete" style="display: block; width: 100%;" data-confirm="Arquivar este tipo de cilindro? (Bloqueado se houver gás vinculado.)">
                                            <?= \Alfasic\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                            <button type="submit" class="action-menu-item danger">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                </svg>
                                                <span>Excluir Tipo</span>
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
</div>

<?php require __DIR__ . '/drawer.php'; ?>
