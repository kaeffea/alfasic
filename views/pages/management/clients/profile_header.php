<?php
/**
 * Componente: Cabeçalho Unificado do Cliente com Breadcrumbs e Abas com Contadores
 */
$stats = $stats ?? \Alfasic\Models\Client::getStats((int) $client['id']);
$activeTab = $activeTab ?? 'show';

$tabNames = [
    'show' => 'Dados & Cadastro',
    'prices' => 'Tabela de Preços',
];
$currentTabName = $tabNames[$activeTab] ?? 'Detalhes';
?>

<div class="page-header" style="margin-bottom: 20px;">
    <!-- Breadcrumbs Corporativo -->
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; flex-wrap: wrap;">
        <nav aria-label="Breadcrumb" class="breadcrumb">
            <a href="/clients" class="breadcrumb-link">Clientes</a>
            <span class="breadcrumb-separator">/</span>
            <a href="/clients/<?= $client['id'] ?>" class="breadcrumb-link" style="font-family: var(--font-mono); font-size: 12px;">#<?= $client['id'] ?></a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current"><?= htmlspecialchars($currentTabName) ?></span>
        </nav>

        <?php if ($activeTab === 'show'): ?>
            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" data-action="open-client-drawer" data-payload="<?= htmlspecialchars(json_encode($client), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline btn-sm"
                    style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar Cadastro
                </button>
                <form method="POST" action="/clients/toggle-active" style="display: inline-block; margin: 0;">
                    <?= \Alfasic\Core\Csrf::input() ?>
                    <input type="hidden" name="id" value="<?= $client['id'] ?>">
                    <input type="hidden" name="redirect_to" value="/clients/<?= $client['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" style="cursor: pointer;">
                        <?= !empty($client['is_active']) ? 'Desativar' : 'Ativar' ?>
                    </button>
                </form>
                <form method="POST" action="/clients/delete" style="display: inline-block; margin: 0;" data-confirm="Deseja realmente arquivar/excluir este cliente do sistema?">
                    <?= \Alfasic\Core\Csrf::input() ?>
                    <input type="hidden" name="id" value="<?= $client['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" style="cursor: pointer; color: #ef4444; border-color: rgba(239, 68, 68, 0.3);">
                        Excluir
                    </button>
                </form>
            </div>
            <?php require __DIR__ . '/drawer.php'; ?>
        <?php endif; ?>
    </div>

    <!-- Título com Nome Completo do Cliente -->
    <h1 class="page-title" style="font-size: 26px; font-weight: 800; color: var(--text-main); letter-spacing: -0.02em; margin-bottom: 0;">
        <?= htmlspecialchars($client['name']) ?>
    </h1>
    <?php if (!empty($client['trade_name'])): ?>
        <div style="font-size: 14px; color: var(--text-muted); font-weight: 600; margin-top: 2px;">
            <?= htmlspecialchars($client['trade_name']) ?>
        </div>
    <?php endif; ?>
</div>

<!-- Abas de Navegação do Cliente (pedidos/cilindros/licitações serão recriados nas Sprints próprias) -->
<div style="display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;">
    <a href="/clients/<?= $client['id'] ?>" class="btn <?= $activeTab === 'show' ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="<?= $activeTab !== 'show' ? 'border: none; color: var(--text-muted);' : 'font-weight: 600;' ?>">
        Dados & Cadastro
    </a>
    <a href="/clients/<?= $client['id'] ?>/prices" class="btn <?= $activeTab === 'prices' ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="<?= $activeTab !== 'prices' ? 'border: none; color: var(--text-muted);' : 'font-weight: 600;' ?>">
        Tabela de Preços (<?= (int)($stats['prices'] ?? 0) ?>)
    </a>
</div>
