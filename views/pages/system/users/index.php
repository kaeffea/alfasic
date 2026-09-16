<?php
/**
 * View: Gestão de Usuários do Sistema
 * Padrão Corporativo Oficial da Alfagás • ZERO Emojis • Vínculo com Funcionários & RBAC
 */
$users = $users ?? [];

$totalCount = count($users);
$countActive = 0;
$countBlocked = 0;
$rolesList = [];

foreach ($users as $u) {
    if (!empty($u['is_active'])) $countActive++;
    else $countBlocked++;
    if (!empty($u['role_name'])) {
        $rolesList[$u['role_name']] = ($rolesList[$u['role_name']] ?? 0) + 1;
    }
}
$countRoles = count($rolesList);
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
}

.fixed-table-container {
    height: 452px;
    min-height: 452px;
    overflow: hidden;
    background-color: #ffffff;
}

.users-fixed-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.users-fixed-table th {
    height: 36px;
    padding: 6px 12px;
    box-sizing: border-box;
    background-color: var(--bg-subtle);
    border-bottom: 1px solid var(--border-color);
}

.users-fixed-table td {
    height: 52px;
    padding: 4px 12px;
    box-sizing: border-box;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.users-fixed-table tbody tr:last-child td {
    border-bottom: none;
}

.user-avatar-badge {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e0f2fe;
    color: #0369a1;
    font-weight: 700;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
</style>

<!-- Cabeçalho da Página -->
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
    <div>
        <h1 class="page-title">Gestão de Usuários</h1>
        <p class="page-subtitle">Controle de contas de acesso ao sistema vinculadas a colaboradores e perfis de segurança</p>
    </div>

    <button type="button" class="btn btn-primary" data-action="open-user-drawer" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; font-size: 13px; font-weight: 700; cursor: pointer;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 4v16m8-8H4"/>
        </svg>
        <span>Novo Usuário</span>
    </button>
</div>

<!-- MÉTRICAS DIRETAS NO TOPO (FLAT STATS) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; padding: 4px 2px;">
    <div style="border-left: 3px solid #0284c7; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total de Usuários</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-total"><?= $totalCount ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Contas cadastradas</div>
    </div>

    <div style="border-left: 3px solid #10b981; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">Usuários Ativos</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-active"><?= $countActive ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Com acesso liberado</div>
    </div>

    <div style="border-left: 3px solid #f59e0b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 0.05em;">Perfis de Acesso</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-roles"><?= $countRoles ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Grupos de privilégios</div>
    </div>

    <div style="border-left: 3px solid #64748b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Inativos / Bloqueados</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-blocked"><?= $countBlocked ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Acesso suspenso</div>
    </div>
</div>

<!-- PAINEL ESTRUTURADO DE FILTROS -->
<div class="panel" style="margin-bottom: 20px;">
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Filtros de Pesquisa</span>

        <button type="button" id="btn-reset-user-filters" style="background: none; border: none; font-size: 11px; font-weight: 600; color: #64748b; cursor: pointer; text-decoration: underline; visibility: hidden; opacity: 0; transition: opacity 0.15s ease;" data-action="reset-user-filters">
            Limpar filtros
        </button>
    </div>

    <div style="padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; background-color: var(--bg-surface);">
        <!-- Linha 1: Busca Textual Principal -->
        <div>
            <label for="filter-user-search" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Busca Textual</label>
            <div class="search-wrapper" style="max-width: 100%;">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="filter-user-search" class="search-input" placeholder="Buscar por Nome de Usuário, E-mail corporativo ou Colaborador vinculado..." autocomplete="off" style="height: 36px; font-size: 13px;">
            </div>
        </div>

        <!-- Linha 2: Grade de Filtros de Usuários -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
            <!-- 1. Perfil de Acesso -->
            <div>
                <label for="filter-user-role" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Perfil de Acesso</label>
                <select id="filter-user-role" class="filter-select">
                    <option value="">Todos os Perfis</option>
                    <option value="Administrador Geral">Administrador Geral</option>
                    <option value="Vendas & Comercial">Vendas & Comercial</option>
                    <option value="Operacional & Usina">Operacional & Usina</option>
                    <option value="Logística & Entregas">Logística & Entregas</option>
                </select>
            </div>

            <!-- 2. Status da Conta -->
            <div>
                <label for="filter-user-status" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Status da Conta</label>
                <select id="filter-user-status" class="filter-select">
                    <option value="">Todos os Status</option>
                    <option value="1">Ativos (Liberados)</option>
                    <option value="0">Inativos / Bloqueados</option>
                </select>
            </div>

            <!-- 3. Vínculo com Funcionário -->
            <div>
                <label for="filter-user-employee" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Vínculo Profissional</label>
                <select id="filter-user-employee" class="filter-select">
                    <option value="">Todos os Usuários</option>
                    <option value="com_vinculo">Com Funcionário Vinculado</option>
                    <option value="sem_vinculo">Sem Vínculo Cadastrado</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- PAINEL DA TABELA DE USUÁRIOS COM ALTURA FIXA PADRONIZADA -->
<div class="panel fixed-table-panel">
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px; height: 38px; box-sizing: border-box; display: flex; align-items: center; justify-content: space-between;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Usuários do Sistema</span>

        <span id="user-result-count" style="font-size: 11px; color: var(--text-muted); font-weight: 500;">
            Mostrando <?= min(8, count($users)) ?> de <?= number_format($totalCount, 0, ',', '.') ?> contas
        </span>
    </div>

    <div class="fixed-table-container">
        <table class="data-table users-fixed-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Conta de Usuário / E-mail</th>
                    <th>Colaborador Vinculado</th>
                    <th style="width: 170px;">Perfil de Acesso</th>
                    <th style="width: 150px;">Último Acesso</th>
                    <th style="width: 70px; text-align: center;">Status</th>
                    <th style="text-align: center; width: 60px;">Ações</th>
                </tr>
            </thead>
            <tbody id="users-table-body">
                <?php if (empty($users)): ?>
                    <tr id="empty-row" style="height: 452px;">
                        <td colspan="7" style="text-align: center; vertical-align: middle; color: var(--text-muted); font-size: 13px;">
                            Nenhum usuário cadastrado no sistema.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <?php $rawJson = htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>
                    <tr class="user-row" 
                        data-id="<?= $user['id'] ?>"
                        data-username="<?= htmlspecialchars(strtolower($user['username'])) ?>"
                        data-email="<?= htmlspecialchars(strtolower($user['email'])) ?>"
                        data-employee="<?= htmlspecialchars(strtolower($user['employee_name'] ?? '')) ?>"
                        data-role="<?= htmlspecialchars($user['role_name'] ?? '') ?>"
                        data-status="<?= $user['is_active'] ? '1' : '0' ?>"
                        data-has-employee="<?= !empty($user['employee_id']) ? '1' : '0' ?>"
                    >
                        <td class="font-mono" style="font-size: 11px; color: var(--text-dim);">#<?= $user['id'] ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <?php
                                $initials = strtoupper(substr($user['username'], 0, 2));
                                ?>
                                <div class="user-avatar-badge"><?= $initials ?></div>
                                <div>
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 13px;"><?= htmlspecialchars($user['username']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($user['employee_name'])): ?>
                                <div style="font-weight: 600; color: var(--text-main); font-size: 12px;"><?= htmlspecialchars($user['employee_name']) ?></div>
                                <div style="font-size: 11px; color: var(--text-dim);"><?= htmlspecialchars($user['employee_role'] ?? 'Colaborador') ?></div>
                            <?php else: ?>
                                <span style="font-size: 11px; color: var(--text-dim);">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 12px; color: var(--text-main);">
                            <?= htmlspecialchars($user['role_name'] ?? 'Sem Perfil') ?>
                        </td>
                        <td>
                            <?php if (!empty($user['last_login_at'])): ?>
                                <span class="font-mono" style="font-size: 11px; color: var(--text-main);"><?= date('d/m/Y H:i', strtotime($user['last_login_at'])) ?></span>
                            <?php else: ?>
                                <span style="font-size: 11px; color: var(--text-dim);">Nunca acessou</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($user['is_active']): ?>
                                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981; display: inline-block;" title="Ativo"></span>
                            <?php else: ?>
                                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #94a3b8; display: inline-block;" title="Inativo"></span>
                            <?php endif; ?>
                            <?php $devCount = (int) ($user['trusted_devices'] ?? 0); ?>
                            <?php if ($devCount > 0): ?>
                                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #0284c7; display: inline-block; margin-left: 3px;" title="<?= $devCount ?> dispositivo(s) confiável(is)"></span>
                            <?php else: ?>
                                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: transparent; border: 1px solid #cbd5e1; display: inline-block; margin-left: 3px;" title="Nenhum dispositivo confiável"></span>
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
                                    <button type="button" class="action-menu-item" data-action="open-user-drawer" data-payload="<?= $rawJson ?>">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        <span>Editar Usuário</span>
                                    </button>
                                    <?php if ((int) ($user['trusted_devices'] ?? 0) > 0): ?>
                                        <form method="POST" action="/users/reset-2fa" style="display: block; width: 100%;" data-confirm="Revogar os dispositivos confiáveis deste usuário? Ele precisará do código por e-mail no próximo login.">
                                            <?= \Alfasic\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="action-menu-item">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Revogar dispositivos</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <div class="action-menu-divider"></div>
                                    <button type="button" class="action-menu-item danger" data-action="delete-user" data-id="<?= $user['id'] ?>" data-name="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                        <span>Excluir Usuário</span>
                                    </button>
                                </div>
                            </div>
                        </td>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Rodapé de Paginação Padrão ERP com SVGs -->
    <div style="padding: 10px 16px; border-top: 1px solid var(--border-color); background-color: var(--bg-subtle); display: flex; justify-content: space-between; align-items: center; height: 44px; box-sizing: border-box;">
        <span style="font-size: 11px; color: var(--text-muted); font-weight: 500;">
            Página <strong id="user-current-page-text" style="color: var(--text-main);">1</strong> de <strong id="user-total-pages-text" style="color: var(--text-main);">1</strong>
        </span>

        <div style="display: flex; align-items: center; gap: 6px;" id="user-pagination-controls">
            <button type="button" class="pagination-btn" id="user-btn-prev" disabled data-action="change-user-page" data-delta="-1" title="Página Anterior">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div id="user-page-numbers" style="display: flex; gap: 4px;">
                <button type="button" class="pagination-btn active">1</button>
            </div>
            <button type="button" class="pagination-btn" id="user-btn-next" disabled data-action="change-user-page" data-delta="1" title="Próxima Página">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>
</div>

<!-- Gaveta Lateral (Drawer) de Cadastro / Edição de Usuário -->
<?php require __DIR__ . '/drawer.php'; ?>

<script src="<?= \Alfasic\Core\View::asset('/js/pages/users-index.js') ?>" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES, 'UTF-8') ?>"></script>
