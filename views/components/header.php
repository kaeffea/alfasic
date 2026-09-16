<?php
/**
 * Componente: Topbar Header Oficial da Alfagás
 * Conciso, direto e minimalista • ZERO Emojis • 100% SVGs Técnicos
 */
$sessionUser = $_SESSION['user'] ?? [];
$userName = $sessionUser['username'] ?? $sessionUser['employee_name'] ?? 'Usuário';
$userEmail = $sessionUser['email'] ?? '';
$userRole = $sessionUser['role_name'] ?? '';
$initials = strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', array_slice(preg_split('/\s+/', trim($userName)) ?: [], 0, 2))));
if ($initials === '') {
    $initials = 'U';
}
?>

<header id="main-header">
    <div style="display: flex; align-items: center;">
        <a href="/" class="header-brand" style="color: #ffffff; text-decoration: none; display: flex; align-items: center; gap: 10px; height: 28px;">
            <img src="/images/logo1.jpg" alt="Alfagás" style="height: 28px; width: auto; border-radius: 4px; object-fit: contain; display: block;">
            <span style="font-family: 'Plus Jakarta Sans', 'Montserrat', var(--font-sans); font-weight: 800; font-size: 19px; line-height: 28px; letter-spacing: 0.03em; color: #ffffff; display: flex; align-items: center;">ALFAGÁS</span>
        </a>

        <nav class="header-nav">
            <!-- Menu Principal: Gestão -->
            <div class="nav-dropdown">
                <button class="nav-btn dd-trigger" data-menu="dd-gestao">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                    <span>Gestão</span>
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="dropdown-menu" id="dd-gestao">
                    <a href="/clients" class="dd-item">
                        <span>Clientes</span>
                    </a>
                    <a href="/suppliers" class="dd-item">
                        <span>Fornecedores</span>
                    </a>
                    <a href="/employees" class="dd-item">
                        <span>Funcionários</span>
                    </a>
                    <a href="/products" class="dd-item">
                        <span>Produtos</span>
                    </a>
                    <a href="/cylinders" class="dd-item">
                        <span>Cilindros</span>
                    </a>
                </div>
            </div>

            <!-- Menu Secundário: Sistema & Administração -->
            <div class="nav-dropdown">
                <button class="nav-btn dd-trigger" data-menu="dd-sistema">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                    <span>Sistema</span>
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="dropdown-menu" id="dd-sistema">
                    <a href="/users" class="dd-item">
                        <span>Usuários</span>
                    </a>
                    <a href="/roles" class="dd-item">
                        <span>Perfis</span>
                    </a>
                    <a href="/audit" class="dd-item">
                        <span>Auditoria</span>
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- Canto Direito: Busca Rápida + Avatar de Perfil -->
    <div style="display: flex; align-items: center; gap: 14px;">
        <div class="header-search" id="cmd-search-trigger" title="Buscar (Tecla /)">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
            <span>Buscar...</span>
            <span class="header-search-badge">/</span>
        </div>

        <!-- Avatar do Usuário com Dropdown -->
        <div class="nav-dropdown">
            <div class="user-avatar-btn dd-trigger" data-menu="dd-user-profile" style="cursor: pointer; display: flex; align-items: center;" title="<?= htmlspecialchars($userName) ?> (<?= htmlspecialchars($userEmail) ?>)">
                <div class="user-avatar-circle" style="width: 32px; height: 32px; border-radius: 50%; background: #0284c7; color: #ffffff; font-weight: 700; font-size: 12px; display: flex; align-items: center; justify-content: center; border: 2px solid #334155; font-family: var(--font-sans); letter-spacing: 0.02em;">
                    <?= htmlspecialchars($initials) ?>
                </div>
            </div>
            <div class="dropdown-menu" id="dd-user-profile" style="right: 0; left: auto; width: 220px;">
                <div style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; margin-bottom: 4px;">
                    <div style="font-weight: 700; font-size: 13px; color: #0f172a;"><?= htmlspecialchars($userName) ?></div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 1px;"><?= htmlspecialchars($userEmail) ?></div>
                    <?php if ($userRole !== ''): ?>
                        <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($userRole) ?></div>
                    <?php endif; ?>
                </div>
                <a href="/users" class="dd-item">
                    <span>Gerenciar Usuários</span>
                </a>
                <a href="/roles" class="dd-item">
                    <span>Perfis de Acesso</span>
                </a>
                <a href="/audit" class="dd-item">
                    <span>Auditoria</span>
                </a>
                <div class="action-menu-divider" style="margin: 4px 0;"></div>
                <form method="POST" action="/logout" style="margin: 0;">
                    <?= \Alfasic\Core\Csrf::input() ?>
                    <button type="submit" class="dd-item" style="color: #dc2626; background: none; border: none; width: 100%; text-align: left; cursor: pointer;">
                        <span>Sair do Sistema</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
