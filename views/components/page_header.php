<?php
/**
 * Componente: Cabeçalho Padrão de Página do Alfasic
 */
$title = $title ?? 'Gestão';
$subtitle = $subtitle ?? '';
$actionButtonText = $actionButtonText ?? null;
$actionButtonHref = $actionButtonHref ?? null;
$actionButtonIconSvg = $actionButtonIconSvg ?? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>';
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
    <div>
        <h1 class="page-title"><?= htmlspecialchars($title) ?></h1>
        <?php if (!empty($subtitle)): ?>
            <p class="page-subtitle"><?= htmlspecialchars($subtitle) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($actionButtonText && $actionButtonHref): ?>
        <a href="<?= htmlspecialchars($actionButtonHref) ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; font-size: 13px; font-weight: 700; text-decoration: none;">
            <?= $actionButtonIconSvg ?>
            <span><?= htmlspecialchars($actionButtonText) ?></span>
        </a>
    <?php endif; ?>
</div>
