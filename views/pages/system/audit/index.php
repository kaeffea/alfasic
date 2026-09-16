<?php
/**
 * View: Gestão de Auditoria
 * Padrão Corporativo Oficial da Alfagás • ZERO Emojis • Tipografia Uniforme • Paginação Interativa • Máscara de Data
 */
$title = 'Gestão de Auditoria - Alfagás';

$fieldLabels = \Alfasic\Models\AuditLog::$fieldLabels ?? [
    'name' => 'Razão Social / Nome',
    'trade_name' => 'Nome Fantasia',
    'document' => 'CPF / CNPJ',
    'phone' => 'Telefone',
    'email' => 'E-mail',
    'payment_terms' => 'Condições de Pagamento',
    'credit_limit' => 'Limite de Crédito',
    'city' => 'Cidade',
    'state' => 'Estado / UF',
    'is_active' => 'Status Ativo/Inativo',
    'deleted_at' => 'Data de Exclusão',
    'standard_price' => 'Preço Padrão',
    'rental_fee' => 'Taxa de Locação',
    'role_title' => 'Cargo / Função',
    'driver_license' => 'CNH',
    'driver_license_category' => 'Categoria CNH',
    'has_mopp' => 'Possui MOPP',
    'branch' => 'Filial / Unidade',
    'username' => 'Usuário de Acesso',
    'role_id' => 'Perfil de Acesso'
];

$eventsList = $auditEvents ?? [];
$totalCount = $stats['total'] ?? count($eventsList);
$countToday = $stats['today'] ?? 0;
$countCritical = $stats['critical'] ?? 0;
$countRollbacks = $stats['rollbacks'] ?? 0;

$currentEntity = $filters['entity'] ?? '';
$currentAction = $filters['action'] ?? '';
$currentUser = $filters['user_id'] ?? '';
$currentDate = $filters['date'] ?? '';
$currentSearch = $filters['search'] ?? '';
?>

<style>
/* Select Estilizado Padrão do Sistema */
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

.filter-select:hover, .filter-input:hover {
    border-color: #94a3b8;
}

.filter-select:focus, .filter-input:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
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

.filter-input.is-invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
}

/* Paginação Estilizada Oficial ERP */
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

/* Estrutura da Linha do Tempo Corporativa */
.timeline-container {
    position: relative;
    padding-left: 20px;
    margin-bottom: 24px;
    min-height: 450px;
}

.timeline-line {
    position: absolute;
    left: 4px;
    top: 17px;
    width: 2px;
    background-color: #e2e8f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 12px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-dot {
    position: absolute;
    left: -20px;
    top: 17px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    box-shadow: 0 0 0 3px #f8fafc;
}

.timeline-card {
    background-color: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 12px 16px;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.timeline-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}
</style>

<!-- 1. CABEÇALHO PADRÃO DA PÁGINA -->
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
    <div>
        <h1 class="page-title">Gestão de Auditoria</h1>
        <p class="page-subtitle">Registro cronológico de operações, modificações, inativações e governança</p>
    </div>
</div>

<!-- 2. MÉTRICAS DIRETAS (FLAT STATS) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; padding: 4px 2px;">
    <div style="border-left: 3px solid #0284c7; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total de Registros</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-total-records"><?= $totalCount ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Ações auditadas no sistema</div>
    </div>

    <div style="border-left: 3px solid #10b981; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">Hoje</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countToday ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Novas operações registradas</div>
    </div>

    <div style="border-left: 3px solid #f59e0b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 0.05em;">Inativações & Exclusões</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countCritical ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Com justificativa informada</div>
    </div>

    <div style="border-left: 3px solid #64748b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Reversões Efetuadas</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countRollbacks ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Rollbacks no histórico</div>
    </div>
</div>

