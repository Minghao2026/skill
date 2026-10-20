<?php
/**
 * JSON API 入口
 */
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

App\Controllers\ApiController::handle();
