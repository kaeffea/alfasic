<?php

declare(strict_types=1);

namespace Alfasic\Middlewares;

use Alfasic\Models\User;

/**
 * Middleware de Autorização por Permissão Granular (RBAC)
 * Garante que o usuário possua privilégios específicos para a ação solicitada.
 */
class PermissionMiddleware implements MiddlewareInterface
{
    public function handle(array $params = []): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $requiredPermission = $params[0] ?? '';
        if ($requiredPermission === '') {
            return true;
        }

        // Verifica se o usuário tem a permissão necessária
        if (!User::hasPermission($user, $requiredPermission)) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));

            if ($isAjax) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Acesso não autorizado para seu perfil de usuário.']);
                return false;
            }

            http_response_code(403);
            echo '<!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <title>403 Acesso Negado - Alfagás</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                    .card { background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 32px; max-width: 440px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
                    h1 { font-size: 20px; color: #ef4444; margin-top: 0; }
                    p { font-size: 13px; color: #94a3b8; line-height: 1.5; }
                    a { display: inline-block; margin-top: 16px; padding: 8px 16px; background: #0284c7; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; font-weight: 600; }
                </style>
            </head>
            <body>
                <div class="card">
                    <h1>403 • Acesso Restrito</h1>
                    <p>Seu perfil de acesso (<strong>' . htmlspecialchars($user['role_name'] ?? 'Usuário') . '</strong>) não possui a permissão <code>' . htmlspecialchars($requiredPermission) . '</code> para acessar este recurso.</p>
                    <a href="/clients">Voltar ao Painel</a>
                </div>
            </body>
            </html>';
            return false;
        }

        return true;
    }
}
