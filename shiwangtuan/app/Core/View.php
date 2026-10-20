<?php
/**
 * 视图渲染器 (服务端渲染, 利于 SEO)
 */
declare(strict_types=1);

namespace App\Core;

class View
{
    /**
     * @param string $path 相对 views/ 的路径, 不含 .php
     * @param array<string,mixed> $vars
     */
    public static function render(string $path, array $vars = []): void
    {
        $file = VIEW_ROOT . '/' . ltrim($path, '/') . '.php';
        if (!is_file($file)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
            return;
        }
        extract($vars, EXTR_SKIP);
        ob_start();
        require $file;
        echo ob_get_clean();
    }
}
