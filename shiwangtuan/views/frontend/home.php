<?php
/**
 * 视旺团官网首页 (服务端渲染, 全部内容来自后台配置) —— v3 图文生动版
 */
declare(strict_types=1);

use App\Core\Security;

$e = static fn($v) => Security::e($v);
$d = $data;                       // 内容聚合
$settings = $settings;           // 站点设置
require VIEW_ROOT . '/frontend/partials/icons.php';

$ic = static fn($name) => swt_icon((string) $name);
$sec = static fn($key, $field, $def = '') => swt_section($d['sections'], $key, $field, $def);
$siteName = $settings['site_name'] ?? '视旺团';
$slogan   = $settings['site_slogan'] ?? '';
$logo     = $settings['site_logo'] ?? '';
$favicon  = $settings['favicon'] ?? '';

/* 静态资源版本号 = CSS/JS 中最新的修改时间
   作用：文件一改动 URL 就变化，浏览器自动拉新版，彻底避免旧 CSS 缓存 */
if (!function_exists('swt_asset_ver')) {
    function swt_asset_ver(): string
    {
        $newest = 0;
        foreach (['assets/css/style.css', 'assets/css/mobile.css', 'assets/js/main.js'] as $rel) {
            $p = PUBLIC_ROOT . '/' . $rel;
            if (is_file($p)) {
                $t = (int) filemtime($p);
                if ($t > $newest) {
                    $newest = $t;
                }
            }
        }
        return $newest > 0 ? substr((string) $newest, -6) : '1';
    }
}
$assetV = swt_asset_ver();

// 工具: 多行文本转列表
$lines = static function (string $txt): array {
    return array_values(array_filter(array_map('trim', explode("\n", $txt)), static fn($x) => $x !== ''));
};

// 图片资源 (后台可替换为任意可访问地址; 默认使用内置原创配图)
$IMG = [
    'hero'    => '/uploads/hero-combo.webp',      // 首屏形象 (女孩+机器人，透明底抠图，按客户效果图制作)
    'hero820' => '/uploads/hero-combo-820.webp',  // 首屏形象小图 (手机端用，体积更小)
    'ai'      => '/uploads/svc-ai.jpg',
    'svc'   => ['/uploads/svc-video.jpg', '/uploads/svc-eco.jpg', '/uploads/svc-ai.jpg', '/uploads/svc-data.jpg'],
    'case'  => ['/uploads/case-cafe.jpg', '/uploads/case-gym.jpg', '/uploads/case-spa.jpg', '/uploads/case-hotel.jpg'],
];

$banner = $d['banners'][0] ?? null;
$heroTitle = $banner['title'] ?? '让视频号团购，为你的生意带来真实增长';
$heroSub   = $banner['subtitle'] ?? '视频号 POI 团购 × 微信生态拓客 × AI 技术落地，一站式代运营服务商';
$heroBtn   = $banner['button_text'] ?? '立即咨询';
$heroUrl   = $banner['button_url'] ?? '#contact';
$tagline   = $settings['hero_tagline'] ?? '微信本地生活 · 万亿流量新蓝海';
?>
<!DOCTYPE html>
<html lang="zh-CN" class="no-js">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $e($title); ?></title>
<meta name="description" content="<?php echo $e($desc); ?>">
<meta name="keywords" content="<?php echo $e($keywords); ?>">
<?php
$defaultFavicon = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect x='2' y='2' width='28' height='28' rx='8' fill='%2307C160'/%3E%3Cpath d='M12 10v12l10-6z' fill='%23fff'/%3E%3C/svg%3E";
?>
<link rel="icon" href="<?php echo $e($favicon ?: $defaultFavicon); ?>">
<link rel="apple-touch-icon" href="<?php echo $e($favicon ?: $defaultFavicon); ?>">
<!-- 社交分享 SEO -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo $e($title); ?>">
<meta property="og:description" content="<?php echo $e($desc); ?>">
<?php if ($logo): ?><meta property="og:image" content="<?php echo $e($logo); ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com">
<script>document.documentElement.className=document.documentElement.className.replace(/\bno-js\b/,'js');</script>
<link rel="preload" as="image" href="<?php echo $e($IMG['hero']); ?>"
      imagesrcset="<?php echo $e($IMG['hero820']); ?> 820w, <?php echo $e($IMG['hero']); ?> 1454w"
      imagesizes="(max-width:768px) 86vw, 486px">
