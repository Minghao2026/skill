<?php
/**
 * 统一 JSON 响应
 * 返回格式: {"code":0,"msg":"","data":{}}
 */
declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function json(int $code, string $msg = '', mixed $data = null): never
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
        }
        echo json_encode([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, string $msg = 'ok'): never
    {
        self::json(0, $msg, $data);
    }

    public static function fail(string $msg = '请求失败', int $code = 1, mixed $data = null): never
    {
        self::json($code, $msg, $data);
    }
}
