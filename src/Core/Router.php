<?php

declare(strict_types=1);

namespace Alfasic\Core;

use Alfasic\Middlewares\MiddlewareInterface;
use RuntimeException;

/**
 * Roteador HTTP Central do Alfasic
 * Suporta dispatching MVC puro com pipeline encadeado de Middlewares.
 */
class Router
{
    private array $dynamicRoutes = [
        'GET' => [],
        'POST' => [],
    ];

    /**
     * Registra uma rota GET
     *
     * @param string $path Caminho da URL (ex: '/users' ou '/clients/{id}')
     * @param array|callable $handler [ControllerClass, 'method'] ou Closure
     * @param array $middlewares Lista de middlewares a executar antes do handler
     */
    public function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * Registra uma rota POST
     *
     * @param string $path Caminho da URL (ex: '/users/store' ou '/clients/{id}/edit')
     * @param array|callable $handler [ControllerClass, 'method'] ou Closure
     * @param array $middlewares Lista de middlewares a executar antes do handler
     */
    public function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middlewares): void
    {
        if (str_contains($path, '{')) {
            // Rota dinâmica com parâmetros nomeados (ex: /clients/{id}/prices -> #^/clients/(?P<id>[^/]+)/prices$#)
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
            $regex = '#^' . $pattern . '$#';
            $this->dynamicRoutes[$method][$regex] = [
                'handler' => $handler,
                'middlewares' => $middlewares,
            ];
        } else {
            // Rota estática rápida O(1)
            $this->routes[$method][$path] = [
                'handler' => $handler,
                'middlewares' => $middlewares,
            ];
        }
    }

    private array $middlewareAliases = [
        'auth' => \Alfasic\Middlewares\AuthMiddleware::class,
        'guest' => \Alfasic\Middlewares\GuestMiddleware::class,
        'permission' => \Alfasic\Middlewares\PermissionMiddleware::class,
    ];

    /**
     * Executa a rota correspondente para o método e URI da requisição
     */
    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $method = strtoupper($method);
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        $matchedRoute = null;
        $routeParams = [];

        // 1. Tenta correspondência exata O(1)
        if (isset($this->routes[$method][$path])) {
            $matchedRoute = $this->routes[$method][$path];
        } else {
            // 2. Tenta correspondência de rotas dinâmicas RESTful
            foreach ($this->dynamicRoutes[$method] as $regex => $routeInfo) {
                if (preg_match($regex, $path, $matches)) {
                    $matchedRoute = $routeInfo;
                    foreach ($matches as $k => $v) {
                        if (is_string($k)) {
                            $routeParams[$k] = $v;
                            $_GET[$k] = $v; // Disponibiliza em $_GET para retrocompatibilidade
                        }
                    }
                    break;
                }
            }
        }

        if ($matchedRoute === null) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        $handler = $matchedRoute['handler'];
        $middlewares = $matchedRoute['middlewares'] ?? [];

        // 3. Executa a cadeia de Middlewares
        foreach ($middlewares as $mw) {
            $middlewareClass = $mw;
            $params = [];

            if (is_array($mw)) {
                $middlewareClass = $mw[0];
                $params = $mw[1] ?? [];
            } elseif (is_string($mw) && str_contains($mw, ':')) {
                [$alias, $rawParam] = explode(':', $mw, 2);
                $middlewareClass = $this->middlewareAliases[$alias] ?? $alias;
                $params = explode(',', $rawParam);
            } elseif (is_string($mw) && isset($this->middlewareAliases[$mw])) {
                $middlewareClass = $this->middlewareAliases[$mw];
            }

            if (!class_exists($middlewareClass)) {
                throw new RuntimeException("Middleware [{$middlewareClass}] não encontrado.");
            }

            $instance = new $middlewareClass();
            if ($instance instanceof MiddlewareInterface) {
                $canProceed = $instance->handle($params);
                if (!$canProceed) {
                    return; // Middleware bloqueou ou redirecionou
                }
            }
        }

        // 4. Normaliza parâmetros: dígitos viram int (strict_types derrubaria "5" em int $id)
        $args = array_map(
            fn($v) => (is_string($v) && ctype_digit($v) ? (int) $v : $v),
            array_values($routeParams)
        );

        // 5. Executa o Handler (Controller ou Closure) passando os parâmetros da rota
        if (is_callable($handler)) {
            call_user_func_array($handler, $args);
            return;
        }

        if (is_array($handler)) {
            [$controllerClass, $action] = $handler;
            if (!class_exists($controllerClass)) {
                throw new RuntimeException("Controller [{$controllerClass}] não encontrado.");
            }

            $controller = new $controllerClass();
            if (!method_exists($controller, $action)) {
                throw new RuntimeException("Método [{$action}] não encontrado no controller [{$controllerClass}].");
            }

            $controller->$action(...$args);
            return;
        }
    }
}