<link rel="stylesheet" href="/assets/css/style.css?v=<?php echo $assetV; ?>">
<!-- 移动端专属样式：文件内部已用 @media(max-width:768px) 自保护，桌面端不下载也不生效 -->
<link rel="stylesheet" href="/assets/css/mobile.css?v=<?php echo $assetV; ?>">
</head>
<body>

<!-- ============ 顶部导航 ============ -->
<header class="site-head" id="nav">
  <div class="container nav-inner">
    <a class="brand" href="#home">
      <?php if ($logo): ?><img class="brand-mark" src="<?php echo $e($logo); ?>" alt="<?php echo $e($siteName); ?>">
      <?php else: ?>
      <svg class="brand-mark" viewBox="0 0 32 32" aria-hidden="true">
        <rect x="2" y="2" width="28" height="28" rx="8" fill="#07C160"/>
        <path d="M12 10v12l10-6z" fill="#fff"/>
        <path d="M22 20a5 5 0 0 0 3-4c0-1.6-1-3-2.6-3.6" stroke="#FF8000" stroke-width="2.2" fill="none" stroke-linecap="round"/>
      </svg>
      <?php endif; ?>
      <span><?php echo $e($siteName); ?></span>
    </a>
    <nav class="nav-links" id="navLinks">
      <?php foreach ($d['nav'] as $n): ?>
        <a href="<?php echo $e($n['url']); ?>"><?php echo $e($n['title']); ?></a>
      <?php endforeach; ?>
      <a class="btn btn-green nav-cta" href="#contact">免费咨询</a>
    </nav>
    <button class="nav-toggle" id="navToggle" aria-label="菜单"><span></span><span></span><span></span></button>
  </div>
</header>

<!-- ============ 首屏 Hero ============ -->
<section class="hero" id="home">
  <div class="hero-mesh"></div>
  <div class="hero-orb orb-1"></div>
  <div class="hero-orb orb-2"></div>
  <div class="container hero-inner">
    <div class="hero-text">
      <span class="hero-tag"><span class="dot"></span><?php echo $e($tagline); ?></span>
      <h1><?php echo $e($heroTitle); ?><span class="hl"> ↗</span></h1>
      <p class="hero-sub"><?php echo $e($heroSub); ?></p>
      <div class="hero-mini">
        <div class="m"><span class="mi">🎬</span><div><b>视频号团购运营</b><span>从 0 到 1 快速起量</span></div></div>
        <div class="m"><span class="mi">💬</span><div><b>微信生态拓客</b><span>私域客户沉淀复购</span></div></div>
        <div class="m"><span class="mi">🤖</span><div><b>AI 技术落地</b><span>智能运营降本增效</span></div></div>
        <div class="m"><span class="mi">📈</span><div><b>数据驱动增长</b><span>看得见的结果</span></div></div>
      </div>
      <div class="hero-actions">
        <a class="btn btn-orange btn-lg" href="<?php echo $e($heroUrl); ?>"><?php echo $e($heroBtn); ?></a>
        <a class="btn btn-ghost btn-lg" href="#services">获取定制方案</a>
      </div>
      <div class="hero-chips">
        <div class="chip"><b>14亿<i>+</i></b><span>微信月活用户</span></div>
        <div class="chip"><b>220<i>%</i></b><span>本地生活年增速</span></div>
        <div class="chip"><b>0.6<i>%</i></b><span>超低支付手续费</span></div>
      </div>
    </div>

    <div class="hero-visual">
      <div class="hero-glowring"></div>
      <div class="hero-character">
        <img class="hero-img" src="<?php echo $e($IMG['hero']); ?>"
             srcset="<?php echo $e($IMG['hero820']); ?> 820w, <?php echo $e($IMG['hero']); ?> 1454w"
             sizes="(max-width:768px) 86vw, 486px"
             alt="视旺团 · 视频号团购与 AI 代运营" loading="eager" fetchpriority="high" width="1454" height="1824">
      </div>
      <div class="hero-kws">
        <span class="kw kw-1">内容种草</span>
        <span class="kw kw-2">团购转化</span>
        <span class="kw kw-3">私域运营</span>
        <span class="kw kw-4">AI 驱动增长</span>
      </div>
      <div class="hero-badge hb-1"><div class="em">📈</div><b>视频号团购</b><span>引爆本地生意</span></div>
      <div class="hero-badge hb-2"><div class="em">🤖</div><b>AI 智能运营</b><span>降本增效</span></div>
      <div class="hero-badge hb-3"><div class="em">💬</div><b>微信生态</b><span>全域增长</span></div>
    </div>
  </div>

  <!-- 平台资质墙 -->
  <div class="trust">
    <div class="container trust-inner">
      <span class="trust-label">多平台官方服务商</span>
      <span class="pf-badge"><span class="ic pf-wechat">微</span>微信 · 视频号</span>
      <span class="pf-badge"><span class="ic pf-douyin">抖</span>抖音本地生活</span>
      <span class="pf-badge"><span class="ic pf-xhs">红</span>小红书</span>
      <span class="pf-badge"><span class="ic pf-qw">企</span>企业微信</span>
      <span class="pf-badge"><span class="ic pf-red">快</span>快手本地生活</span>
    </div>
  </div>
