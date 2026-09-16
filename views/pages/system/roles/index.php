<?php
/**
 * View: Gestão de Perfis de Acesso & Permissões (RBAC)
 * Padrão Corporativo Oficial da Alfagás • ZERO Emojis • Tabela ERP de Altura Fixa
 */
$roles = $roles ?? [];

$totalRoles = count($roles);
$countActive = count(array_filter($roles, fn($r) => !empty($r['is_active'])));
$countInactive = count(array_filter($roles, fn($r) => empty($r['is_active'])));
$totalAssignedUsers = array_sum(array_column($roles, 'users_count'));
?>

<style>
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

/* Painel de Tabela com Altura Fixa */
.fixed-table-panel {
    display: flex;
    flex-direction: column;
    background-color: var(--bg-surface);
    position: relative;
    overflow: visible !important;
}

.fixed-table-container {
    height: 452px;
    min-height: 452px;
    overflow: visible !important;
    background-color: #ffffff;
    position: relative;
}

.roles-fixed-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
    table-layout: fixed;
}

.roles-fixed-table th {
    height: 36px;
    padding: 6px 12px;
    box-sizing: border-box;
    background-color: var(--bg-subtle);
    border-bottom: 1px solid var(--border-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.roles-fixed-table td {
    height: 52px;
    max-height: 52px;
    padding: 4px 12px;
    box-sizing: border-box;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
    white-space: nowrap;
    overflow: visible;
}

.roles-fixed-table tbody tr:last-child td {
    border-bottom: none;
}

/* Popover Persistente e Copiável de Permissões */
.perm-hover-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    min-width: 0;
}

.perm-text-summary {
    font-size: 12px;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: default;
    max-width: 100%;
}

.perm-popover-card {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    width: 360px;
    max-width: min(380px, 90vw);
    max-height: 230px;
    background: #0f172a;
    color: #f8fafc;
    border: 1px solid #334155;
    border-radius: 6px;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.28), 0 4px 10px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    padding: 12px 14px;
    font-size: 11.5px;
    line-height: 1.5;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.18s ease, visibility 0.18s ease;
    user-select: text;
    cursor: text;
    white-space: normal;
    word-break: break-word;
}

.perm-popover-card::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 0;
    width: 100%;
    height: 8px;
    background: transparent;
}

.perm-hover-wrapper:hover .perm-popover-card,
.perm-popover-card:hover {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transition-delay: 0.25s;
}

/* Apenas a partir da 6ª linha abre para cima */
.role-row:nth-child(n+6) .perm-popover-card {
    top: auto;
    bottom: calc(100% + 6px);
}
.role-row:nth-child(n+6) .perm-popover-card::before {
    bottom: auto;
    top: 100%;
}
</style>

<!-- Cabeçalho da Página -->
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
    <div>
        <h1 class="page-title">Gestão de Perfis</h1>
        <p class="page-subtitle">Definição de grupos de privilégios e controle de permissões por módulo (RBAC)</p>
    </div>

    <button type="button" class="btn btn-primary" data-action="open-role-drawer" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; font-size: 13px; font-weight: 700; cursor: pointer;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 4v16m8-8H4"/>
        </svg>
        <span>Novo Perfil</span>
    </button>
</div>

<!-- MÉTRICAS DIRETAS NO TOPO (FLAT STATS - CORES PADRÃO) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; padding: 4px 2px;">
    <div style="border-left: 3px solid #0284c7; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total de Perfis</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-role-total"><?= $totalRoles ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Grupos cadastrados</div>
    </div>

    <div style="border-left: 3px solid #10b981; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">Perfis Ativos</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-role-active"><?= $countActive ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Regras em vigor</div>
    </div>

    <div style="border-left: 3px solid #f59e0b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 0.05em;">Perfis Inativos</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-role-inactive"><?= $countInactive ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Regras suspensas</div>
    </div>

    <div style="border-left: 3px solid #64748b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Usuários Alocados</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-role-users"><?= $totalAssignedUsers ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Contas vinculadas</div>
    </div>
</div>

