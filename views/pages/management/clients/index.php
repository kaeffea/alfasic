<?php
/**
 * View: Gestão de Clientes
 * Padrão Corporativo Oficial da Alfagás • ZERO Emojis • Filtros ERP & Tabela de Altura Fixa
 */
$clients = $clients ?? [];
$totalCount = $totalCount ?? count($clients);

// Contadores para as Métricas no Fundo da Página (Flat Stats)
$countPJ = 0;
$countPF = 0;
$countRecentOrders = 0;

foreach ($clients as $c) {
    $doc = preg_replace('/\D/', '', $c['document'] ?? '');
    if (strlen($doc) > 11 || ($c['client_type'] ?? '') === 'company' || ($c['client_type'] ?? '') === 'hospital' || ($c['client_type'] ?? '') === 'government') {
        $countPJ++;
    } else {
        $countPF++;
    }

    if (!empty($c['last_order_date'])) {
        $countRecentOrders++;
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

.clients-fixed-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.clients-fixed-table th {
    height: 36px;
    padding: 6px 12px;
    box-sizing: border-box;
    background-color: var(--bg-subtle);
    border-bottom: 1px solid var(--border-color);
}

.clients-fixed-table td {
    height: 52px;
    padding: 4px 12px;
    box-sizing: border-box;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.clients-fixed-table tbody tr:last-child td {
    border-bottom: none;
}
</style>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
    <div>
        <h1 class="page-title">Gestão de Clientes</h1>
        <p class="page-subtitle">Cadastro geral, consultas, contatos e condições comerciais</p>
    </div>

    <button type="button" class="btn btn-primary" data-action="open-client-drawer" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; font-size: 13px; font-weight: 700; cursor: pointer;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 4v16m8-8H4"/>
        </svg>
        <span>Novo Cliente</span>
    </button>
</div>

<!-- MÉTRICAS DIRETAS NO FUNDO DA PÁGINA (FLAT STATS) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; padding: 4px 2px;">
    <div style="border-left: 3px solid #0284c7; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total de Clientes</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" id="stat-total"><?= $totalCount ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Cadastrados na base</div>
    </div>

    <div style="border-left: 3px solid #10b981; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">Pessoas Jurídicas (PJ)</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countPJ ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Indústrias, Hospitais e Comércio</div>
    </div>

    <div style="border-left: 3px solid #f59e0b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 0.05em;">Pessoas Físicas (PF)</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countPF ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Autônomos e Home Care</div>
    </div>

    <div style="border-left: 3px solid #64748b; padding-left: 12px;">
        <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Com Compras Recentes</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;"><?= $countRecentOrders ?></div>
        <div style="font-size: 11px; color: var(--text-dim);">Pedidos registrados no histórico</div>
    </div>
</div>

<!-- PAINEL ESTRUTURADO DE FILTROS DE CLIENTES -->
<div class="panel" style="margin-bottom: 20px;">
    <!-- Cabeçalho Limpo e Fixo com Botão de Limpar Integrado -->
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Filtros de Pesquisa</span>

        <button type="button" id="btn-reset-filters" style="background: none; border: none; font-size: 11px; font-weight: 600; color: #64748b; cursor: pointer; text-decoration: underline; visibility: hidden; opacity: 0; transition: opacity 0.15s ease;" data-action="reset-client-filters">
            Limpar filtros
        </button>
    </div>

    <!-- Corpo do Painel com os Campos de Filtro -->
    <div style="padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; background-color: var(--bg-surface);">
        <!-- Linha 1: Campo de Busca Principal -->
        <div>
            <label for="filter-client-search" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Busca Textual</label>
            <div class="search-wrapper" style="max-width: 100%;">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="filter-client-search" class="search-input" placeholder="Buscar por Razão Social, Nome Fantasia, CNPJ/CPF ou Cidade..." value="<?= htmlspecialchars($search ?? '') ?>" autocomplete="off" style="height: 36px; font-size: 13px;">
            </div>
        </div>

        <!-- Linha 2: Grade de Atributos Específicos de Clientes -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px;">
            <!-- 1. Tipo de Pessoa -->
            <div>
                <label for="filter-client-person" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Tipo de Pessoa</label>
                <select id="filter-client-person" class="filter-select">
                    <option value="">Todos</option>
                    <option value="pj">Pessoa Jurídica (PJ)</option>
                    <option value="pf">Pessoa Física (PF)</option>
                </select>
            </div>

            <!-- 2. Segmento de Atuação -->
            <div>
                <label for="filter-client-segment" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Segmento</label>
                <select id="filter-client-segment" class="filter-select">
                    <option value="">Todos</option>
                    <option value="industrial">Industrial / Solda</option>
                    <option value="hospital">Hospitalar / Saúde</option>
                    <option value="government">Governo / Licitações</option>
                    <option value="commercial">Comércio / Serviços</option>
                    <option value="individual">Residencial / Particular</option>
                </select>
            </div>

            <!-- 3. Cidade / Região -->
            <div>
                <label for="filter-client-city" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Cidade</label>
                <select id="filter-client-city" class="filter-select">
                    <option value="">Todas as Cidades</option>
                    <option value="são miguel dos campos">São Miguel dos Campos</option>
                    <option value="maceió">Maceió</option>
                    <option value="arapiraca">Arapiraca</option>
                    <option value="penedo">Penedo</option>
                    <option value="coruripe">Coruripe</option>
                    <option value="marechal deodoro">Marechal Deodoro</option>
                </select>
            </div>

            <!-- 4. Histórico de Compras -->
            <div>
                <label for="filter-client-status" style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-dim); margin-bottom: 4px;">Histórico de Compras</label>
                <select id="filter-client-status" class="filter-select">
                    <option value="">Todos</option>
                    <option value="with_orders">Com Compras Registradas</option>
                    <option value="no_orders">Sem Compras</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- PAINEL DA TABELA DE CLIENTES COM ALTURA 100% CONSTANTE E IMUTÁVEL -->
<div class="panel fixed-table-panel">
    <!-- Cabeçalho da Lista -->
    <div class="panel-header" style="background-color: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 10px 16px; height: 38px; box-sizing: border-box;">
        <span class="panel-title" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Clientes Registrados</span>

        <span id="client-result-count" style="font-size: 11px; color: var(--text-muted); font-weight: 500;">
            Mostrando <?= min(8, count($clients)) ?> de <?= number_format($totalCount, 0, ',', '.') ?> clientes
        </span>
    </div>

    <!-- Tabela de Clientes em Área Flexível com Altura Fixa -->
    <div class="fixed-table-container">
        <table class="data-table clients-fixed-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Razão Social / Nome</th>
                    <th style="width: 140px;">CNPJ / CPF</th>
                    <th style="width: 160px;">Cidade / UF</th>
                    <th style="width: 100px;">Cadastro</th>
                    <th style="width: 120px;">Última Compra</th>
                    <th style="text-align: center; width: 50px;">Status</th>
                    <th style="text-align: center; width: 60px;">Ações</th>
                </tr>
            </thead>
            <tbody id="clients-table-body">
                <?php if (empty($clients)): ?>
                    <tr id="empty-row" style="height: 452px;">
                        <td colspan="8" style="text-align: center; vertical-align: middle; color: var(--text-muted);">
                            Nenhum cliente cadastrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clients as $c): 
                        $docClean = preg_replace('/\D/', '', $c['document'] ?? '');
                        $isPJ = strlen($docClean) > 11 || ($c['client_type'] ?? '') === 'company' || ($c['client_type'] ?? '') === 'hospital' || ($c['client_type'] ?? '') === 'government';
                        $hasOrders = !empty($c['last_order_date']) ? '1' : '0';
                    ?>
                        <tr class="client-row" 
                            data-name="<?= htmlspecialchars(strtolower(($c['name'] ?? '') . ' ' . ($c['trade_name'] ?? '') . ' ' . ($c['document'] ?? '') . ' ' . ($c['city'] ?? ''))) ?>"
                            data-person="<?= $isPJ ? 'pj' : 'pf' ?>"
                            data-segment="<?= htmlspecialchars(strtolower($c['client_type'] ?? 'commercial')) ?>"
                            data-city="<?= htmlspecialchars(strtolower($c['city'] ?? '')) ?>"
                            data-orders="<?= $hasOrders ?>">
                            <td style="font-family: var(--font-mono); color: var(--text-dim); font-size: 11px;">#<?= $c['id'] ?></td>
                            <td>
                                <a href="/clients/<?= $c['id'] ?>" style="font-weight: 700; color: var(--text-main); text-decoration: none; font-size: 13px;">
                                    <?= htmlspecialchars($c['name']) ?>
                                </a>
                                <?php if (!empty($c['trade_name'])): ?>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 380px;">
                                         <?= htmlspecialchars($c['trade_name']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 11px; color: var(--text-main);">
                                <?= htmlspecialchars($c['document'] ?: '-') ?>
                            </td>
                            <td style="font-size: 12px; color: var(--text-main);">
                                <?= htmlspecialchars($c['city'] ?: '-') ?>/<?= htmlspecialchars($c['state'] ?: 'AL') ?>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 11px; color: var(--text-muted);">
                                <?= !empty($c['created_at']) ? date('d/m/Y', strtotime($c['created_at'])) : '-' ?>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 11px; color: var(--text-main); font-weight: 600;">
                                <?= !empty($c['last_order_date']) ? date('d/m/Y', strtotime($c['last_order_date'])) : '<span style="color: var(--text-dim); font-weight: normal;">Sem compras</span>' ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($c['is_active'])): ?>
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
                                        <a href="/clients/<?= $c['id'] ?>" class="action-menu-item">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                            </svg>
                                            <span>Dados & Cadastro</span>
                                        </a>
                                        <a href="/clients/<?= $c['id'] ?>/prices" class="action-menu-item">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                                            </svg>
                                            <span>Tabela de Preços</span>
                                        </a>
                                        <a href="/clients/<?= $c['id'] ?>/orders" class="action-menu-item">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                                            </svg>
                                            <span>Histórico de Pedidos</span>
                                        </a>
                                        <a href="/clients/<?= $c['id'] ?>/rentals" class="action-menu-item">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                                            </svg>
                                            <span>Vasilhames & Locação</span>
                                        </a>
                                        <button type="button" class="action-menu-item" data-action="open-client-drawer" data-payload="<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                            <span>Editar Cadastro</span>
                                        </button>
                                        <form method="POST" action="/clients/toggle-active" style="display: block; width: 100%;">
                                            <?= \Alfasic\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <input type="hidden" name="redirect_to" value="/clients">
                                            <button type="submit" class="action-menu-item">
                                                <?php if (!empty($c['is_active'])): ?>
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                    <span>Desativar Cliente</span>
                                                <?php else: ?>
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                    <span>Ativar Cliente</span>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                        <div class="action-menu-divider"></div>
                                        <form method="POST" action="/clients/delete" style="display: block; width: 100%;" data-confirm="Deseja realmente arquivar/excluir este cliente do sistema?">
                                            <?= \Alfasic\Core\Csrf::input() ?>
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="action-menu-item danger">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                </svg>
                                                <span>Excluir Cliente</span>
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
            Página <strong id="client-current-page-text" style="color: var(--text-main);">1</strong> de <strong id="client-total-pages-text" style="color: var(--text-main);">1</strong>
        </span>

        <div style="display: flex; align-items: center; gap: 6px;" id="client-pagination-controls">
            <button type="button" class="pagination-btn" id="client-btn-prev" disabled data-action="change-client-page" data-delta="-1" title="Página Anterior">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div id="client-page-numbers" style="display: flex; gap: 4px;">
                <button type="button" class="pagination-btn active">1</button>
            </div>
            <button type="button" class="pagination-btn" id="client-btn-next" disabled data-action="change-client-page" data-delta="1" title="Próxima Página">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>
</div>

<!-- Slide-Over Drawer (Modal Lateral à Direita) para Novo/Edição de Cliente -->
<?php require __DIR__ . '/drawer.php'; ?>

<script src="<?= \Alfasic\Core\View::asset('/js/pages/clients-index.js') ?>" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES, 'UTF-8') ?>"></script>