</section>

<!-- ============ 四大服务 (图文卡) ============ -->
<section class="section" id="services">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-kicker">SERVICES</span>
      <h2><?php echo $e($sec('services', 'title', '四大服务 · 打通生意增长全链路')); ?></h2>
      <p><?php echo $e($sec('services', 'subtitle', '从内容到转化，从公域到私域，AI 赋能每一步')); ?></p>
    </div>
    <div class="svc-grid">
      <?php foreach (array_slice($d['services'], 0, 4) as $i => $s): ?>
      <div class="svc-card reveal d<?php echo ($i % 4) + 1; ?>">
        <div class="svc-media">
          <img src="<?php echo $e($IMG['svc'][$i % 4]); ?>" alt="<?php echo $e($s['title']); ?>" loading="lazy">
          <span class="svc-ic"><?php echo $ic($s['icon']); ?></span>
        </div>
        <div class="svc-body">
          <h3><?php echo $e($s['title']); ?></h3>
          <p><?php echo $e($s['description']); ?></p>
          <?php if (!empty($s['points'])): ?>
          <ul>
            <?php foreach (array_slice($lines($s['points']), 0, 4) as $p): ?><li><?php echo $e($p); ?></li><?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ 数字业绩带 ============ -->
<section class="section stats-sec" id="performance">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-kicker" style="background:rgba(255,255,255,.12);color:#fff">PERFORMANCE</span>
      <h2 style="color:#fff"><?php echo $e($sec('performance', 'title', '视旺团业绩展示')); ?></h2>
      <p style="color:#bcd0c7"><?php echo $e($sec('performance', 'subtitle', '用真实结果说话')); ?></p>
    </div>
    <div class="stats-grid">
      <?php foreach ($d['stats'] as $st): ?>
      <div class="stat reveal">
        <div class="ic"><?php echo $ic($st['icon'] ?? 'chart'); ?></div>
        <div class="stat-num" data-count="<?php echo $e($st['value']); ?>"><?php echo $e($st['value']); ?></div>
        <?php if (!empty($st['unit'])): ?><div class="stat-unit"><?php echo $e($st['unit']); ?></div><?php endif; ?>
        <div class="stat-label"><?php echo $e($st['label']); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ 精选案例 (实拍图文墙) ============ -->
