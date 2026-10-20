<?php
/**
 * 视旺团官网 · 浏览器一键安装向导
 * 用法: 将项目上传到服务器后, 访问 https://你的域名/install.php
 *  - 自动建库建表 + 导入示例内容
 *  - 创建/重置后台管理员
 *  - 安装完成后请删除本文件或设置访问保护
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', APP_ROOT . '/public');

$step   = 'form';
$errors = [];
$ok     = '';
$lock   = APP_ROOT . '/.installed';

// 已安装保护
if (file_exists($lock) && !isset($_GET['force'])) {
    $step = 'installed';
}

$defaultCfg = [
    'host'    => '127.0.0.1',
    'port'    => 3306,
    'dbname'  => 'swtsite2026',
    'user'    => 'swtsite2026',
    'pass'    => '数据库密码',
];

// 读取现有配置做默认值
$cfgPath = APP_ROOT . '/config/database.php';
if (is_file($cfgPath)) {
    $existing = require $cfgPath;
    if (is_array($existing)) {
        $defaultCfg = array_merge($defaultCfg, $existing);
    }
}

function envCheck(array &$errors): void
{
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        $errors[] = 'PHP 版本需 >= 8.0, 当前为 ' . PHP_VERSION;
    }
    if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
        $errors[] = '缺少 PDO / pdo_mysql 扩展';
    }
    if (!is_writable(APP_ROOT . '/config')) {
        $errors[] = 'config 目录不可写, 请检查权限 (755/644)';
    }
    if (!is_file(APP_ROOT . '/sql/install.sql')) {
        $errors[] = '缺少 sql/install.sql';
    }
}

function runInstall(array $c, string $adminUser, string $adminPass, array &$errors): bool
{
    try {
        $dsn = "mysql:host={$c['host']};port={$c['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        // 确保数据库存在
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$c['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        $pdo->exec("USE `{$c['dbname']}`");

        $sql = file_get_contents(APP_ROOT . '/sql/install.sql');
        $sql = preg_replace('/^--.*$/m', '', $sql); // 去除注释行, 避免与后续语句粘连被跳过
        $sql .= "\n";
        $statements = preg_split('/;\s*\n/', $sql, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($statements as $stmt) {
            $s = trim($stmt);
            if ($s === '') {
                continue;
            }
            $pdo->exec($s);
        }

        // 创建/更新管理员
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $chk = $pdo->prepare("SELECT id FROM swt_admin WHERE username = ? LIMIT 1");
        $chk->execute([$adminUser]);
        if ($chk->fetch()) {
            $pdo->prepare("UPDATE swt_admin SET password = ? WHERE username = ?")
                ->execute([$hash, $adminUser]);
        } else {
            $pdo->prepare("INSERT INTO swt_admin (username, password, nickname, role) VALUES (?, ?, ?, ?)")
                ->execute([$adminUser, $hash, '超级管理员', 'admin']);
        }

        // 写回配置 (允许自定义 host/db)
        $cfg = "<?php\n/** 数据库配置 (由安装程序生成) */\ndeclare(strict_types=1);\n\nreturn [\n"
            . "    'host'     => " . var_export($c['host'], true) . ",\n"
            . "    'port'     => " . (int) $c['port'] . ",\n"
            . "    'dbname'   => " . var_export($c['dbname'], true) . ",\n"
            . "    'user'     => " . var_export($c['user'], true) . ",\n"
            . "    'pass'     => " . var_export($c['pass'], true) . ",\n"
            . "    'charset'  => 'utf8mb4',\n"
            . "];\n";
        file_put_contents(APP_ROOT . '/config/database.php', $cfg);

        // 安装锁
        file_put_contents(APP_ROOT . '/.installed', date('Y-m-d H:i:s'));
        return true;
    } catch (Throwable $e) {
        $errors[] = '安装失败: ' . $e->getMessage();
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    envCheck($errors);
    $c = [
        'host'    => trim((string) ($_POST['host'] ?? $defaultCfg['host'])),
        'port'    => (int) ($_POST['port'] ?? $defaultCfg['port']),
        'dbname'  => trim((string) ($_POST['dbname'] ?? $defaultCfg['dbname'])),
        'user'    => trim((string) ($_POST['user'] ?? $defaultCfg['user'])),
        'pass'    => (string) ($_POST['pass'] ?? $defaultCfg['pass']),
    ];
    $adminUser = trim((string) ($_POST['admin_user'] ?? 'admin'));
    $adminPass = (string) ($_POST['admin_pass'] ?? '');
    if ($adminUser === '') {
        $errors[] = '请填写管理员账号';
    }
    if (strlen($adminPass) < 6) {
        $errors[] = '管理员密码至少 6 位';
    }
    if (empty($errors)) {
        if (runInstall($c, $adminUser, $adminPass, $errors)) {
            $step = 'done';
        }
    }
} else {
    envCheck($errors);
}

