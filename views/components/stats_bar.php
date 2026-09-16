<?php
/**
 * Componente: Barra de Métricas Flat (Zero Emojis, Cores Institucionais)
 */
$stats = $stats ?? [];
?>
<?php if (!empty($stats)): ?>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; padding: 4px 2px;">
    <?php foreach ($stats as $s): ?>
        <?php
        $borderColor = $s['color'] ?? '#0284c7';
        $labelColor = match ($borderColor) {
            '#10b981' => '#059669',
            '#f59e0b' => '#d97706',
            '#64748b' => '#475569',
            default => 'var(--text-muted)'
        };
        ?>
        <div style="border-left: 3px solid <?= htmlspecialchars($borderColor) ?>; padding-left: 12px;">
            <div style="font-size: 11px; font-weight: 700; color: <?= htmlspecialchars($labelColor) ?>; text-transform: uppercase; letter-spacing: 0.05em;">
                <?= htmlspecialchars($s['label']) ?>
            </div>
            <div style="font-size: 24px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-top: 2px;" <?php if (!empty($s['id'])): ?>id="<?= htmlspecialchars($s['id']) ?>"<?php endif; ?>>
                <?= htmlspecialchars((string)$s['value']) ?>
            </div>
            <div style="font-size: 11px; color: var(--text-dim);">
                <?= htmlspecialchars($s['desc'] ?? '') ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