<section class="section section-soft" id="cases">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-kicker">CASE STUDY</span>
      <h2>精选案例 · <span class="green">用结果说话</span></h2>
      <p>不止流量，更有真实的生意增长</p>
    </div>
    <div class="case-grid">
      <?php foreach (array_slice($d['cases'], 0, 4) as $i => $c): ?>
      <div class="case reveal d<?php echo ($i % 4) + 1; ?>">
        <div class="case-media">
          <img src="<?php echo $e(!empty($c['cover']) ? $c['cover'] : $IMG['case'][$i % 4]); ?>" alt="<?php echo $e($c['title']); ?>" loading="lazy">
        </div>
        <div class="case-body">
          <?php if (!empty($c['category'])): ?><span class="case-cat"><?php echo $e($c['category']); ?></span><?php endif; ?>
          <h3><?php echo $e($c['title']); ?></h3>
          <?php if (!empty($c['metrics'])): ?>
          <ul class="metrics">
            <?php foreach ($lines($c['metrics']) as $m): ?><li><?php echo $e($m); ?></li><?php endforeach; ?>
          </ul>
          <?php elseif (!empty($c['summary'])): ?>
          <p><?php echo $e($c['summary']); ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ AI 能力 ============ -->
<section class="section ai-sec" id="ai">
  <div class="container">
    <div class="ai-wrap">
      <div class="ai-photo reveal"><img src="<?php echo $e($IMG['ai']); ?>" alt="视旺团 AI 运营" loading="lazy"></div>
      <div class="ai-text">
        <div class="sec-head reveal" style="text-align:left;margin:0 0 28px">
          <span class="sec-kicker" style="background:rgba(62,240,166,.16);color:#3ef0a6">AI POWERED</span>
          <h2 style="color:#fff"><?php echo $e($sec('ai', 'title', 'AI 能力 · 让运营更轻')); ?></h2>
          <p style="color:#b9ccc3;margin-top:12px"><?php echo $e($sec('ai', 'subtitle', 'AI 深度融入本地生活经营，全链路智能化')); ?></p>
        </div>
        <div class="ai-grid">
          <?php foreach ($d['ai'] as $a): ?>
          <div class="ai-card reveal">
            <div class="ai-ic"><?php echo $ic($a['icon']); ?></div>
            <h3><?php echo $e($a['title']); ?></h3>
            <p><?php echo $e($a['description']); ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ 核心优势 ============ -->
<section class="section" id="advantages">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-kicker">WHY US</span>
      <h2>为什么选择 <span class="green">视旺团</span></h2>
      <p>区别于传统代运营，真正为商家降本增效</p>
    </div>
    <div class="adv-grid">
      <?php foreach ($d['advantages'] as $a): ?>
      <div class="adv-card reveal">
        <div class="adv-ic"><?php echo $ic($a['icon']); ?></div>
        <h3><?php echo $e($a['title']); ?></h3>
        <p><?php echo $e($a['description']); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ 服务流程 ============ -->
<section class="section section-soft" id="process">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-kicker">PROCESS</span>
      <h2><?php echo $e($sec('process', 'title', '标准化服务流程')); ?></h2>
      <p><?php echo $e($sec('process', 'subtitle', '专人对接，快速上线开卖')); ?></p>
    </div>
    <div class="steps">
      <?php foreach ($d['process'] as $i => $pr): ?>
      <div class="step reveal d<?php echo ($i % 4) + 1; ?>">
        <div class="sn"><?php echo (int) ($pr['step'] ?? ($i + 1)); ?></div>
        <div class="sx">STEP <?php echo (int) ($pr['step'] ?? ($i + 1)); ?></div>
        <h3><?php echo $e($pr['title']); ?></h3>
        <p><?php echo $e($pr['description']); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ 全行业方案 ============ -->
