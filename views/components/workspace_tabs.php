<?php
/**
 * Componente: Abas Multitarefas Fixas no Topo (Tabbed ERP Workspace)
 * Suporte a rolagem horizontal via setas e touchpad/mousewheel • Aba única por cliente
 */
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
?>
<div class="workspace-tabs-wrapper">
    <button type="button" id="tabs-scroll-left" class="tabs-scroll-btn" title="Rolar abas para a esquerda" aria-label="Rolar para esquerda">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <div id="workspace-tabs-scroll" class="workspace-tabs-scroll-container">
        <div id="workspace-tabs" data-page-title="<?= htmlspecialchars($pageTitle ?? $title ?? '') ?>" data-current-url="<?= htmlspecialchars($currentUri) ?>"></div>
    </div>
    <button type="button" id="tabs-scroll-right" class="tabs-scroll-btn" title="Rolar abas para a direita" aria-label="Rolar para direita">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </button>
</div>
