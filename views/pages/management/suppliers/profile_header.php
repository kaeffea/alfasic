<?php
/**
 * Componente: Cabeçalho Unificado de Fornecedor com Breadcrumbs e Ações
 */
$activeTab = $activeTab ?? 'show';
$currentTabName = ($activeTab === 'show') ? 'Dados & Cadastro' : 'Detalhes';
?>

<div class="page-header" style="margin-bottom: 20px;">
    <!-- Breadcrumbs Corporativo -->
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; flex-wrap: wrap;">
        <nav aria-label="Breadcrumb" class="breadcrumb">
            <a href="/suppliers" class="breadcrumb-link">Fornecedores</a>
            <span class="breadcrumb-separator">/</span>
            <a href="/suppliers/<?= $supplier['id'] ?>" class="breadcrumb-link" style="font-family: var(--font-mono); font-size: 12px;">#<?= $supplier['id'] ?></a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current"><?= htmlspecialchars($currentTabName) ?></span>
        </nav>

        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" data-action="open-supplier-drawer" data-payload="<?= htmlspecialchars(json_encode($supplier), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline btn-sm"
                style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar Cadastro
            </button>
            <form method="POST" action="/suppliers/toggle-active" style="display: inline-block; margin: 0;">
                <?= \Alfasic\Core\Csrf::input() ?>
                <input type="hidden" name="id" value="<?= $supplier['id'] ?>">
                <input type="hidden" name="redirect_to" value="/suppliers/<?= $supplier['id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm" style="cursor: pointer;">
                    <?= !empty($supplier['is_active']) ? 'Desativar' : 'Ativar' ?>
                </button>
            </form>
            <form method="POST" action="/suppliers/delete" style="display: inline-block; margin: 0;" data-confirm="Deseja realmente arquivar/excluir este fornecedor do sistema?">
                <?= \Alfasic\Core\Csrf::input() ?>
                <input type="hidden" name="id" value="<?= $supplier['id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm" style="cursor: pointer; color: #ef4444; border-color: rgba(239, 68, 68, 0.3);">
                    Excluir
                </button>
            </form>
            <?php require __DIR__ . '/drawer.php'; ?>
        </div>
    </div>

    <!-- Título com Nome do Fornecedor / Usina -->
    <h1 class="page-title" style="font-size: 26px; font-weight: 800; color: var(--text-main); letter-spacing: -0.02em; margin-bottom: 0;">
        <?= htmlspecialchars($supplier['name']) ?>
    </h1>
    <?php if (!empty($supplier['trade_name'])): ?>
        <div style="font-size: 14px; color: var(--text-muted); font-weight: 600; margin-top: 2px;">
            <?= htmlspecialchars($supplier['trade_name']) ?> &bull; <span style="text-transform: uppercase; font-size: 12px; font-weight: 700; color: var(--text-dim);"><?= htmlspecialchars($supplier['supplier_type'] ?? 'usina') ?></span>
        </div>
    <?php endif; ?>
</div>
