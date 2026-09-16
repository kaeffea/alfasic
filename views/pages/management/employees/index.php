<?php
/**
 * View: Gestão de Funcionários & Colaboradores
 * Padrão Corporativo Oficial da Alfagás • ZERO Emojis • Filtros ERP & Tabela de Altura Fixa
 */
$employees = $employees ?? [];
$totalCount = $totalCount ?? count($employees);

// Contadores para as Métricas no Fundo da Página (Flat Stats)
$countDrivers = 0;
$countPlant = 0;
$countAdmin = 0;

foreach ($employees as $e) {
    $role = strtolower($e['role_title'] ?? '');
    $hasCnh = !empty($e['driver_license']);

    if ($hasCnh || str_contains($role, 'motorista') || str_contains($role, 'entrega')) {
        $countDrivers++;
    } elseif (str_contains($role, 'usina') || str_contains($role, 'operador') || str_contains($role, 'enchimento')) {
        $countPlant++;
    } else {
        $countAdmin++;
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

.employees-fixed-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.employees-fixed-table th {
    height: 36px;
    padding: 6px 12px;
    box-sizing: border-box;
    background-color: var(--bg-subtle);
    border-bottom: 1px solid var(--border-color);
}

.employees-fixed-table td {
    height: 52px;
    padding: 4px 12px;
    box-sizing: border-box;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.employees-fixed-table tbody tr:last-child td {
    border-bottom: none;
}
</style>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
    <div>
        <h1 class="page-title">Gestão de Funcionários</h1>
        <p class="page-subtitle">Quadro de motoristas de entrega, operadores de usina, equipe comercial e operacional</p>
    </div>

    <button type="button" class="btn btn-primary" data-action="open-employee-drawer" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; font-size: 13px; font-weight: 700; cursor: pointer;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 4v16m8-8H4"/>
        </svg>
        <span>Novo Funcionário</span>
    </button>
</div>

<!-- MÉTRICAS DIRETAS NO FUNDO DA PÁGINA (FLAT STATS) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; padding: 4px 2px;">
    <div style="border-left: 3px solid #0284c7; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total de Colaboradores</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-total"><?= $totalCount ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Equipe registrada</div>
    </div>

    <div style="border-left: 3px solid #10b981; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">Motoristas & Entregas</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countDrivers ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Com CNH e rotas ativas</div>
    </div>

    <div style="border-left: 3px solid #f59e0b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 0.05em;">Operação de Usina</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countPlant ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Enchimento, carga e manutenção</div>
    </div>

    <div style="border-left: 3px solid #64748b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Administrativo & Vendas</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countAdmin ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Faturamento e atendimento</div>
    </div>
</div>

<!-- PAINEL ESTRUTURADO DE FILTROS DE FUNCIONÁRIOS -->
<div class="panel" style="margin-bottom: 20px;">
    <!-- Cabeçalho Limpo e Fixo com Botão de Limpar Integrado -->
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Filtros de Pesquisa</span>

        <button type="button" id="btn-reset-filters" style="background: none; border: none; font-size: 11px; font-weight: 600; color: #64748b; cursor: pointer; text-decoration: underline; visibility: hidden; opacity: 0; transition: opacity 0.15s ease;" data-action="reset-employee-filters">
            Limpar filtros
        </button>
    </div>

    <!-- Corpo do Painel com os Campos de Filtro -->
    <div style="padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; background-color: var(--bg-surface);">
        <!-- Linha 1: Campo de Busca Principal -->
        <div>
            <label for="filter-employee-search" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Busca Textual</label>
            <div class="search-wrapper" style="max-width: 100%;">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="filter-employee-search" class="search-input" placeholder="Buscar por Nome, CPF, Cargo, Telefone ou Cidade..." value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off" style="height: 36px; font-size: 13px;">
            </div>
        </div>

        <!-- Linha 2: Grade de Atributos Específicos de Funcionários -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
            <!-- 1. Departamento / Setor -->
            <div>
                <label for="filter-employee-role" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Setor / Função</label>
                <select id="filter-employee-role" class="filter-select">
                    <option value="">Todos os Setores</option>
                    <option value="motorista">Logística / Motoristas</option>
                    <option value="usina">Operação / Usina</option>
                    <option value="comercial">Comercial / Atendimento</option>
                    <option value="administrativo">Administrativo / Financeiro</option>
                </select>
            </div>

            <!-- 2. Habilitação CNH -->
            <div>
                <label for="filter-employee-cnh" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Categoria CNH</label>
                <select id="filter-employee-cnh" class="filter-select">
                    <option value="">Todas</option>
                    <option value="com_cnh">Com CNH Ativa</option>
                    <option value="d">Cat. D (Caminhões)</option>
                    <option value="e">Cat. E (Carretas/Articulados)</option>
                    <option value="b">Cat. B (Veículos Leves)</option>
                    <option value="sem_cnh">Sem CNH Registrada</option>
                </select>
            </div>

            <!-- 3. Cidade / Polo -->
            <div>
                <label for="filter-employee-city" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Cidade</label>
                <select id="filter-employee-city" class="filter-select">
                    <option value="">Todas as Cidades</option>
                    <option value="são miguel dos campos">São Miguel dos Campos</option>
                    <option value="maceió">Maceió</option>
                    <option value="arapiraca">Arapiraca</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- PAINEL DA TABELA DE FUNCIONÁRIOS COM ALTURA 100% CONSTANTE E IMUTÁVEL -->
<div class="panel fixed-table-panel">
    <!-- Cabeçalho da Lista -->
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px; height: 38px; box-sizing: border-box;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Funcionários Registrados</span>

        <span id="employee-result-count" style="font-size: 11px; color: var(--text-muted); font-weight: 500;">
            Mostrando <?= min(8, count($employees)) ?> de <?= number_format($totalCount, 0, ',', '.') ?> funcionários
        </span>
    </div>

    <!-- Tabela de Funcionários em Área Flexível com Altura Fixa -->
    <div class="fixed-table-container">
        <table class="data-table employees-fixed-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Nome Completo / Cargo</th>
                    <th style="width: 140px;">CPF</th>
                    <th>Telefone / Contato</th>
                    <th style="width: 160px;">Cidade / UF</th>
                    <th style="width: 150px;">CNH / Categoria</th>
                    <th style="text-align: center; width: 50px;">Status</th>
                    <th style="text-align: center; width: 60px;">Ações</th>
                </tr>
            </thead>
            <tbody id="employees-table-body">
                <?php if (empty($employees)): ?>
                    <tr id="empty-row" style="height: 452px;">
                        <td colspan="8" style="text-align: center; vertical-align: middle; color: var(--text-muted);">
                            Nenhum funcionário cadastrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employees as $e): 
                        $cnh = !empty($e['driver_license']) ? '1' : '0';
                        $cat = strtolower($e['driver_license_category'] ?? '');
                    ?>
                        <tr class="employee-row" 
                            data-name="<?= htmlspecialchars(strtolower(($e['name'] ?? '') . ' ' . ($e['role_title'] ?? '') . ' ' . ($e['document'] ?? '') . ' ' . ($e['phone'] ?? '') . ' ' . ($e['city'] ?? ''))) ?>"
                            data-role="<?= htmlspecialchars(strtolower($e['role_title'] ?? 'operacional')) ?>"
                            data-cnh="<?= $cnh ?>"
                            data-cnhcat="<?= $cat ?>"
                            data-city="<?= htmlspecialchars(strtolower($e['city'] ?? '')) ?>">
                            <td style="font-family: var(--font-mono); color: var(--text-dim); font-size: 11px;">#<?= $e['id'] ?></td>
                            <td>
                                <a href="/employees/<?= $e['id'] ?>" style="font-weight: 700; color: var(--text-main); text-decoration: none; font-size: 13px;">
                                    <?= htmlspecialchars($e['name']) ?>
                                </a>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 1px;">
                                    <?= htmlspecialchars($e['role_title'] ?? 'Operacional') ?>
                                </div>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 11px; color: var(--text-main);">
                                <?= htmlspecialchars($e['document'] ?: '-') ?>
                            </td>
                            <td style="font-size: 11px;">
                                <div style="font-family: var(--font-mono); color: var(--text-main); font-weight: 600;"><?= htmlspecialchars($e['phone'] ?: '-') ?></div>
                                <?php if (!empty($e['email'])): ?>
                                    <div style="color: var(--text-muted); font-size: 10px;"><?= htmlspecialchars($e['email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px; color: var(--text-main);">
                                <?= htmlspecialchars($e['city'] ?: 'São Miguel dos Campos') ?>/<?= htmlspecialchars($e['state'] ?: 'AL') ?>
                            </td>
                            <td style="font-size: 11px;">
                                <?php if (!empty($e['driver_license'])): ?>
                                    <span style="font-family: var(--font-mono); font-weight: 700; color: var(--brand-primary);">
                                         Cat. <?= htmlspecialchars($e['driver_license_category'] ?: 'B') ?>
                                    </span>
                                    <span style="color: var(--text-muted); font-size: 10px;">(<?= htmlspecialchars($e['driver_license']) ?>)</span>
                                <?php else: ?>
                                    <span style="color: var(--text-dim);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($e['is_active'])): ?>
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
                                        <a href="/employees/<?= $e['id'] ?>" class="action-menu-item">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                            </svg>
                                            <span>Dados & Cadastro</span>
                                        </a>
                                        <button type="button" class="action-menu-item" data-action="open-employee-drawer" data-payload="<?= htmlspecialchars(json_encode($e), ENT_QUOTES, 'UTF-8') ?>">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                            <span>Editar Cadastro</span>
                                        </button>
                                        <form method="POST" action="/employees/toggle-active" style="display: block; width: 100%;">
                                            <?= \Alfasic\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                            <input type="hidden" name="redirect_to" value="/employees">
                                            <button type="submit" class="action-menu-item">
                                                <?php if (!empty($e['is_active'])): ?>
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                    <span>Desativar Colaborador</span>
                                                <?php else: ?>
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                    <span>Ativar Colaborador</span>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                        <div class="action-menu-divider"></div>
                                        <form method="POST" action="/employees/delete" style="display: block; width: 100%;" data-confirm="Deseja realmente arquivar/excluir este colaborador do sistema?">
                                            <?= \Alfasic\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                            <button type="submit" class="action-menu-item danger">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                </svg>
                                                <span>Excluir Colaborador</span>
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
            Página <strong id="employee-current-page-text" style="color: var(--text-main);">1</strong> de <strong id="employee-total-pages-text" style="color: var(--text-main);">1</strong>
        </span>

        <div style="display: flex; align-items: center; gap: 6px;" id="employee-pagination-controls">
            <button type="button" class="pagination-btn" id="employee-btn-prev" disabled data-action="change-employee-page" data-delta="-1" title="Página Anterior">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div id="employee-page-numbers" style="display: flex; gap: 4px;">
                <button type="button" class="pagination-btn active">1</button>
            </div>
            <button type="button" class="pagination-btn" id="employee-btn-next" disabled data-action="change-employee-page" data-delta="1" title="Próxima Página">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>
</div>

<!-- Slide-Over Drawer (Modal Lateral à Direita) para Novo/Edição de Funcionário -->
<?php require __DIR__ . '/drawer.php'; ?>

<script src="<?= \Alfasic\Core\View::asset('/js/pages/employees-index.js') ?>" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES, 'UTF-8') ?>"></script>
