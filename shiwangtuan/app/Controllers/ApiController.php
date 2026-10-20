<?php
/**
 * API 控制器: 公开留言 + 后台内容 CRUD (统一 JSON 响应)
 */
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Models\Content;

class ApiController
{
    public static function handle(): void
    {
        $action = Request::get('action') ?? Request::post('action', '');
        match ($action) {
            'contact'   => self::contact(),
            'login'     => self::login(),
            'logout'    => self::logout(),
            'content'   => self::content(),
            'save'      => self::save(),
            'delete'    => self::delete(),
            'upload'    => self::upload(),
            'overview'  => self::overview(),
            default     => Response::fail('未知操作', 404),
        };
    }

    /** 公开: 咨询留言提交 */
    private static function contact(): void
    {
        if (!Request::isPost()) {
            Response::fail('请求方式错误', 405);
        }
        $name    = Request::postString('name');
        $phone   = Request::postString('phone');
        $company = Request::postString('company');
        $city    = Request::postString('city');
        $content = Request::postString('content');

        if ($name === '' || $phone === '' || $content === '') {
            Response::fail('请填写姓名、手机号与咨询内容');
        }
        if (!Security::validPhone($phone)) {
            Response::fail('手机号格式不正确');
        }
        if (mb_strlen($content, 'UTF-8') > 1000) {
            Response::fail('咨询内容过长');
        }
        $id = Content::addMessage([
            'name'    => mb_substr($name, 0, 32, 'UTF-8'),
            'phone'   => mb_substr($phone, 0, 20, 'UTF-8'),
            'company' => mb_substr($company, 0, 128, 'UTF-8'),
            'city'    => mb_substr($city, 0, 64, 'UTF-8'),
            'content' => mb_substr($content, 0, 1000, 'UTF-8'),
            'source'  => '官网首页',
            'status'  => 0,
        ]);
        Response::success(['id' => $id], '提交成功，顾问将尽快与您联系');
    }

    /** 后台: 登录 */
    private static function login(): void
    {
        if (!Security::validateCsrf()) {
            Response::fail('表单已过期，请刷新重试', 1);
        }
        $username = Request::postString('username');
        $password = Request::postString('password');
        if ($username === '' || $password === '') {
            Response::fail('请输入账号和密码');
        }
        if (Auth::attempt($username, $password)) {
            Response::success([], '登录成功');
        }
        Response::fail('账号或密码错误', 1);
    }

    private static function logout(): void
    {
        Auth::logout();
        Response::success([], '已退出登录');
    }

    /** 后台: 取某类内容列表 */
    private static function content(): void
    {
        Auth::requireLogin();
        $type = Request::get('type', '');
        if (!in_array($type, Content::tables(), true)) {
            Response::fail('内容类型错误');
        }
        Response::success(Content::list($type, false));
    }

    /** 后台: 保存 (新建/更新) */
    private static function save(): void
    {
        Auth::requireLogin();
        if (!Security::validateCsrf()) {
            Response::fail('表单已过期，请刷新重试', 1);
        }
        $type = Request::post('type', '');
        if (!in_array($type, Content::tables(), true)) {
            Response::fail('内容类型错误');
        }
        $id = Request::postInt('id', 0) ?: null;

        $fields = [];
        foreach (Content::columns($type) as $col) {
            if (array_key_exists($col, $_POST)) {
                $v = $_POST[$col];
                $fields[$col] = is_string($v) ? trim($v) : $v;
            }
        }
        if (empty($fields)) {
            Response::fail('没有可保存的字段');
        }
        $newId = Content::save($type, $fields, $id);
        Response::success(['id' => $newId], '保存成功');
    }

    /** 后台: 软删除 */
    private static function delete(): void
    {
        Auth::requireLogin();
        if (!Security::validateCsrf()) {
            Response::fail('表单已过期，请刷新重试', 1);
        }
        $type = Request::post('type', '');
        $id   = Request::postInt('id', 0);
        if (!in_array($type, Content::tables(), true) || $id <= 0) {
            Response::fail('参数错误');
        }
        Content::remove($type, $id);
        Response::success([], '已删除');
    }

    /** 后台: 图片上传 (返回可访问 URL) */
    private static function upload(): void
    {
        Auth::requireLogin();
        if (!Security::validateCsrf()) {
            Response::fail('表单已过期，请刷新重试', 1);
        }
        if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
            Response::fail('未收到文件');
        }
        $name = Security::upload($_FILES['file']);
        if ($name === null) {
            Response::fail('上传失败，请检查文件类型(支持 jpg/png/gif/webp/ico)与大小(≤2MB)');
        }
        $base = $GLOBALS['APP_CONFIG']['upload_url'] ?? '/assets/uploads';
        $url  = rtrim($base, '/') . '/' . $name;
        Response::success(['url' => $url, 'name' => $name], '上传成功');
    }

    /** 后台: 概览 (仪表盘) */
    private static function overview(): void
    {
        Auth::requireLogin();
        Response::success([
            'counts'   => Content::counts(),
            'settings' => Content::settings(),
        ]);
    }
}