<!-- PAINEL ESTRUTURADO DE FILTROS DE PERFIS -->
<div class="panel" style="margin-bottom: 20px;">
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Filtros de Pesquisa</span>

        <button type="button" id="btn-reset-role-filters" style="background: none; border: none; font-size: 11px; font-weight: 600; color: #64748b; cursor: pointer; text-decoration: underline; visibility: hidden; opacity: 0; transition: opacity 0.15s ease;" data-action="reset-role-filters">
            Limpar filtros
        </button>
    </div>

    <div style="padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; background-color: var(--bg-surface);">
        <!-- Linha 1: Busca Textual Principal -->
        <div>
            <label for="filter-role-search" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Busca Textual</label>
            <div class="search-wrapper" style="max-width: 100%;">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="filter-role-search" class="search-input" placeholder="Buscar por Nome do Perfil, Descrição ou Permissões..." autocomplete="off" style="height: 36px; font-size: 13px;">
            </div>
        </div>

        <!-- Linha 2: Grade de Filtros de Perfis -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
            <!-- 1. Status do Perfil -->
            <div>
                <label for="filter-role-status" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Status do Perfil</label>
                <select id="filter-role-status" class="filter-select">
                    <option value="">Todos os Status</option>
                    <option value="1">Ativos (Vigentes)</option>
                    <option value="0">Inativos (Suspensos)</option>
                </select>
            </div>

            <!-- 2. Alocação de Usuários -->
            <div>
                <label for="filter-role-allocation" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Alocação de Contas</label>
                <select id="filter-role-allocation" class="filter-select">
                    <option value="">Todos os Perfis</option>
                    <option value="com_usuarios">Com Usuários Alocados</option>
                    <option value="sem_usuarios">Sem Usuários Vinculados</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- PAINEL DA TABELA DE PERFIS COM ALTURA FIXA PADRONIZADA -->
