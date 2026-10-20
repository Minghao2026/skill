<?php
/**
 * 前端首页控制器 (服务端渲染)
 */
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Content;

class HomeController
{
    public static function index(): void
    {
        $data = Content::siteBundle();
        $settings = $data['settings'];
        $title = ($settings['site_name'] ?? '视旺团')
            . ' | ' . ($settings['site_slogan'] ?? '视频号POI团购一站式代运营陪跑');
        $desc = $settings['site_description'] ?? '';
        $keywords = $settings['site_keywords'] ?? '';

        header('Content-Type: text/html; charset=utf-8');
        View::render('frontend/home', [
            'data'     => $data,
            'settings' => $settings,
            'title'    => $title,
            'desc'     => $desc,
            'keywords' => $keywords,
        ]);
    }
}
