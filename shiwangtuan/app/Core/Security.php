<?php
/**
 * 安全辅助: 输出转义 / CSRF / 输入校验 / 上传
 * 规范: 输出 htmlspecialchars(ENT_QUOTES,UTF-8); CSRF 令牌;
 *      输入 filter_var/正则校验; 上传校验类型与大小并重命名
 */
declare(strict_types=1);

namespace App\Core;

class Security
{
    /** 输出转义, 防 XSS */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }

    /** 生成/获取 CSRF 令牌 */
    public static function csrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['swt_csrf'])) {
            $_SESSION['swt_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['swt_csrf'];
    }

    /** 输出 CSRF 隐藏域 */
    public static function csrfField(): string
    {
        $token = self::csrfToken();
        return '<input type="hidden" name="swt_csrf" value="' . self::e($token) . '">';
    }

    /** 校验 CSRF (POST 时调用) */
    public static function validateCsrf(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $posted = $_POST['swt_csrf'] ?? '';
        return is_string($posted)
            && isset($_SESSION['swt_csrf'])
            && hash_equals((string) $_SESSION['swt_csrf'], $posted);
    }

    /** 中国大陆手机号校验 */
    public static function validPhone(string $v): bool
    {
        return (bool) preg_match('/^1[3-9]\d{9}$/', $v);
    }

    /** 邮箱校验 */
    public static function validEmail(string $v): bool
    {
        return (bool) filter_var($v, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 安全上传文件
     * @return string|null 成功返回相对 /assets/uploads 的文件名, 失败返回 null
     */
    public static function upload(array $file, array $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico'], int $maxBytes = 2097152): ?string
    {
        if (!isset($file['error']) || is_array($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        if ($file['size'] > $maxBytes) {
            return null;
        }
        $name = $file['name'] ?? '';
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            return null;
        }
        $dir = $GLOBALS['APP_CONFIG']['upload_dir'] ?? (APP_ROOT . '/uploads');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $newName = bin2hex(random_bytes(12)) . '.' . $ext;
        $dest = rtrim($dir, '/') . '/' . $newName;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }
        @chmod($dest, 0644);
        return $newName;
    }
}