<div class="panel fixed-table-panel">
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px; height: 38px; box-sizing: border-box; display: flex; align-items: center; justify-content: space-between;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Perfis de Acesso Cadastrados</span>

        <span id="role-result-count" style="font-size: 11px; color: var(--text-muted); font-weight: 500;">
            Mostrando <?= min(8, count($roles)) ?> de <?= $totalRoles ?> perfis
        </span>
    </div>

    <div class="fixed-table-container">
        <table class="data-table roles-fixed-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 170px;">Nome do Perfil</th>
                    <th style="width: 28%;">Descrição Operacional</th>
                    <th>Permissões</th>
                    <th style="width: 80px; text-align: center;">Usuários</th>
                    <th style="width: 70px; text-align: center;">Status</th>
                    <th style="text-align: center; width: 60px;">Ações</th>
                </tr>
            </thead>
            <tbody id="roles-table-body">
                <?php if (empty($roles)): ?>
                    <tr id="empty-row" style="height: 452px;">
                        <td colspan="7" style="text-align: center; vertical-align: middle; color: var(--text-muted); font-size: 13px;">
                            Nenhum perfil de acesso cadastrado no sistema.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($roles as $r): ?>
                        <?php $rawJson = htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8'); ?>
                        <tr class="role-row"
                            data-id="<?= $r['id'] ?>"
                            data-name="<?= htmlspecialchars(strtolower($r['name'])) ?>"
                            data-desc="<?= htmlspecialchars(strtolower($r['description'] ?? '')) ?>"
                            data-perm="<?= htmlspecialchars(strtolower($r['permissions_summary'] ?? '')) ?>"
                            data-status="<?= !empty($r['is_active']) ? '1' : '0' ?>"
                            data-has-users="<?= !empty($r['users_count']) ? '1' : '0' ?>"
                        >
                            <td class="font-mono" style="font-size: 11px; color: var(--text-dim);">#<?= $r['id'] ?></td>
                            <td style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <div style="font-weight: 700; color: var(--text-main); font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($r['name']) ?>">
                                    <?= htmlspecialchars($r['name']) ?>
                                </div>
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                                <?php $desc = trim((string)($r['description'] ?? '')); ?>
                                <?php if ($desc === ''): ?>
                                    <span style="color: var(--text-dim);">-</span>
                                <?php else: ?>
                                    <div class="perm-hover-wrapper" style="width: 100%; max-width: 100%;">
                                        <span class="perm-text-summary" style="width: 100%; color: var(--text-muted); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?= htmlspecialchars($desc) ?>
                                        </span>
                                        <div class="perm-popover-card">
                                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 6px; margin-bottom: 8px;">
                                                <span style="font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8;">
                                                    Descrição do Perfil
                                                </span>
                                                <button type="button" data-action="copy-text" data-text="<?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?>" data-toast="Descrição copiada!" style="background: #1e293b; border: 1px solid #475569; color: #cbd5e1; font-size: 10px; padding: 2px 8px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                                    Copiar
                                                </button>
                                            </div>
                                            <div style="color: #f1f5f9; font-size: 11.5px; line-height: 1.6; user-select: text; cursor: text; max-height: 180px; overflow-y: auto;">
                                                <?= htmlspecialchars($desc) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px; color: var(--text-main); white-space: nowrap;">
                                <?php 
                                $rawSummary = trim((string)($r['permissions_summary'] ?? ''));
                                if ($rawSummary === '' || $rawSummary === 'Nenhuma permissão configurada') {
                                    echo '<span style="color: var(--text-dim); font-size: 11px;">Sem permissões</span>';
                                } else {
                                    $permItems = array_values(array_filter(array_map('trim', explode(',', $rawSummary))));
                                    $totalP = count($permItems);
                                    $maxShow = 3;
                                    $shown = array_slice($permItems, 0, $maxShow);
                                    $rest = $totalP - count($shown);
                                    $shownText = implode(', ', $shown);
                                    ?>
                                    <div class="perm-hover-wrapper">
                                        <div class="perm-text-summary">
                                            <span><?= htmlspecialchars($shownText) ?></span><?php if ($rest > 0): ?><span style="color: var(--text-muted); font-size: 12px; margin-left: 4px;">e +<?= $rest ?></span><?php endif; ?>
                                        </div>

                                        <div class="perm-popover-card">
                                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 6px; margin-bottom: 8px;">
                                                <span style="font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8;">
                                                    Permissões (<?= $totalP ?>)
                                                </span>
                                                <button type="button" data-action="copy-text" data-text="<?= htmlspecialchars($rawSummary, ENT_QUOTES, 'UTF-8') ?>" data-toast="Permissões copiadas!" style="background: #1e293b; border: 1px solid #475569; color: #cbd5e1; font-size: 10px; padding: 2px 8px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                                    Copiar
                                                </button>
                                            </div>
                                            <div style="color: #f1f5f9; font-size: 11.5px; line-height: 1.6; user-select: text; cursor: text; max-height: 180px; overflow-y: auto;">
                                                <?= htmlspecialchars($rawSummary) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </td>
                            <td style="text-align: center; font-family: var(--font-mono); font-weight: 700; color: var(--text-main); font-size: 13px;">
                                <?= (int)$r['users_count'] ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($r['is_active'])): ?>
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981; display: inline-block;" title="Ativo"></span>
                                <?php else: ?>
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #94a3b8; display: inline-block;" title="Inativo"></span>
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
                                        <button type="button" class="action-menu-item" data-action="open-role-drawer" data-payload="<?= $rawJson ?>">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                            <span>Editar Perfil</span>
                                        </button>
                                        <div class="action-menu-divider"></div>
                                        <button type="button" class="action-menu-item danger" data-action="delete-role" data-id="<?= $r['id'] ?>" data-name="<?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?>">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                            <span>Excluir Perfil</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Rodapé de Paginação Padrão ERP com SVGs -->
    <div style="padding: 10px 16px; border-top: 1px solid var(--border-color); background-color: var(--bg-subtle); display: flex; justify-content: space-between; align-items: center; height: 44px; box-sizing: border-box;">
        <span style="font-size: 11px; color: var(--text-muted); font-weight: 500;">
            Página <strong id="role-current-page-text" style="color: var(--text-main);">1</strong> de <strong id="role-total-pages-text" style="color: var(--text-main);">1</strong>
        </span>

        <div style="display: flex; align-items: center; gap: 6px;" id="role-pagination-controls">
            <button type="button" class="pagination-btn" id="role-btn-prev" disabled data-action="change-role-page" data-delta="-1" title="Página Anterior">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div id="role-page-numbers" style="display: flex; gap: 4px;">
                <button type="button" class="pagination-btn active">1</button>
            </div>
            <button type="button" class="pagination-btn" id="role-btn-next" disabled data-action="change-role-page" data-delta="1" title="Próxima Página">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>
</div>

<!-- Gaveta Lateral de Cadastro/Edição de Perfil e Permissões Granulares -->
<?php require __DIR__ . '/drawer.php'; ?>

<script src="<?= \Alfasic\Core\View::asset('/js/pages/roles-index.js') ?>" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES, 'UTF-8') ?>"></script>