<section class="section" id="industry">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-kicker">INDUSTRY</span>
      <h2><?php echo $e($sec('industry', 'title', '全行业均可入驻')); ?></h2>
      <p><?php echo $e($sec('industry', 'subtitle', '不限类目、不限门店类型')); ?></p>
    </div>
    <div class="sol-grid">
      <?php foreach ($d['industries'] as $it): ?>
      <div class="sol reveal">
        <div class="top">
          <div class="ic"><?php echo $ic($it['icon']); ?></div>
          <h3><?php echo $e($it['name']); ?></h3>
        </div>
        <div class="pain">常见痛点：<b><?php echo $e($it['description'] ?: '客流少、复购低、线上不会做'); ?></b></div>
        <div class="fix">从入驻到代运营一站式交付</div>
        <div class="fix">AI + 同城流量，低成本精准拓客</div>
        <div class="fix">数据回流，每一分投入看得见</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ 套餐方案 ============ -->
<section class="section section-soft" id="plans">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-kicker">PLANS</span>
      <h2><?php echo $e($sec('plans', 'title', '套餐方案')); ?></h2>
      <p><?php echo $e($sec('plans', 'subtitle', '按需选择，灵活合作')); ?></p>
    </div>
    <div class="plans">
      <?php foreach ($d['plans'] as $pl): ?>
      <div class="plan reveal <?php echo !empty($pl['is_hot']) ? 'hot' : ''; ?>">
        <?php if (!empty($pl['is_hot'])): ?><div class="plan-flag">热门推荐</div><?php endif; ?>
        <h3><?php echo $e($pl['name']); ?></h3>
        <div class="desc">适合不同阶段的门店与连锁品牌</div>
        <div class="price"><b><?php echo $e($pl['price']); ?></b><?php if (!empty($pl['original_price'])): ?><s>¥<?php echo $e($pl['original_price']); ?></s><?php endif; ?><i><?php echo $e($pl['unit'] ?? ''); ?></i></div>
        <ul class="plan-feat">
          <?php foreach ($lines($pl['features'] ?? '') as $f): ?><li><?php echo $e($f); ?></li><?php endforeach; ?>
        </ul>
        <a class="btn btn-green btn-block" href="#contact">立即咨询</a>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="plans-note">最终合作方案与报价以顾问一对一沟通确认为准，支持按门店规模与经营阶段定制。</p>
  </div>
</section>

<!-- ============ 荣誉资质墙 + 合作伙伴 ============ -->
<section class="section honor-sec" id="about">
  <div class="container">
    <div class="sec-head reveal">
      <span class="sec-kicker">CREDENTIALS</span>
      <h2>资质荣誉 · 值得信赖</h2>
      <p>多平台官方认证与行业认可，为您的合作保驾护航</p>
    </div>
    <div class="honor-grid">
      <div class="honor reveal"><div class="em">🏅</div><b>视频号本地生活</b><span>官方服务商认证</span></div>
      <div class="honor reveal d1"><div class="em">🎖️</div><b>抖音本地生活</b><span>代运营服务商</span></div>
      <div class="honor reveal d2"><div class="em">🔐</div><b>企业微信</b><span>SCRM 合作生态</span></div>
      <div class="honor reveal d3"><div class="em">⭐</div><b>行业口碑</b><span>服务 2000+ 实体门店</span></div>
    </div>
    <?php if (!empty($d['partners'])): ?>
    <div class="marquee reveal">
      <div class="marquee-track">
        <?php foreach (array_merge($d['partners'], $d['partners']) as $p): ?>
        <span class="m-logo"><?php echo $e($p['name']); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ============ 大 CTA ============ -->
<section class="cta-sec">
  <div class="container">
    <div class="cta-box reveal">
      <h2>现在开始，让 AI 和视频号<br>为你的生意创造更大可能</h2>
      <p>留下手机号，专属顾问将尽快联系你，提供视频号 POI 团购方案与免费诊断。</p>
      <form class="cta-form" id="ctaForm">
        <input name="phone" type="tel" inputmode="numeric" placeholder="请输入您的手机号" required>
        <button class="btn btn-orange" type="submit">免费获取方案</button>
      </form>
      <div class="cta-chips">
        <span>1V1 专属顾问</span><span>行业定制方案</span><span>快速启动落地</span>
      </div>
      <p class="form-msg" id="ctaMsg"></p>
    </div>
  </div>
