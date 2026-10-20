<?php
/**
 * 后台管理员鉴权 (基于 Session)
 */
declare(strict_types=1);

namespace App\Core;

use App\Models\AdminModel;

class Auth
{
    private const SESSION_KEY = 'swt_admin';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($GLOBALS['APP_CONFIG']['session_name'] ?? 'swt_session');
            session_start();
        }
    }

    /** 校验账号密码, 成功写入会话 */
    public static function attempt(string $username, string $password): bool
    {
        $admin = AdminModel::findByUsername($username);
        if ($admin === null) {
            return false;
        }
        if (!password_verify($password, $admin['password'])) {
            return false;
        }
        self::start();
        $_SESSION[self::SESSION_KEY] = [
            'id'       => $admin['id'],
            'username' => $admin['username'],
            'nickname' => $admin['nickname'] ?? $admin['username'],
            'role'     => $admin['role'] ?? 'admin',
        ];
        return true;
    }

    public static function check(): bool
    {
        self::start();
        return isset($_SESSION[self::SESSION_KEY]['id']);
    }

    /** @return array{id:int,username:string,nickname:string,role:string}|null */
    public static function user(): ?array
    {
        self::start();
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function logout(): void
    {
        self::start();
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }

    /** 未登录则输出 JSON 失败并终止 */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            Response::fail('未登录或登录已过期', 401);
        }
    }
}
