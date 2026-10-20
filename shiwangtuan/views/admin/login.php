<?php
/**
 * 后台登录页
 */
declare(strict_types=1);
$csrf = App\Core\Security::csrfField();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>视旺团后台 · 登录</title>
<style>
  :root{--green:#07C160;--green-d:#06a050;--orange:#FF8000}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:-apple-system,"PingFang SC","Microsoft YaHei",sans-serif;background:linear-gradient(135deg,#06a050,#07C160 60%,#0bbf6e);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
  .box{background:#fff;border-radius:18px;box-shadow:0 20px 60px rgba(0,0,0,.25);width:100%;max-width:380px;padding:38px 32px;text-align:center}
  .logo{display:inline-flex;align-items:center;gap:8px;font-weight:800;font-size:22px;color:var(--green);margin-bottom:6px}
  .logo svg{width:34px;height:34px}
  .sub{color:#888;font-size:14px;margin-bottom:26px}
  label{display:block;text-align:left;font-size:13px;color:#555;margin:14px 0 6px;font-weight:600}
  input{width:100%;padding:12px 14px;border:1px solid #d9e3dd;border-radius:10px;font-size:15px;outline:none}
  input:focus{border-color:var(--green)}
  button{margin-top:24px;width:100%;padding:13px;border:0;border-radius:11px;background:var(--green);color:#fff;font-size:16px;font-weight:700;cursor:pointer}
  button:hover{background:var(--green-d)}
  .msg{margin-top:14px;font-size:13.5px;min-height:18px;color:#e0533d}
  .back{margin-top:18px;font-size:13px}
  .back a{color:var(--green)}
</style>
</head>
<body>
  <form class="box" id="loginForm">
    <div class="logo">
      <?php if (!empty($settings['site_logo'])): ?><img src="<?php echo htmlspecialchars($settings['site_logo'], ENT_QUOTES, 'UTF-8'); ?>" alt="logo" style="width:34px;height:34px;border-radius:8px;object-fit:contain">
      <?php else: ?>
      <svg viewBox="0 0 32 32"><rect x="2" y="2" width="28" height="28" rx="8" fill="#07C160"/><path d="M12 10v12l10-6z" fill="#fff"/></svg>
      <?php endif; ?>
      视旺团
    </div>
    <div class="sub">视频号POI团购 · 内容管理后台</div>
    <label>管理员账号</label>
    <input name="username" autocomplete="username" placeholder="admin" required>
    <label>密码</label>
    <input name="password" type="password" autocomplete="current-password" placeholder="请输入密码" required>
    <?php echo $csrf; ?>
    <button type="submit">登 录</button>
    <div class="msg" id="msg"></div>
    <div class="back"><a href="index.php">← 返回官网</a></div>
  </form>
<script>
document.getElementById('loginForm').addEventListener('submit', function(e){
  e.preventDefault();
  var fd=new FormData(this); fd.append('action','login');
  var msg=document.getElementById('msg'); msg.textContent='登录中...';
  fetch('/api.php',{method:'POST',body:fd}).then(r=>r.json()).then(res=>{
    if(res.code===0){ location.href='admin.php'; }
    else { msg.textContent=res.msg||'登录失败'; }
  }).catch(()=>{ msg.textContent='网络错误'; });
});
</script>
</body>
</html>
