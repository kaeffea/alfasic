<?php

namespace Alfasic\Core;

class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/app'): void
    {
        $viewsDir = dirname(__DIR__, 2) . '/views';
        
        $candidates = [
            $viewsDir . '/' . $template . '.php',
            $viewsDir . '/pages/' . $template . '.php',
            $viewsDir . '/pages/management/' . $template . '.php',
            $viewsDir . '/pages/system/' . $template . '.php',
        ];

        $viewFile = null;
        foreach ($candidates as $cand) {
            if (file_exists($cand)) {
                $viewFile = $cand;
                break;
            }
        }

        if ($viewFile === null) {
            http_response_code(500);
            echo "500 Internal Server Error: View [{$template}] not found.";
            return;
        }

        extract($data, EXTR_SKIP);

        // Nonce da CSP futura disponivel em todas as views como $cspNonce
        if (!isset($cspNonce)) {
            $cspNonce = $GLOBALS['csp_nonce'] ?? null;
        }

        // 1. Renderiza o conteúdo da página específica no buffer
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // 2. Se houver um layout definido, envolve o conteúdo no layout mestre
        if ($layout !== null) {
            $layoutFile = dirname(__DIR__, 2) . '/views/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                ob_start();
                require $layoutFile;
                echo ob_get_clean();
                return;
            }
        }

        // 3. Caso não haja layout ou o arquivo não exista, imprime o conteúdo direto
        echo $content;
    }

    /**
     * URL de asset com versão pelo mtime (cache agressivo, bust automático).
     * Troca o ?v=time() que impedia qualquer cache e causava delay nas abas.
     */
    public static function asset(string $relativePath): string
    {
        static $cache = [];
        if (!isset($cache[$relativePath])) {
            $file = dirname(__DIR__, 2) . '/public/' . ltrim($relativePath, '/');
            $ver = is_file($file) ? (string) filemtime($file) : '1';
            $cache[$relativePath] = '/' . ltrim($relativePath, '/') . '?v=' . $ver;
        }
        return $cache[$relativePath];
    }

    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}