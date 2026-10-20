<?php
/**
 * 后台管理控制器: 登录页 / 仪表盘外壳
 * 实际内容增删改通过 api.php 完成 (fetch JSON)
 */
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Content;

class AdminController
{
    public static function handle(): void
    {
        $action = $_GET['action'] ?? '';
        if ($action === 'logout') {
            Auth::logout();
            header('Location: admin.php');
            exit;
        }
        if (!Auth::check()) {
            self::loginPage();
            return;
        }
        self::dashboard();
    }

    private static function loginPage(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        View::render('admin/login', [
            'settings' => Content::settings(),
        ]);
    }

    private static function dashboard(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        View::render('admin/dashboard', [
            'user'     => Auth::user(),
            'settings' => Content::settings(),
        ]);
    }
}
