<?php
// Etapa 2 do login: código de 6 dígitos por e-mail ou WhatsApp.
$errorMsg = null;
if (!empty($error)) {
    $errorMsg = match ($error) {
        'codigo_invalido' => 'Código inválido ou expirado. Confira a última mensagem recebida.',
        'muitas_tentativas' => 'Muitas tentativas. Volte ao login e comece de novo.',
        'csrf_invalido' => 'Sessão expirada. Volte ao login.',
        'email_falhou' => 'Não consegui enviar agora. Tente o outro canal ou avise a TI.',
        default => htmlspecialchars((string) $error),
    };
}
$masked = $masked_email ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação por e-mail - Alfagás</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: #0b132b; color: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .box { width: 100%; max-width: 400px; background: #ffffff; color: #0f172a; border-radius: 10px; padding: 32px; }
        h1 { font-size: 19px; font-weight: 800; margin-bottom: 6px; }
        p.sub { font-size: 13px; color: #64748b; margin-bottom: 20px; }
        p.sub strong { color: #0f172a; }
        .alert { background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; font-size: 12px; padding: 10px 14px; border-radius: 6px; margin-bottom: 18px; }
        label { display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px; }
        input.code { width: 100%; height: 46px; font-size: 20px; letter-spacing: 0.3em; text-align: center; font-family: monospace; border: 1px solid #cbd5e1; border-radius: 6px; margin-bottom: 14px; }
        .trust { display: flex; gap: 8px; align-items: center; font-size: 12px; color: #475569; margin-bottom: 20px; }
        button.main { width: 100%; height: 44px; background: #0284c7; color: #fff; border: none; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; }
        button.main:hover { background: #0369a1; }
        .resend { margin-top: 14px; text-align: center; }
        .resend button { background: none; border: none; color: #0284c7; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: underline; }
        .hint { margin-top: 14px; font-size: 11px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
<div class="box">
    <h1>Confira seu e-mail</h1>
    <p class="sub">Enviamos um código de 6 dígitos para <strong><?= htmlspecialchars($masked) ?></strong>. Vale por 10 minutos.</p>
    <?php if ($errorMsg): ?>
        <div class="alert"><?= $errorMsg ?></div>
    <?php endif; ?>
    <form method="POST" action="/2fa/verify" autocomplete="off">
        <?= \Alfasic\Core\Csrf::input() ?>
        <label for="code">Código</label>
        <input class="code" type="text" name="code" id="code" required autofocus inputmode="numeric" maxlength="6" placeholder="000000">
        <label class="trust"><input type="checkbox" name="trust_device" value="1" checked> Confiar neste dispositivo por 30 dias</label>
        <button class="main" type="submit">Verificar e entrar</button>
    </form>
    <form class="resend" method="POST" action="/2fa/resend">
        <?= \Alfasic\Core\Csrf::input() ?>
        <button type="submit">Não recebi — reenviar código</button>
    </form>

    <p class="hint">Sem acesso ao e-mail? Peça a um administrador para revogar seus dispositivos e confira o cadastro.</p>
</div>
</body>
</html>
