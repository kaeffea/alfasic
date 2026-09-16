<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Alfasic - Sistema de Gestão de Gases') ?></title>
    <link rel="icon" type="image/jpeg" href="/images/logo1.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800;900&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= \Alfasic\Core\View::asset('/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= \Alfasic\Core\View::asset('/css/style.css') ?>">
</head>
<body>

    <div class="app-layout">
        <!-- 1. Full Topbar Header Oficial com Dropdowns -->
        <?php require dirname(__DIR__) . '/components/header.php'; ?>

        <!-- 2. Abas Multitarefas Fixas no Topo (Tabbed ERP Workspace) -->
        <?php require dirname(__DIR__) . '/components/workspace_tabs.php'; ?>

        <!-- 3. Dynamic Page Content Slot (100% de Largura Útil) -->
        <main class="page-content">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; justify-content: space-between;">
                    <span><strong>Sucesso:</strong> <?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    <button data-action="dismiss-parent" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #166534;">&times;</button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; justify-content: space-between;">
                    <span><strong>Erro:</strong> <?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    <button data-action="dismiss-parent" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #991b1b;">&times;</button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>

    <!-- 4. Command Palette, Modal de Justificativa & Toasts Globais -->
    <?php require dirname(__DIR__) . '/components/command_palette.php'; ?>
    <?php require dirname(__DIR__) . '/components/audit_modal.php'; ?>

    <!-- Vanilla JS (nonce da futura CSP; ignorado pela policy vigente) -->
    <script src="<?= \Alfasic\Core\View::asset('/js/app.js') ?>" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
