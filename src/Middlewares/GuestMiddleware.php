<?php

declare(strict_types=1);

namespace Alfasic\Middlewares;

/**
 * Middleware para Rotas de Visitante (como /login)
 * Se o usuário já estiver autenticado, redireciona diretamente para o painel.
 */
class GuestMiddleware implements MiddlewareInterface
{
    public function handle(array $params = []): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            header('Location: /clients');
            exit;
        }

        return true;
    }
}