</section>

<!-- ============ 联系我们 ============ -->
<section class="section contact-sec" id="contact">
  <div class="container contact-grid">

    <!-- 左栏：联系信息 -->
    <div class="contact-info">
      <span class="sec-kicker">CONTACT</span>
      <h2 class="ct-h2">联系我们 · <em>免费评估</em></h2>
      <p class="contact-lead">留下门店信息，专属顾问将在 <b>1 个工作日</b>内联系您，提供视频号 POI 团购方案与报价。</p>

      <div class="info-card">
        <ul class="info-list">
          <?php
          $mPhone = trim((string) ($settings['contact_phone'] ?? ''));
          $mWechat = trim((string) ($settings['contact_wechat'] ?? ''));
          $mEmail = trim((string) ($settings['contact_email'] ?? ''));
          $mAddr = trim((string) ($settings['contact_address'] ?? ''));
          $mTel = $mPhone !== '' ? preg_replace('/[^0-9+\-]/', '', $mPhone) : '';
          ?>
          <?php if ($mTel !== ''): ?>
          <li>
            <a href="tel:<?php echo $e($mTel); ?>" class="info-hit" aria-label="拨打咨询热线">
              <span class="ic ic-phone" aria-hidden="true">📞</span>
              <span class="tx">
                <b>咨询热线</b>
                <strong class="hl-num"><?php echo $e($mPhone); ?></strong>
              </span>
              <span class="go" aria-hidden="true">拨打</span>
            </a>
          </li>
          <?php endif; ?>
          <?php if ($mWechat !== ''): ?>
          <li>
            <div class="info-hit">
              <span class="ic ic-wechat" aria-hidden="true">💬</span>
              <span class="tx">
                <b>微信咨询</b>
                <strong><?php echo $e($mWechat); ?></strong>
              </span>
              <button class="copy-btn" type="button" data-copy="<?php echo $e($mWechat); ?>" aria-label="复制微信号">复制</button>
            </div>
          </li>
          <?php endif; ?>
          <?php if ($mEmail !== ''): ?>
          <li>
            <a href="mailto:<?php echo $e($mEmail); ?>" class="info-hit" aria-label="发送邮件到客服邮箱">
              <span class="ic ic-mail" aria-hidden="true">✉️</span>
              <span class="tx">
                <b>客服邮箱</b>
                <strong><?php echo $e($mEmail); ?></strong>
              </span>
              <span class="go" aria-hidden="true">写信</span>
            </a>
          </li>
          <?php endif; ?>
          <?php if ($mAddr !== ''): ?>
          <li class="last">
            <div class="info-hit">
              <span class="ic ic-pin" aria-hidden="true">📍</span>
              <span class="tx">
                <b>公司地址</b>
                <strong><?php echo $e($mAddr); ?></strong>
              </span>
            </div>
          </li>
          <?php endif; ?>
        </ul>

        <!-- 顾问二维码 -->
        <div class="qr-box">
          <div class="qr<?php echo empty($settings['consultant_qr']) ? ' qr-empty' : ''; ?>">
            <?php if (!empty($settings['consultant_qr'])): ?>
            <img src="<?php echo $e($settings['consultant_qr']); ?>" alt="商务顾问微信二维码" loading="lazy" width="132" height="132">
            <?php else: ?>
            <div class="qr-ph" aria-hidden="true">
              <div class="qr-grid"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
              <b>扫码添加顾问</b>
            </div>
            <?php endif; ?>
          </div>
          <div class="qr-tx">
            <b>添加商务顾问</b>
            <span>获取 1 对 1 视频号团购诊断与报价</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 右栏：留资表单 -->
    <div class="contact-panel">
      <form class="contact-form" id="contactForm" novalidate>
        <div class="cf-head">
          <h3>免费获取方案与报价</h3>
          <p>填写信息，专属顾问 1 对 1 与你对接</p>
        </div>

        <div class="frow">
          <div class="field">
            <label for="cfName">您的称呼 <i>*</i></label>
            <input id="cfName" name="name" type="text" placeholder="怎么称呼您" required autocomplete="name">
          </div>
          <div class="field">
            <label for="cfPhone">手机号 <i>*</i></label>
            <input id="cfPhone" name="phone" type="tel" inputmode="numeric" placeholder="11 位手机号" required autocomplete="tel">
          </div>
        </div>

        <div class="frow">
          <div class="field">
            <label for="cfCompany">门店 / 公司</label>
            <input id="cfCompany" name="company" type="text" placeholder="选填，便于准备方案" autocomplete="organization">
          </div>
          <div class="field">
            <label for="cfCity">所在城市</label>
            <input id="cfCity" name="city" type="text" placeholder="选填" autocomplete="address-level2">
          </div>
        </div>

        <div class="field">
          <label for="cfContent">咨询内容 <i>*</i></label>
          <textarea id="cfContent" name="content" rows="4" placeholder="简单说下行业和现状，例如：餐饮门店，想做视频号团购" required></textarea>
        </div>

        <button class="btn btn-orange btn-block cf-submit" type="submit">
          <span class="lb">提交咨询 · 免费获取方案</span>
          <span class="ld" aria-hidden="true">提交中…</span>
        </button>

        <p class="form-msg" id="formMsg" role="status" aria-live="polite"></p>

        <p class="cf-note">
          我们承诺：<b>信息仅用于顾问与你联系</b>，不会对外泄露或用于其他用途。
        </p>
      </form>
    </div>

  </div>