<!-- 3. PAINEL ESTRUTURADO DE FILTROS -->
<div class="panel" style="margin-bottom: 20px;">
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px; display: flex; justify-content: space-between; align-items: center;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Filtros de Pesquisa</span>
        <button type="button" id="btn-reset-filters" style="background: none; border: none; font-size: 11px; font-weight: 600; color: #64748b; cursor: pointer; text-decoration: underline; visibility: hidden; opacity: 0; transition: opacity 0.15s ease;" data-action="reset-audit-filters">
            Limpar filtros
        </button>
    </div>

    <div style="padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; background-color: var(--bg-surface);">
        <!-- Linha 1: Campo de Busca Principal -->
        <div>
            <label for="filter-audit-search" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Busca Textual</label>
            <div class="search-wrapper" style="max-width: 100%;">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="filter-audit-search" class="search-input" value="<?= htmlspecialchars($currentSearch) ?>" placeholder="Buscar por motivo, cliente, produto, usuário, IP ou campo alterado..." autocomplete="off" data-action-input="audit-filter-change" style="height: 36px; font-size: 13px;">
            </div>
        </div>

        <!-- Linha 2: Grade de Filtros Específicos -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px;">
            <div>
                <label for="filter-audit-entity" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Módulo</label>
                <select id="filter-audit-entity" class="filter-select" data-action-change="audit-filter-change">
                    <option value="">Todos os Módulos</option>
                    <option value="clients" <?= $currentEntity === 'clients' ? 'selected' : '' ?>>Clientes</option>
                    <option value="suppliers" <?= $currentEntity === 'suppliers' ? 'selected' : '' ?>>Fornecedores</option>
                    <option value="employees" <?= $currentEntity === 'employees' ? 'selected' : '' ?>>Funcionários</option>
                    <option value="products" <?= $currentEntity === 'products' ? 'selected' : '' ?>>Produtos</option>
                    <option value="users" <?= $currentEntity === 'users' ? 'selected' : '' ?>>Usuários</option>
                    <option value="roles" <?= $currentEntity === 'roles' ? 'selected' : '' ?>>Perfis</option>
                </select>
            </div>

            <div>
                <label for="filter-audit-action" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Operação</label>
                <select id="filter-audit-action" class="filter-select" data-action-change="audit-filter-change">
                    <option value="">Todas as Operações</option>
                    <option value="create" <?= $currentAction === 'create' ? 'selected' : '' ?>>Cadastro</option>
                    <option value="update" <?= $currentAction === 'update' ? 'selected' : '' ?>>Edição</option>
                    <option value="activate" <?= $currentAction === 'activate' ? 'selected' : '' ?>>Ativação</option>
                    <option value="deactivate" <?= $currentAction === 'deactivate' ? 'selected' : '' ?>>Inativação</option>
                    <option value="soft_delete" <?= $currentAction === 'soft_delete' ? 'selected' : '' ?>>Exclusão Parcial</option>
                    <option value="force_delete" <?= $currentAction === 'force_delete' ? 'selected' : '' ?>>Exclusão Permanente</option>
                    <option value="rollback" <?= $currentAction === 'rollback' ? 'selected' : '' ?>>Reversão</option>
                </select>
            </div>

            <div>
                <label for="filter-audit-user" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Autor da Ação</label>
                <select id="filter-audit-user" class="filter-select" data-action-change="audit-filter-change">
                    <option value="">Todos os Usuários</option>
                    <?php 
                    $usersList = \Alfasic\Models\User::all() ?? [];
                    foreach ($usersList as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= (string)$currentUser === (string)$u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['username'] ?? $u['email'] ?? 'Usuário #' . $u['id']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="filter-audit-date" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Data</label>
                <div style="position: relative;">
                    <input type="text" id="filter-audit-date" class="filter-input" value="<?= htmlspecialchars($currentDate) ?>" placeholder="DD/MM/AAAA" maxlength="10" autocomplete="off" data-action-input="audit-date-input" data-action-keydown="audit-digits-only">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. BARRA DE LEGENDA HARMONIOSA -->
<div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap; padding: 8px 14px; background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); margin-bottom: 16px; font-size: 11px; color: var(--text-muted);">
    <span style="font-weight: 700; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.04em;">Legenda:</span>
    <span style="display: inline-flex; align-items: center; gap: 6px;">
        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981; display: inline-block;"></span> Cadastro
    </span>
    <span style="display: inline-flex; align-items: center; gap: 6px;">
        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #0284c7; display: inline-block;"></span> Edição
    </span>
    <span style="display: inline-flex; align-items: center; gap: 6px;">
        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #0d9488; display: inline-block;"></span> Ativação
    </span>
    <span style="display: inline-flex; align-items: center; gap: 6px;">
        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #f59e0b; display: inline-block;"></span> Inativação
    </span>
    <span style="display: inline-flex; align-items: center; gap: 6px;">
        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #ef4444; display: inline-block;"></span> Exclusão Parcial
    </span>
    <span style="display: inline-flex; align-items: center; gap: 6px;">
        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #475569; display: inline-block;"></span> Exclusão Permanente
    </span>
    <span style="display: inline-flex; align-items: center; gap: 6px;">
        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #6366f1; display: inline-block;"></span> Reversão
    </span>
