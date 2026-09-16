<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Restrito - Alfagás</title>
    <link rel="icon" type="image/jpeg" href="/images/logo1.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@700;800;900&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-font-smoothing: antialiased;
        }

        html, body {
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #0b132b;
        }

        .split-layout {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        /* ----------------------------------------------------
           LADO ESQUERDO: Painel Institucional & Carrossel
           ---------------------------------------------------- */
        .brand-panel {
            flex: 1.4;
            background: linear-gradient(145deg, #0b132b 0%, #16203b 50%, #0f172a 100%);
            color: #ffffff;
            padding: 44px 52px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 15% 25%, rgba(2, 132, 199, 0.12) 0%, transparent 50%),
                              radial-gradient(circle at 85% 75%, rgba(14, 165, 233, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }

        .brand-top {
            display: flex;
            align-items: center;
            gap: 14px;
            z-index: 1;
        }

        .brand-logo-img {
            height: 32px;
            width: auto;
            border-radius: 5px;
            object-fit: contain;
        }

        .brand-logo-text {
            font-family: 'Plus Jakarta Sans', 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 34px;
            letter-spacing: 0.03em;
            color: #ffffff;
            line-height: 1;
        }

        .brand-content {
            width: 100%;
            max-width: 720px;
            z-index: 1;
            margin-top: 36px;
            margin-bottom: 20px;
        }

        .brand-headline {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.3;
            letter-spacing: -0.02em;
            color: #f8fafc;
            margin-bottom: 10px;
        }

        .brand-description {
            font-size: 14px;
            line-height: 1.6;
            color: #94a3b8;
            margin-bottom: 28px;
        }

        /* Carrossel de Cards com Navegação Integrada na Base */
        .carousel-section {
            width: 100%;
            position: relative;
        }

        .cards-track-viewport {
            width: 100%;
            overflow: hidden;
            border-radius: 8px;
        }

        .cards-track {
            display: flex;
            gap: 14px;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .feature-card {
            flex: 0 0 calc((100% - 28px) / 3);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 16px 18px;
            backdrop-filter: blur(8px);
            min-height: 115px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .feature-title {
            font-size: 13px;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 6px;
        }

        .feature-desc {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.45;
        }

        /* Barra Inferior com Dots e Setas Minimalistas */
        .carousel-bottom-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 16px;
            padding: 0 2px;
        }

        .carousel-dots {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .carousel-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .carousel-dot.active {
            width: 18px;
            border-radius: 3px;
            background: #38bdf8;
        }

        .carousel-nav-arrows {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ghost-arrow-btn {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease, transform 0.1s ease;
        }

        .ghost-arrow-btn:hover {
            color: #ffffff;
            transform: scale(1.15);
        }

        .ghost-arrow-btn:active {
            color: #38bdf8;
        }

        .brand-footer {
            font-size: 12px;
            color: #64748b;
            z-index: 1;
        }

        /* ----------------------------------------------------
           LADO DIREITO: Painel de Autenticação / Login
           ---------------------------------------------------- */
        .auth-panel {
            flex: 0.9;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .auth-box {
            width: 100%;
            max-width: 380px;
        }

        .auth-header {
            margin-bottom: 26px;
        }

        .auth-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: 13px;
            color: #64748b;
        }

        .auth-alert {
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: #fef2f2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            height: 42px;
            padding: 10px 14px;
            font-size: 13px;
            font-family: inherit;
            color: #0f172a;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
        }

        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper .form-input {
            padding-right: 42px;
        }

        .password-toggle-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }

        .password-toggle-btn:hover {
            color: #0f172a;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #475569;
            cursor: pointer;
            user-select: none;
        }

        .remember-checkbox {
            border-radius: 4px;
            accent-color: #0284c7;
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            height: 44px;
            background-color: #0284c7;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            font-family: inherit;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: background-color 0.15s ease, transform 0.05s ease;
        }

        .btn-submit:hover {
            background-color: #0369a1;
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .auth-footer {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }

        @media (max-width: 1024px) {
            .feature-card {
                flex: 0 0 calc((100% - 14px) / 2);
            }
        }

        @media (max-width: 900px) {
            .brand-panel {
                display: none;
            }
            .auth-panel {
                flex: 1;
            }
        }
    </style>
</head>
<body>

<div class="split-layout">
    <!-- LADO ESQUERDO: Painel Institucional & Carrossel de 3 Cards -->
    <div class="brand-panel">
        <div class="brand-top">
            <img src="/images/logo1.jpg" alt="Alfagás" class="brand-logo-img">
            <span class="brand-logo-text">ALFAGÁS</span>
        </div>

        <div class="brand-content">
            <h1 class="brand-headline">Distribuição de Gases Industriais, Medicinais e Especiais.</h1>
            <p class="brand-description">Plataforma operacional e comercial para gestão de contratos, controle de vasilhames e faturamento corporativo.</p>

            <!-- Carrossel com 3 Cards Lado a Lado -->
            <div class="carousel-section">
                <div class="cards-track-viewport" id="cardsViewport">
                    <div class="cards-track" id="cardsTrack">
                        <!-- Card 1 -->
                        <div class="feature-card">
                            <div class="feature-title">Controle de Vasilhames</div>
                            <div class="feature-desc">Rastreabilidade em tempo real de cilindros cheios em comodato e retorno de vazios.</div>
                        </div>
                        <!-- Card 2 -->
                        <div class="feature-card">
                            <div class="feature-title">Logística & Frotas</div>
                            <div class="feature-desc">Monitoramento de rotas de entrega, CNH/MOPP e carregamentos na usina.</div>
                        </div>
                        <!-- Card 3 -->
                        <div class="feature-card">
                            <div class="feature-title">Tabelas & ARPs</div>
                            <div class="feature-desc">Gestão de tabelas exclusivas por contrato e controle de cotas homologadas.</div>
                        </div>
                        <!-- Card 4 -->
                        <div class="feature-card">
                            <div class="feature-title">Pedidos & Vendas</div>
                            <div class="feature-desc">Agilidade no despacho comercial, emissão de romaneios e conciliação fiscal.</div>
                        </div>
                        <!-- Card 5 -->
                        <div class="feature-card">
                            <div class="feature-title">Gestão de Clientes</div>
                            <div class="feature-desc">Fichas cadastrais completas, histórico de compras e condições de faturamento.</div>
                        </div>
                        <!-- Card 6 -->
                        <div class="feature-card">
                            <div class="feature-title">Segurança & RBAC</div>
                            <div class="feature-desc">Controle de acesso granular por usuário e trilha de auditoria completa.</div>
                        </div>
                    </div>
                </div>

                <!-- Barra de Navegação Inferior: Bolinhas na esquerda, Setas minimalistas sem borda na direita -->
                <div class="carousel-bottom-nav">
                    <div class="carousel-dots" id="carouselDots">
                        <div class="carousel-dot active" data-action="goto-card" data-idx="0"></div>
                        <div class="carousel-dot" data-action="goto-card" data-idx="1"></div>
                        <div class="carousel-dot" data-action="goto-card" data-idx="2"></div>
                        <div class="carousel-dot" data-action="goto-card" data-idx="3"></div>
                    </div>

                    <div class="carousel-nav-arrows">
                        <button type="button" class="ghost-arrow-btn" data-action="slide-step" data-delta="-1" title="Anterior" aria-label="Módulo anterior">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <button type="button" class="ghost-arrow-btn" data-action="slide-step" data-delta="1" title="Próximo" aria-label="Próximo módulo">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="brand-footer">
            <p>© 2026 Alfa Comercial de Gases LTDA • Todos os direitos reservados.</p>
        </div>
    </div>

    <!-- LADO DIREITO: Painel de Autenticação -->
    <div class="auth-panel">
        <div class="auth-box">
            <div class="auth-header">
                <h2 class="auth-title">Acessar o Sistema</h2>
                <p class="auth-subtitle">Informe seu usuário ou e-mail corporativo para continuar</p>
            </div>

            <?php 
            $errorMsg = null;
            if (!empty($error)) {
                $errorMsg = match ($error) {
                    'credenciais_invalidas' => 'Usuário ou senha incorretos. Verifique os dados.',
                    'muitas_tentativas' => 'Muitas tentativas falhas. Aguarde 15 minutos para tentar novamente.',
                    'campos_vazios' => 'Por favor, informe seu usuário/e-mail e senha.',
                    'csrf_invalido' => 'Sessão expirada. Recarregue a página e tente novamente.',
                    'sessao_expirada' => 'Sua sessão expirou por inatividade. Entre novamente.',
                    'suspended' => 'Sua conta de acesso está suspensa. Procure a TI/Diretoria.',
                    default => htmlspecialchars($error),
                };
            }
            ?>
            <?php if ($errorMsg): ?>
                <div class="auth-alert">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span><?= $errorMsg ?></span>
                </div>
            <?php elseif (!empty($loggedOut)): ?>
                <div class="auth-alert" style="background-color: #f0fdf4; border-color: #86efac; color: #166534;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span>Sua sessão foi encerrada com segurança.</span>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <?= \Alfasic\Core\Csrf::input() ?>

                <div class="form-group">
                    <label class="form-label">Usuário ou E-mail</label>
                    <input type="text" name="username" id="username-input" required autofocus placeholder="nome.sobrenome ou usuario@alfagas.com.br" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Senha de Acesso</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password-input" required placeholder="Digite sua senha" class="form-input">
                        <button type="button" class="password-toggle-btn" data-action="toggle-password" title="Mostrar/Ocultar Senha" aria-label="Mostrar senha">
                            <svg id="eye-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    Entrar no Sistema
                </button>
            </form>

            <div class="auth-footer">
                <p>Acesso restrito e monitorado para colaboradores autorizados.</p>
            </div>
        </div>
    </div>
</div>

<script src="<?= \Alfasic\Core\View::asset('/js/pages/login.js') ?>" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES, 'UTF-8') ?>"></script>

</body>
</html>