</section>

<!-- ============ 页脚 ============ -->
<footer class="footer">
  <div class="container footer-inner">
    <div class="f-brand">
      <div class="brand"><?php if ($logo): ?><img class="brand-mark" src="<?php echo $e($logo); ?>" alt="<?php echo $e($siteName); ?>">
      <?php else: ?><svg class="brand-mark" viewBox="0 0 32 32"><rect x="2" y="2" width="28" height="28" rx="8" fill="#07C160"/><path d="M12 10v12l10-6z" fill="#fff"/></svg>
      <?php endif; ?><span><?php echo $e($siteName); ?></span></div>
      <p><?php echo $e($slogan); ?></p>
    </div>
    <div class="f-nav">
      <b>快速导航</b>
      <?php foreach ($d['nav'] as $n): ?><a href="<?php echo $e($n['url']); ?>"><?php echo $e($n['title']); ?></a><?php endforeach; ?>
    </div>
    <div class="f-contact">
      <b>联系方式</b>
      <p>电话: <?php echo $e($settings['contact_phone'] ?? ''); ?></p>
      <p>微信: <?php echo $e($settings['contact_wechat'] ?? ''); ?></p>
      <p>邮箱: <?php echo $e($settings['contact_email'] ?? ''); ?></p>
    </div>
    <div class="f-partners">
      <b>合作伙伴</b>
      <div class="p-logos">
        <?php foreach ($d['partners'] as $p): ?><span><?php echo $e($p['name']); ?></span><?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="footer-bar">
    <div class="container">
      <span><?php echo $e($settings['copyright'] ?? ''); ?></span>
      <span><?php echo $e($settings['icp'] ?? ''); ?></span>
    </div>
  </div>
</footer>

<!-- 移动端底部固定操作条(仅手机显示) -->
<?php $mPhone = trim((string)($settings['contact_phone'] ?? '')); ?>
<div class="m-bar" id="mBar">
  <?php if ($mPhone !== ''): ?>
  <a class="m-tel" href="tel:<?php echo $e(preg_replace('/[^0-9+\-]/', '', $mPhone)); ?>">
    <span class="em">📞</span>电话咨询
  </a>
  <?php endif; ?>
  <a class="m-sub" href="#ctaForm">免费获取方案</a>
</div>

<script src="/assets/js/main.js?v=<?php echo $assetV; ?>"></script>
</body>
</html>