$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>视旺团官网 · 安装向导</title>
<style>
  :root{--green:#07C160;--green-d:#06a050;--orange:#FF8000;}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:-apple-system,"PingFang SC","Microsoft YaHei",sans-serif;background:#f4f8f5;color:#1a1a1a;padding:24px}
  .wrap{max-width:560px;margin:40px auto;background:#fff;border-radius:14px;box-shadow:0 8px 30px rgba(7,193,96,.12);overflow:hidden}
  .hd{background:linear-gradient(135deg,var(--green),var(--green-d));color:#fff;padding:26px 28px}
  .hd h1{font-size:20px} .hd p{opacity:.9;margin-top:6px;font-size:13px}
  .bd{padding:24px 28px}
  .err{background:#fff3f3;border:1px solid #ffd0d0;color:#c0392b;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px}
  .ok{background:#f0fbf4;border:1px solid #b6e8c8;color:#067a43;padding:14px 16px;border-radius:8px;margin-bottom:14px;font-size:14px}
  label{display:block;font-size:13px;color:#555;margin:12px 0 5px;font-weight:600}
  input{width:100%;padding:10px 12px;border:1px solid #d9e3dd;border-radius:8px;font-size:14px;outline:none}
  input:focus{border-color:var(--green)}
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  button{margin-top:20px;width:100%;padding:12px;border:0;border-radius:9px;background:var(--green);color:#fff;font-size:15px;font-weight:700;cursor:pointer}
  button:hover{background:var(--green-d)}
  .tip{font-size:12px;color:#888;margin-top:6px;line-height:1.6}
  a{color:var(--green);text-decoration:none}
  .links{display:flex;gap:12px;margin-top:18px}
  .links a{flex:1;text-align:center;padding:11px;border:1px solid var(--green);border-radius:9px;font-weight:600}
</style>
</head>
<body>
<div class="wrap">
  <div class="hd">
    <h1>视旺团官网 · 安装向导</h1>
    <p>视频号POI团购一站式代运营陪跑 · 内容可后台灵活配置</p>
  </div>
  <div class="bd">
    <?php if ($step === 'installed'): ?>
      <div class="ok">系统已安装 (<?php echo htmlspecialchars(file_get_contents($lock), ENT_QUOTES, 'UTF-8'); ?>)。如需重新安装, 请
        <a href="?force=1">强制重装</a> (将清空现有内容表)。</div>
      <div class="links">
        <a href="index.php">访问官网</a>
        <a href="admin.php">进入后台</a>
      </div>
    <?php elseif ($step === 'done'): ?>
      <div class="ok">🎉 安装成功！数据库与示例内容已就绪, 管理员已创建。</div>
      <div class="err">安全提示: 请立即删除 <b>public/install.php</b> 或设置访问保护, 避免被他人重装。</div>
      <div class="links">
        <a href="index.php">访问官网</a>
        <a href="admin.php">进入后台</a>
      </div>
    <?php else: ?>
      <?php if (!empty($errors)): ?>
        <div class="err"><?php echo implode('<br>', array_map(fn($e)=>htmlspecialchars($e,ENT_QUOTES,'UTF-8'),$errors)); ?></div>
      <?php endif; ?>
      <form method="post">
        <label>数据库主机</label>
        <input name="host" value="<?php echo htmlspecialchars($defaultCfg['host'], ENT_QUOTES, 'UTF-8'); ?>" required>
        <div class="grid">
          <div><label>端口</label><input name="port" value="<?php echo (int)$defaultCfg['port']; ?>" required></div>
          <div><label>数据库名</label><input name="dbname" value="<?php echo htmlspecialchars($defaultCfg['dbname'], ENT_QUOTES, 'UTF-8'); ?>" required></div>
        </div>
        <div class="grid">
          <div><label>数据库用户</label><input name="user" value="<?php echo htmlspecialchars($defaultCfg['user'], ENT_QUOTES, 'UTF-8'); ?>" required></div>
          <div><label>数据库密码</label><input name="pass" value="<?php echo htmlspecialchars($defaultCfg['pass'], ENT_QUOTES, 'UTF-8'); ?>"></div>
        </div>
        <label>后台管理员账号</label>
        <input name="admin_user" value="admin" required>
        <label>后台管理员密码</label>
        <input name="admin_pass" type="text" placeholder="至少6位" required>
        <p class="tip">默认数据库信息已按服务器预置填写, 如无误直接设置管理员密码即可。<br>点击安装将自动建库、建表并导入示例内容 (业绩数据为示例展示值, 可在后台修改)。</p>
        <button type="submit">开始安装</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