</div>

<!-- 5. LINHA DO TEMPO CORPORATIVA LIMPA (SEM TABELAS) -->
<div class="timeline-container">
    <div class="timeline-line"></div>

    <div id="timeline-items-list" style="display: flex; flex-direction: column; gap: 12px;">
        <?php if (empty($eventsList)): ?>
            <div style="padding: 60px 20px; text-align: center; color: var(--text-muted); font-size: 13px;">
                Nenhum evento registrado na trilha de auditoria para os filtros selecionados.
            </div>
        <?php else: ?>
            <?php foreach ($eventsList as $event): ?>
                <div class="timeline-item audit-item"
                    data-entity="<?= htmlspecialchars($event['entity']) ?>"
                    data-action="<?= htmlspecialchars($event['action']) ?>"
                    data-user="<?= htmlspecialchars((string)($event['user_id'] ?? '')) ?>"
                    data-date="<?= htmlspecialchars($event['created_at_date'] ?? '') ?>"
                    data-search="<?= htmlspecialchars(strtolower($event['entity_label'] . ' ' . ($event['reason'] ?? '') . ' ' . $event['user_name'] . ' ' . $event['context_module'] . ' ' . ($event['diff_summary'] ?? ''))) ?>">
                    
                    <!-- Bolinha Sólida de 10px com Cor Única por Operação -->
                    <div class="timeline-dot" style="background-color: <?= $event['dot_color'] ?>;" title="<?= htmlspecialchars($event['action_label']) ?>"></div>

                    <!-- Card Limpo, Harmonioso e Padronizado -->
                    <div class="timeline-card">
                        
                        <!-- Linha Superior: Registro, Módulo, Tempo e Menu 3 Pontinhos -->
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 6px;">
                            
                            <!-- Lado Esquerdo: Módulo • Nome do Registro (ID) • #Log -->
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; font-size: 13px;">
                                <span style="color: var(--text-muted); font-weight: 500;"><?= htmlspecialchars($event['context_module']) ?></span>
                                <span style="color: var(--text-dim);">&bull;</span>
                                <strong style="color: var(--text-main); font-weight: 700;"><?= htmlspecialchars($event['entity_label']) ?></strong>
                                <span style="color: var(--text-muted); font-size: 12px; font-weight: 400;">(ID #<?= $event['entity_id'] ?>)</span>
                                <span style="color: var(--text-dim);">&bull;</span>
                                <span style="font-family: var(--font-mono); font-size: 11px; color: var(--text-dim);">#<?= $event['id'] ?></span>
                            </div>

                            <!-- Lado Direito: Tempo Relativo, Data Exata e Menu de Opções -->
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <div style="text-align: right; display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 12px; font-weight: 600; color: var(--text-main);">
                                        <?= htmlspecialchars($event['created_at_relative']) ?>
                                    </span>
                                    <span style="color: var(--text-dim);">&bull;</span>
                                    <span style="font-size: 11px; font-family: var(--font-mono); color: var(--text-muted);">
                                        <?= htmlspecialchars($event['created_at_exact']) ?>
                                    </span>
                                </div>

                                <!-- Menu de 3 Pontinhos Oficial do Sistema -->
                                <div class="row-actions-dropdown">
                                    <button type="button" class="btn-row-action" title="Opções" aria-label="Opções">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                        </svg>
                                    </button>
                                    <div class="row-action-menu">
                                        <button type="button" class="action-menu-item" data-action="open-audit-drawer" data-payload="<?= htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8') ?>">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            <span>Ver Detalhes</span>
                                        </button>

                                        <?php if (!empty($event['can_rollback'])): ?>
                                            <button type="button" class="action-menu-item" data-action="audit-rollback" data-payload="<?= htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8') ?>">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                                                </svg>
                                                <span>Reverter Ação</span>
                                            </button>
                                        <?php endif; ?>

                                        <?php if (!empty($event['is_soft_deleted'])): ?>
                                            <button type="button" class="action-menu-item danger" data-action="audit-hard-delete" data-payload="<?= htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8') ?>">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                </svg>
                                                <span>Excluir Permanentemente</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Corpo do Card: Motivo ou Resumo de Campos em Linha Única (Sem Quebra) -->
                        <?php if (!empty($event['reason'])): ?>
                            <div style="font-size: 12px; color: #334155; line-height: 1.4; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($event['reason']) ?>">
                                <strong style="color: var(--text-main);">Motivo:</strong> <?= htmlspecialchars($event['reason']) ?>
                            </div>
                        <?php elseif (!empty($event['diff_summary'])): ?>
                            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 6px; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($event['diff_summary']) ?>">
                                <strong style="color: var(--text-main);"><?= $event['action'] === 'create' ? 'Campos cadastrados:' : ($event['action'] === 'rollback' ? 'Campos restaurados:' : 'Campos alterados:') ?></strong> 
                                <?= htmlspecialchars($event['diff_summary']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Rodapé do Card: Autor e IP em Tipografia Discreta -->
                        <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f1f5f9; padding-top: 6px; font-size: 11px; color: var(--text-muted);">
                            <div>
                                <span style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($event['user_name']) ?></span> 
                                <span>&bull; <?= htmlspecialchars($event['user_role']) ?></span>
                            </div>
                            <div style="font-family: var(--font-mono); color: var(--text-dim);">
                                IP: <?= htmlspecialchars($event['ip_address']) ?>
                            </div>
                        </div>

                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- 6. PAGINAÇÃO OFICIAL ERP INTERATIVA -->
<div style="padding: 10px 16px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background-color: var(--bg-subtle); display: flex; justify-content: space-between; align-items: center; height: 44px; box-sizing: border-box; margin-bottom: 24px;">
    <span style="font-size: 11px; color: var(--text-muted); font-weight: 500;">
        Página <strong id="audit-current-page-text" style="color: var(--text-main);">1</strong> de <strong id="audit-total-pages-text" style="color: var(--text-main);">1</strong>
        &bull; Exibindo <strong id="audit-showing-count" style="color: var(--text-main);"><?= count($eventsList) ?></strong> de <strong id="audit-total-records" style="color: var(--text-main);"><?= count($eventsList) ?></strong> registros
    </span>

    <div style="display: flex; align-items: center; gap: 6px;" id="audit-pagination-controls">
        <button type="button" class="pagination-btn" id="audit-btn-prev" disabled data-action="change-audit-page" data-delta="-1" title="Página Anterior">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <div id="audit-page-numbers" style="display: flex; gap: 4px;">
            <button type="button" class="pagination-btn active">1</button>
        </div>
        <button type="button" class="pagination-btn" id="audit-btn-next" disabled data-action="change-audit-page" data-delta="1" title="Próxima Página">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>
</div>

<!-- Slide-Over Drawer de Diff & Inspeção Profunda -->
<?php require __DIR__ . '/drawer.php'; ?>

<script type="application/json" id="audit-field-labels">
<?= json_encode(\Alfasic\Models\AuditLog::$fieldLabels, JSON_UNESCAPED_UNICODE) ?>
</script>
<script src="<?= \Alfasic\Core\View::asset('/js/pages/audit-index.js') ?>" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES, 'UTF-8') ?>"></script>
