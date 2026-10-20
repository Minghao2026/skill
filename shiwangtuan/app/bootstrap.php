<?php
/**
 * 引导文件: 定义路径常量 + 注册自动加载 + 载入配置
 * 由 public/ 下各入口 require 引入
 */
declare(strict_types=1);

// 项目根目录 (/www/wwwroot/shiwangtuan)
define('APP_ROOT', dirname(__DIR__));
// 对外 web 根目录 (public)
define('PUBLIC_ROOT', APP_ROOT . '/public');
// 视图目录
define('VIEW_ROOT', APP_ROOT . '/views');

date_default_timezone_set('Asia/Shanghai');

// 统一会话名称 (Auth 与 CSRF 校验必须使用同一会话名, 否则无法续接会话)
session_name($GLOBALS['APP_CONFIG']['session_name'] ?? 'swt_session');

// ---- 简易 PSR-4 风格自动加载 (App\ 命名空间 -> app/) ----
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, 4) !== 0) {
        return;
    }
    $relative = substr($class, 4);
    $file = APP_ROOT . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// 载入配置
$GLOBALS['APP_CONFIG'] = require APP_ROOT . '/config/app.php';
$GLOBALS['DB_CONFIG']  = require APP_ROOT . '/config/database.php';

// ---- 生产环境错误控制 ----
// 线上必须关闭 display_errors: 否则任何 notice/warning 都会把
// 服务器绝对路径输出到页面, 既泄漏信息也破坏布局。
// 错误仍然完整写入 logs/, 便于排查。
$__debug = !empty($GLOBALS['APP_CONFIG']['debug']);
if ($__debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    $__logDir = $GLOBALS['APP_CONFIG']['log_dir'] ?? (APP_ROOT . '/logs');
    if (is_dir($__logDir) && is_writable($__logDir)) {
        ini_set('error_log', rtrim($__logDir, '/') . '/php-error.log');
    }
}
unset($__debug, $__logDir);

if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    // 入口脚本在需要时自行 session_start(), 这里不强制开启
}
