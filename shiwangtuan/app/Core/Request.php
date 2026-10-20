<?php
/**
 * 请求输入封装: 过滤 GET/POST, 统一取值
 */
declare(strict_types=1);

namespace App\Core;

class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    public static function isGet(): bool
    {
        return self::method() === 'GET';
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public static function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * 取 POST 中的字符串并去除首尾空白
     */
    public static function postString(string $key, string $default = ''): string
    {
        $v = $_POST[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    }

    public static function postInt(string $key, int $default = 0): int
    {
        return (int) ($_POST[$key] ?? $default);
    }

    public static function rawBody(): array
    {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($ct, 'application/json') !== false) {
            $body = json_decode((string) file_get_contents('php://input'), true);
            return is_array($body) ? $body : [];
        }
        return $_POST;
    }
}
