<?php
/**
 * 前端入口 (Web 根目录)
 * 部署时请将站点根目录指向本 public/ 目录
 */
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

App\Controllers\HomeController::index();
