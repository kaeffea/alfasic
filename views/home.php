<?php
/**
 * View: Painel Operacional da Alfagás (cadastros; pedidos virão no motor comercial)
 */
$totalClients = (int)($metrics['total_clients'] ?? 0);
$totalProducts = (int)($metrics['total_products'] ?? 0);
$totalSuppliers = (int)($metrics['total_suppliers'] ?? 0);
$totalEmployees = (int)($metrics['total_employees'] ?? 0);
?>

<!-- Header do Painel -->
<div class="page-header" style="margin-bottom: 20px;">
    <div class="breadcrumb" style="font-size: 11px; color: var(--text-dim); margin-bottom: 4px;">
        <span>Início</span> <span>/</span>
        <span style="color: var(--text-main); font-weight: 600;">Painel Operacional</span>
    </div>
    <h1 class="page-title" style="font-size: 20px; font-weight: 800; color: var(--text-main); letter-spacing: -0.02em; margin: 0 0 2px 0;">Painel Operacional</h1>
    <p class="page-subtitle" style="font-size: 12px; color: var(--text-muted); margin: 0;">
        Cadastros ativos — Gestão (clientes, produtos, fornecedores, funcionários) e Sistema (usuários, perfis, auditoria)
    </p>
</div>

<!-- 4 Cards de Cadastros -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 22px;">
    <div class="panel" style="padding: 16px 18px; margin: 0;">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px;">
            Clientes
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-main); font-family: var(--font-mono); letter-spacing: -0.02em;">
            <?= number_format($totalClients, 0, ',', '.') ?>
        </div>
        <div style="font-size: 11px; color: var(--text-dim); margin-top: 4px;">
            <a href="/clients" style="color: inherit;">Ver gestão de clientes &rarr;</a>
        </div>
    </div>

    <div class="panel" style="padding: 16px 18px; margin: 0;">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px;">
            Produtos
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-main); font-family: var(--font-mono); letter-spacing: -0.02em;">
            <?= number_format($totalProducts, 0, ',', '.') ?>
        </div>
        <div style="font-size: 11px; color: var(--text-dim); margin-top: 4px;">
            <a href="/products" style="color: inherit;">Ver catálogo &rarr;</a>
        </div>
    </div>

    <div class="panel" style="padding: 16px 18px; margin: 0;">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px;">
            Fornecedores
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-main); font-family: var(--font-mono); letter-spacing: -0.02em;">
            <?= number_format($totalSuppliers, 0, ',', '.') ?>
        </div>
        <div style="font-size: 11px; color: var(--text-dim); margin-top: 4px;">
            <a href="/suppliers" style="color: inherit;">Ver fornecedores &rarr;</a>
        </div>
    </div>

    <div class="panel" style="padding: 16px 18px; margin: 0;">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px;">
            Funcionários
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-main); font-family: var(--font-mono); letter-spacing: -0.02em;">
            <?= number_format($totalEmployees, 0, ',', '.') ?>
        </div>
        <div style="font-size: 11px; color: var(--text-dim); margin-top: 4px;">
            <a href="/employees" style="color: inherit;">Ver quadro &rarr;</a>
        </div>
    </div>
</div>

<!-- Últimos Clientes Cadastrados -->
<div class="panel" style="margin: 0;">
    <div class="panel-header">
        <h2 style="font-size: 13px; font-weight: 700; color: var(--text-main); margin: 0;">
            Últimos Clientes Cadastrados
        </h2>
    </div>

    <div style="display: flex; flex-direction: column;">
        <?php if (empty($recentClients)): ?>
            <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">
                Nenhum cliente cadastrado ainda.
            </div>
        <?php else: ?>
            <?php foreach ($recentClients as $rc): ?>
                <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 4px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <a href="/clients/<?= $rc['id'] ?>" style="font-weight: 600; font-size: 12px; color: var(--text-main); text-decoration: none; line-height: 1.3;" class="table-link">
                            <?= htmlspecialchars($rc['name']) ?>
                        </a>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                        <span><?= htmlspecialchars($rc['document'] ?: 'Doc não informado') ?></span>
                        <span><?= htmlspecialchars($rc['city'] ?: 'AL') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div style="padding: 10px 14px; text-align: center; background: #f8fafc; border-top: 1px solid var(--border-color);">
        <a href="/clients" class="panel-view-all-link" style="display: block; padding: 6px 10px; font-size: 12px; font-weight: 600; color: var(--text-main); text-decoration: none; border-radius: 4px; transition: background 0.15s ease;">
            Ver todos os <?= number_format($totalClients ?? count($recentClients), 0, ',', '.') ?> clientes &rarr;
        </a>
    </div>
</div>
