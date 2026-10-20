<?php
/**
 * 后台仪表盘 (内容管理 SPA) —— v2 科技感控制台
 * 所有增删改通过 /api.php 完成
 */
declare(strict_types=1);
$csrf = App\Core\Security::csrfField();
$user = $user ?? [];
$nickname = $user['nickname'] ?? '管理员';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>视旺团后台 · 内容管理</title>
<style>
  :root{--green:#07C160;--green-d:#04a050;--green-l:#e9f8ef;--orange:#FF8000;--orange-l:#fff3e6;
    --ink:#1f2a24;--muted:#6b7682;--line:#e7ecea;--side:#0c1a13;--side-2:#10241a;--card:#fff}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:-apple-system,"PingFang SC","Microsoft YaHei",sans-serif;color:var(--ink);background:#eef3f1}
  a{text-decoration:none;color:inherit}
  /* 布局 */
  .app{display:grid;grid-template-columns:248px 1fr;min-height:100vh}
  .side{background:linear-gradient(180deg,var(--side),var(--side-2));color:#cfd8d2;padding:20px 14px;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow:auto}
  .side .logo{display:flex;align-items:center;gap:10px;color:#fff;font-weight:900;font-size:19px;margin-bottom:18px;padding:4px 8px}
  .side .logo img{width:32px;height:32px;border-radius:9px;object-fit:contain}
  .group-label{font-size:11.5px;letter-spacing:1px;color:#6f857a;font-weight:700;margin:16px 10px 6px}
  .group-label:first-of-type{margin-top:4px}
  .side a.item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;font-size:14px;margin-bottom:2px;color:#cfd8d2;transition:.15s;cursor:pointer}
  .side a.item .mi{font-size:16px;width:20px;text-align:center}
  .side a.item:hover{background:rgba(7,193,96,.16);color:#fff}
  .side a.item.active{background:var(--green);color:#fff;box-shadow:0 8px 18px rgba(7,193,96,.32)}
  .side .spacer{flex:1}
  .side .me{font-size:13px;color:#9fb0a7;border-top:1px solid rgba(255,255,255,.1);padding-top:14px;margin-top:8px;line-height:1.8}
  .side .me a{color:var(--orange);font-weight:700}
  /* 主区 */
  .main{display:flex;flex-direction:column;min-width:0}
  .topbar{background:#fff;border-bottom:1px solid var(--line);padding:16px 26px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:20}
  .topbar .title{font-size:19px;font-weight:900}
  .topbar .crumb{font-size:12.5px;color:var(--muted);margin-top:2px}
  .topbar .right{display:flex;align-items:center;gap:14px}
  .topbar .right a{font-size:13.5px;font-weight:600;color:var(--muted)}
  .topbar .right a:hover{color:var(--green)}
  .topbar .avatar{width:34px;height:34px;border-radius:50%;background:var(--green-l);color:var(--green-d);display:grid;place-items:center;font-weight:800}
  .content{padding:26px}
  .toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:16px;flex-wrap:wrap}
  .toolbar .desc{font-size:14px;color:var(--muted);max-width:680px}
  .btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:10px;font-weight:700;font-size:14px;border:0;cursor:pointer;transition:.2s}
  .btn-green{background:var(--green);color:#fff}
  .btn-green:hover{background:var(--green-d);transform:translateY(-1px)}
  .btn-ghost{background:#eef3f0;color:var(--ink)}
  .btn-orange{background:var(--orange);color:#fff}
  /* 表格 */
  table{width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 6px 22px rgba(11,20,16,.06)}
  th,td{text-align:left;padding:13px 18px;border-bottom:1px solid var(--line);font-size:14px}
  th{background:#f6faf8;color:#566;font-weight:800;font-size:12.5px;letter-spacing:.3px}
  tr:last-child td{border-bottom:0}
  tbody tr{transition:.15s}
  tbody tr:hover{background:var(--green-l)}
  .badge{display:inline-block;font-size:12px;padding:3px 10px;border-radius:999px;font-weight:700}
  .badge.on{background:var(--green-l);color:var(--green-d)} .badge.off{background:#f1f1f1;color:#999}
  .badge.hot{background:var(--orange-l);color:var(--orange)}
  .badge.pending{background:#fff3e6;color:var(--orange)}
  .row-actions button{background:none;border:0;color:var(--green-d);cursor:pointer;font-size:13px;margin-right:12px;font-weight:700}
  .row-actions button.del{color:#e0533d}
  /* 弹窗 */
  .mask{position:fixed;inset:0;background:rgba(8,16,12,.5);display:none;align-items:center;justify-content:center;padding:20px;z-index:50;backdrop-filter:blur(3px)}
  .mask.open{display:flex}
  .modal{background:#fff;border-radius:16px;width:100%;max-width:580px;max-height:90vh;overflow:auto;padding:26px;box-shadow:0 30px 80px rgba(0,0,0,.3)}
  .modal h3{margin-bottom:18px;font-size:19px;display:flex;align-items:center;gap:8px}
  .field{margin-bottom:15px}
  .field label{display:block;font-size:13px;color:#556;font-weight:700;margin-bottom:6px}
  .field input,.field textarea,.field select{width:100%;padding:11px 13px;border:1px solid var(--line);border-radius:10px;font-size:14px;font-family:inherit;outline:none;transition:.2s}
  .field input:focus,.field textarea:focus,.field select:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(7,193,96,.12)}
  .field textarea{resize:vertical;min-height:78px}
  .field .preview{margin-top:8px;font-size:22px}
  .modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:8px}
  .empty{padding:50px;text-align:center;color:var(--muted);background:#fff;border-radius:14px}
  .toast{position:fixed;top:20px;left:50%;transform:translateX(-50%) translateY(-20px);background:#0c1a13;color:#fff;padding:12px 22px;border-radius:11px;font-size:14px;opacity:0;transition:.3s;z-index:99;pointer-events:none;box-shadow:0 12px 30px rgba(0,0,0,.25)}
  .toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
  @media(max-width:860px){.app{grid-template-columns:1fr}.side{display:none}.content{padding:16px}}

  /* ====== 概览仪表盘 ====== */
  .ov-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px}
  .ov-card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:22px;box-shadow:0 6px 22px rgba(11,20,16,.05);position:relative;overflow:hidden}
  .ov-card::after{content:"";position:absolute;right:-20px;top:-20px;width:80px;height:80px;border-radius:50%;background:var(--green-l);opacity:.6}
  .ov-card .ic{font-size:26px;position:relative}
  .ov-card .num{font-size:34px;font-weight:900;margin:6px 0 2px;position:relative}
  .ov-card .lab{font-size:13.5px;color:var(--muted);position:relative}
  .ov-grid{display:grid;grid-template-columns:1.25fr 1fr;gap:16px;margin-bottom:22px}
  .ov-panel{background:#fff;border:1px solid var(--line);border-radius:16px;padding:22px;box-shadow:0 6px 22px rgba(11,20,16,.05)}
  .ov-panel h4{font-size:15px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
  .ov-panel h4 .tag{font-size:11px;font-weight:700;color:var(--green-d);background:var(--green-l);padding:2px 9px;border-radius:999px}
  .mbar{display:flex;align-items:center;gap:12px;margin-bottom:12px;font-size:13.5px}
  .mbar .nm{width:96px;color:#445;flex-shrink:0}
  .mbar .track{flex:1;height:9px;background:#eef3f0;border-radius:6px;overflow:hidden}
  .mbar .track i{display:block;height:100%;background:linear-gradient(90deg,#07C160,#0bbf6e);border-radius:6px;transition:width 1s ease}
  .mbar .ct{width:34px;text-align:right;font-weight:800;color:var(--green-d)}
  .omsg{display:flex;gap:10px;padding:11px 0;border-bottom:1px solid #f0f3f1;font-size:13.5px;cursor:pointer;transition:.15s}
  .omsg:last-child{border-bottom:0}
  .omsg:hover{background:var(--green-l);border-radius:8px;padding-left:8px}
  .omsg .av{width:34px;height:34px;border-radius:50%;background:var(--green-l);color:var(--green-d);display:grid;place-items:center;font-weight:800;flex-shrink:0}
  .omsg .body{flex:1;min-width:0}
  .omsg .body b{font-size:13.5px}
  .omsg .body p{color:var(--muted);font-size:12.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .omsg .st{font-size:11px;font-weight:700;flex-shrink:0}
  .ov-quick{background:#fff;border:1px solid var(--line);border-radius:16px;padding:22px;box-shadow:0 6px 22px rgba(11,20,16,.05)}
  .ov-quick h4{font-size:15px;margin-bottom:14px}
  .quick-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
  .quick-grid a{display:flex;align-items:center;gap:9px;padding:13px 14px;border:1px solid var(--line);border-radius:12px;font-size:14px;font-weight:600;transition:.2s}
  .quick-grid a:hover{border-color:var(--green);background:var(--green-l);transform:translateY(-2px)}
  .quick-grid a .mi{font-size:17px}
  @media(max-width:860px){.ov-cards,.ov-grid,.quick-grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="app">
  <aside class="side">
    <div class="logo">
      <?php if (!empty($settings['site_logo'])): ?><img src="<?php echo htmlspecialchars($settings['site_logo'], ENT_QUOTES, 'UTF-8'); ?>" alt="logo">
      <?php else: ?>
      <svg viewBox="0 0 32 32" width="32" height="32"><rect x="2" y="2" width="28" height="28" rx="8" fill="#07C160"/><path d="M12 10v12l10-6z" fill="#fff"/></svg>
      <?php endif; ?>
      视旺团后台
    </div>
    <nav id="menu"></nav>
    <div class="spacer"></div>
    <div class="me">
      👤 <?php echo htmlspecialchars($nickname, ENT_QUOTES, 'UTF-8'); ?><br>
      <a href="admin.php?action=logout">退出登录</a> · <a href="index.php" target="_blank">看官网 ↗</a>
    </div>
  </aside>

  <main class="main">
    <div class="topbar">
      <div>
        <div class="title" id="typeTitle">数据概览</div>
        <div class="crumb" id="typeDesc">官网运营数据总览</div>
      </div>
      <div class="right">
        <a href="index.php" target="_blank">🌐 访问官网</a>
        <div class="avatar"><?php echo mb_substr($nickname,0,1,'UTF-8'); ?></div>
      </div>
    </div>
    <div class="content">
      <div class="toolbar" id="toolbar">
        <span class="desc" id="typeDesc2"></span>
        <button class="btn btn-green" id="addBtn">+ 新增</button>
      </div>
      <div id="tableView"></div>
    </div>
  </main>
</div>

<!-- 编辑弹窗 -->
<div class="mask" id="mask">
  <div class="modal">
    <h3 id="modalTitle">编辑</h3>
    <form id="editForm"></form>
    <div class="modal-actions">
      <button class="btn btn-ghost" type="button" id="cancelBtn">取消</button>
      <button class="btn btn-green" type="button" id="saveBtn">保存</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>
<?php echo $csrf; ?>

<script>
const CSRF = document.querySelector('input[name="swt_csrf"]').value;
const ICONS = {rocket:'🚀',money:'💰',zero:'🆓',ai:'🤖',private:'🔒',handshake:'🤝',doc:'📝',build:'🛠️',run:'🏃',smart:'✨',food:'🍜',shop:'🛍️',beauty:'💇',fun:'🎮',life:'🔧',travel:'🎡',hotel:'🏨',tea:'🧋',map:'🗺️',check:'✅',heart:'❤️',pin:'📍',chat:'💬',group:'👥',box:'📦',video:'🎬',pen:'✍️',target:'🎯',chart:'📊',users:'👥',store:'🏪',star:'⭐'};

const TYPES = {
  swt_settings:{name:'站点设置',titleField:'setting_key',desc:'站点名称、口号、联系方式、备案号等全局配置',
    fields:[{k:'setting_key',l:'键名',t:'text',req:1},{k:'setting_value',l:'值',t:'textarea'},{k:'setting_group',l:'分组',t:'text'},{k:'sort',l:'排序',t:'number',def:0}]},
  swt_nav:{name:'导航菜单',titleField:'title',desc:'顶部导航的菜单项与跳转锚点',
    fields:[{k:'title',l:'标题',t:'text',req:1},{k:'url',l:'链接(#锚点/URL)',t:'text',req:1},{k:'target',l:'打开方式',t:'select',opts:['_self','_blank']},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_banner:{name:'首屏轮播',titleField:'title',desc:'首页首屏主标题与副标题（默认取第1条）',
    fields:[{k:'title',l:'主标题',t:'text',req:1},{k:'subtitle',l:'副标题',t:'textarea'},{k:'image',l:'背景图URL',t:'text'},{k:'button_text',l:'按钮文字',t:'text'},{k:'button_url',l:'按钮链接',t:'text'},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_section:{name:'板块标题',titleField:'section_key',desc:'各内容板块的标题/副标题/简介（按 section_key 区分）',
    fields:[{k:'section_key',l:'板块键(勿改)',t:'text',req:1},{k:'title',l:'标题',t:'text'},{k:'subtitle',l:'副标题',t:'text'},{k:'content',l:'简介',t:'textarea'},{k:'sort',l:'排序',t:'number'}]},
  swt_advantage:{name:'核心优势',titleField:'title',desc:'首页「为什么选择视旺团」卡片',
    fields:[{k:'icon',l:'图标名',t:'icon'},{k:'title',l:'标题',t:'text',req:1},{k:'description',l:'描述',t:'textarea'},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_service:{name:'一站式服务',titleField:'title',desc:'注册/搭建/运营陪跑/AI 四大服务模块',
    fields:[{k:'icon',l:'图标名',t:'icon'},{k:'title',l:'标题',t:'text',req:1},{k:'description',l:'描述',t:'textarea'},{k:'points',l:'要点(每行一条)',t:'textarea'},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_industry:{name:'行业覆盖',titleField:'name',desc:'可入驻的行业类目标签',
    fields:[{k:'icon',l:'图标名',t:'icon'},{k:'name',l:'名称',t:'text',req:1},{k:'description',l:'描述',t:'text'},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_stat:{name:'业绩数据',titleField:'label',desc:'首页业绩展示区的核心指标（数字可含 + / 万 等）',
    fields:[{k:'label',l:'指标名称',t:'text',req:1},{k:'value',l:'数值(如 1200 / 8600)',t:'text',req:1},{k:'unit',l:'单位(如 + 家)',t:'text'},{k:'icon',l:'图标名',t:'icon'},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_case:{name:'客户案例',titleField:'title',desc:'业绩展示区的商家案例（metrics 每行一个）',
    fields:[{k:'title',l:'案例标题',t:'text',req:1},{k:'category',l:'分类',t:'text'},{k:'cover',l:'封面图URL',t:'text'},{k:'summary',l:'摘要',t:'textarea'},{k:'metrics',l:'数据指标(每行一条)',t:'textarea'},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_ai:{name:'AI能力',titleField:'title',desc:'AI 探店 / 文案 / 拓客 / 复盘 等能力卡片',
    fields:[{k:'icon',l:'图标名',t:'icon'},{k:'title',l:'标题',t:'text',req:1},{k:'description',l:'描述',t:'textarea'},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_process:{name:'服务流程',titleField:'title',desc:'6 步开通流程',
    fields:[{k:'step',l:'步骤号',t:'number'},{k:'icon',l:'图标名',t:'icon'},{k:'title',l:'标题',t:'text',req:1},{k:'description',l:'描述',t:'textarea'},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_plan:{name:'套餐方案',titleField:'name',desc:'阶梯式合作套餐（价格支持示例展示）',
    fields:[{k:'name',l:'套餐名',t:'text',req:1},{k:'price',l:'价格',t:'text',req:1},{k:'original_price',l:'原价',t:'text'},{k:'unit',l:'单位(如 元/年)',t:'text'},{k:'features',l:'包含项(每行一条)',t:'textarea'},{k:'is_hot',l:'标记热门',t:'check'},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_partner:{name:'合作伙伴',titleField:'name',desc:'页脚合作伙伴/友情链接',
    fields:[{k:'name',l:'名称',t:'text',req:1},{k:'url',l:'链接',t:'text'},{k:'logo',l:'Logo图URL',t:'text'},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_team:{name:'团队成员',titleField:'name',desc:'关于我们板块的团队介绍',
    fields:[{k:'name',l:'名称',t:'text',req:1},{k:'title',l:'职位',t:'text'},{k:'avatar',l:'头像图URL',t:'text'},{k:'intro',l:'简介',t:'textarea'},{k:'sort',l:'排序',t:'number'},{k:'is_show',l:'显示',t:'check'}]},
  swt_message:{name:'咨询留言',titleField:'name',desc:'官网提交的咨询线索（可更新状态/删除）',
    fields:[{k:'name',l:'称呼',t:'text',ro:1},{k:'phone',l:'手机号',t:'text',ro:1},{k:'company',l:'公司',t:'text',ro:1},{k:'city',l:'城市',t:'text',ro:1},{k:'content',l:'内容',t:'textarea',ro:1},{k:'status',l:'状态',t:'select',opts:['0:未处理','1:已联系','2:已成交']}]}
};

/* 内部键名 → 中文名称 (用于列表“名称”列, 让管理人员看懂) */
const KEY_LABELS = {
  'site_name':'网站名称','site_slogan':'网站口号','site_description':'网站简介','site_keywords':'SEO关键词',
  'contact_phone':'联系电话','contact_wechat':'联系微信','contact_email':'联系邮箱','contact_address':'联系地址',
  'wechat_qr':'微信二维码','consultant_qr':'顾问二维码','icp':'备案号','copyright':'版权信息',
  'stats_note':'业绩备注','hero_tagline':'首屏标语','site_logo':'网站Logo','favicon':'浏览器图标',
  'what':'什么是视频号POI团购','why':'为什么选择视旺团','services':'一站式服务','industry':'行业覆盖',
  'performance':'业绩展示','ai':'AI能力','process':'服务流程','plans':'套餐方案','about':'关于视旺团'
};
function keyLabel(type,raw){
  if((type==='swt_settings'||type==='swt_section') && KEY_LABELS[raw]) return KEY_LABELS[raw];
  return raw;
}

/* 侧边栏分组 */
const GROUPS = [
  {label:'概览', items:[['overview','🏠','数据概览']]},
  {label:'站点与展示', items:[['swt_settings','⚙️','站点设置'],['swt_section','🧩','板块标题'],['swt_nav','🔗','导航菜单'],['swt_banner','🖼️','首屏轮播']]},
  {label:'服务与内容', items:[['swt_advantage','⭐','核心优势'],['swt_service','🛎️','一站式服务'],['swt_industry','🏬','行业覆盖'],['swt_stat','📊','业绩数据'],['swt_case','🏆','客户案例'],['swt_ai','🤖','AI能力'],['swt_process','🔄','服务流程'],['swt_plan','💎','套餐方案']]},
  {label:'团队与伙伴', items:[['swt_team','👥','团队成员'],['swt_partner','🤝','合作伙伴']]},
  {label:'客户线索', items:[['swt_message','💬','咨询留言']]},
];

let curType = 'overview';
let curList = [];

/* 渲染侧边栏 */
const menuEl = document.getElementById('menu');
(function buildMenu(){
  let html='';
  GROUPS.forEach(function(g){
    html+='<div class="group-label">'+g.label+'</div>';
    g.items.forEach(function(it){
      const key=it[0], icon=it[1];
      const label = key==='overview' ? '数据概览' : (TYPES[key] ? TYPES[key].name : it[2]);
      html+='<a class="item'+(key===curType?' active':'')+'" data-k="'+key+'" href="#"><span class="mi">'+icon+'</span>'+label+'</a>';
    });
    menuEl.innerHTML=html;
  });
  menuEl.querySelectorAll('a.item').forEach(function(a){
    a.onclick=function(e){e.preventDefault();selectType(a.dataset.k);};
  });
})();

function selectType(key){
  curType=key;
  document.querySelectorAll('.side a.item').forEach(function(a){a.classList.toggle('active',a.dataset.k===key);});
  const tb=document.getElementById('toolbar');
  if(key==='overview'){
    document.getElementById('typeTitle').textContent='数据概览';
    document.getElementById('typeDesc').textContent='官网运营数据总览';
    tb.style.display='none';
    renderOverview();
    return;
  }
  tb.style.display='flex';
  document.getElementById('typeTitle').textContent=TYPES[key].name;
  document.getElementById('typeDesc').textContent=TYPES[key].desc;
  document.getElementById('typeDesc2').textContent=TYPES[key].desc;
  loadList();
}

function toast(msg){
  const t=document.getElementById('toast'); t.textContent=msg; t.classList.add('show');
  setTimeout(function(){t.classList.remove('show');},1800);
}

function loadList(){
  fetch('/api.php?action=content&type='+curType).then(r=>r.json()).then(res=>{
    if(res.code!==0){toast(res.msg);return;}
    curList=res.data||[];
    renderTable();
  });
}

function renderTable(){
  const cfg=TYPES[curType];
  if(!curList.length){
    document.getElementById('tableView').innerHTML='<div class="empty">暂无数据，点击右上角「新增」添加</div>';
    return;
  }
  let html='<table><thead><tr><th>名称</th>';
  const hasShow = cfg.fields.some(f=>f.k==='is_show');
  const hasHot = cfg.fields.some(f=>f.k==='is_hot');
  if(hasShow) html+='<th>状态</th>';
  if(hasHot) html+='<th>标记</th>';
  if(curType==='swt_message') html+='<th>状态</th>';
  html+='<th>操作</th></tr></thead><tbody>';
  curList.forEach(function(row){
    html+='<tr><td>'+esc(keyLabel(curType,row[cfg.titleField]||''))+'</td>';
    if(hasShow) html+='<td><span class="badge '+(row.is_show?'on':'off')+'">'+(row.is_show?'显示':'隐藏')+'</span></td>';
    if(hasHot) html+='<td>'+(row.is_hot?'<span class="badge hot">热门</span>':'')+'</td>';
    if(curType==='swt_message'){const m=['未处理','已联系','已成交'];html+='<td><span class="badge '+(row.status==2?'on':(row.status==1?'hot':'pending'))+'">'+(m[row.status]||'未知')+'</span></td>';}
    html+='<td class="row-actions"><button class="edit" data-id="'+row.id+'">编辑</button>';
    if(curType!=='swt_settings') html+='<button class="del" data-id="'+row.id+'">删除</button>';
    html+='</td></tr>';
  });
  html+='</tbody></table>';
  document.getElementById('tableView').innerHTML=html;
  document.querySelectorAll('.edit').forEach(b=>b.onclick=()=>openModal(+b.dataset.id));
  document.querySelectorAll('.del').forEach(b=>b.onclick=()=>del(+b.dataset.id));
}

/* 概览仪表盘 */
function renderOverview(){
  fetch('/api.php?action=overview').then(r=>r.json()).then(res=>{
    if(res.code!==0){toast(res.msg);return;}
    const c=res.data.counts||{};
    fetch('/api.php?action=content&type=swt_message').then(r=>r.json()).then(mr=>{
      const msgs=mr.data||[];
      const pending=msgs.filter(m=>m.status==0).length;
      const maxMod=Math.max(1,c.swt_case||0,c.swt_plan||0,c.swt_service||0,c.swt_industry||0,c.swt_ai||0,c.swt_advantage||0,c.swt_process||0,c.swt_team||0,c.swt_partner||0);
      const MODS=[['swt_advantage','核心优势'],['swt_service','一站式服务'],['swt_industry','行业覆盖'],['swt_stat','业绩数据'],['swt_case','客户案例'],['swt_ai','AI能力'],['swt_process','服务流程'],['swt_plan','套餐方案'],['swt_team','团队成员'],['swt_partner','合作伙伴']];
      let bars='';
      MODS.forEach(function(m){const v=c[m[0]]||0;bars+='<div class="mbar"><span class="nm">'+m[1]+'</span><div class="track"><i style="width:'+Math.round(v/maxMod*100)+'%"></i></div><span class="ct">'+v+'</span></div>';});
      const statusTxt=['未处理','已联系','已成交'];
      let mlist='';
      if(!msgs.length){mlist='<div style="color:#9aa;font-size:13px;padding:10px 0">暂无咨询留言</div>';}
      msgs.slice(0,6).forEach(function(m){
        const initial=(m.name||'?').substr(0,1);
        mlist+='<div class="omsg" data-id="'+m.id+'"><div class="av">'+esc(initial)+'</div><div class="body"><b>'+esc(m.name)+' · '+esc(m.phone)+'</b><p>'+esc(m.content||'')+'</p></div><span class="st badge '+(m.status==2?'on':(m.status==1?'hot':'pending'))+'">'+(statusTxt[m.status]||'未处理')+'</span></div>';
      });
      const quick=['swt_settings','swt_section','swt_banner','swt_advantage','swt_service','swt_industry','swt_stat','swt_case','swt_ai','swt_process','swt_plan','swt_team','swt_partner','swt_message'];
      let q='';
      quick.forEach(function(k){const icon=GROUPS.flatMap(g=>g.items).find(it=>it[0]===k);q+='<a data-k="'+k+'"><span class="mi">'+(icon?icon[1]:'•')+'</span>'+(TYPES[k]?TYPES[k].name:k)+'</a>';});
      document.getElementById('tableView').innerHTML=
        '<div class="ov-cards">'
        +'<div class="ov-card"><div class="ic">💬</div><div class="num">'+((c.swt_message)||0)+'</div><div class="lab">咨询留言总数</div></div>'
        +'<div class="ov-card"><div class="ic">⏳</div><div class="num">'+pending+'</div><div class="lab">待处理线索</div></div>'
        +'<div class="ov-card"><div class="ic">🏆</div><div class="num">'+((c.swt_case)||0)+'</div><div class="lab">客户案例</div></div>'
        +'<div class="ov-card"><div class="ic">💎</div><div class="num">'+((c.swt_plan)||0)+'</div><div class="lab">套餐方案</div></div>'
        +'</div>'
        +'<div class="ov-grid">'
        +'<div class="ov-panel"><h4>📦 内容模块统计 <span class="tag">实时</span></h4>'+bars+'</div>'
        +'<div class="ov-panel"><h4>💬 最新咨询留言 <span class="tag">'+(msgs.length)+'</span></h4>'+mlist+'</div>'
        +'</div>'
        +'<div class="ov-quick"><h4>⚡ 快捷入口</h4><div class="quick-grid">'+q+'</div></div>';
      // 绑定
      document.querySelectorAll('.omsg[data-id]').forEach(el=>el.onclick=function(){const id=+el.dataset.id;curType='swt_message';selectType('swt_message');setTimeout(function(){openModal(id);},450);});
      document.querySelectorAll('.quick-grid a').forEach(el=>el.onclick=function(){selectType(el.dataset.k);});
    });
  });
}

function esc(s){return String(s).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));}

/* 弹窗 */
const mask=document.getElementById('mask');
document.getElementById('cancelBtn').onclick=()=>mask.classList.remove('open');
mask.onclick=e=>{if(e.target===mask)mask.classList.remove('open');};

function openModal(id){
  const cfg=TYPES[curType];
  const row = id? curList.find(r=>r.id===id) : null;
  document.getElementById('modalTitle').textContent=(id?'编辑 ':'新增 ')+cfg.name;
  let html='';
  cfg.fields.forEach(f=>{
    let val = row? (row[f.k]??'') : (f.def!==undefined?f.def:'');
    /* 站点设置中的 logo / favicon: 改为图片上传控件 (选图后自动上传并保存) */
    if(curType==='swt_settings' && f.k==='setting_value' && row && ['site_logo','favicon'].includes(row.setting_key)){
      html+='<div class="field"><label>'+f.l+'（选择图片后自动上传并保存）</label>'
        +'<input type="hidden" name="setting_value" value="'+esc(val)+'">'
        +'<input type="file" name="__img" accept="image/*" onchange="swtUploadImage(this)">'
        +'<div class="preview">'+(val?'<img src="'+esc(val)+'" style="max-height:80px;border-radius:8px"><div style="font-size:12px;color:#0a8a4a;margin-top:4px;word-break:break-all">当前：'+esc(val)+'</div>':'<span style="color:#888;font-size:13px">未设置（选择图片后会自动上传保存，留空则用默认）</span>')+'</div>'
        +'</div>';
      return;
    }
    if(f.t==='check'){
      html+='<div class="field"><label>'+f.l+'</label><input type="checkbox" name="'+f.k+'" '+(val?'checked':'')+' '+(f.ro?'disabled':'')+'></div>';
    } else if(f.t==='textarea'){
      html+='<div class="field"><label>'+f.l+(f.req?' *':'')+'</label><textarea name="'+f.k+'" '+(f.ro?'disabled':'')+'>'+esc(val)+'</textarea></div>';
    } else if(f.t==='select'){
      html+='<div class="field"><label>'+f.l+'</label><select name="'+f.k+'" '+(f.ro?'disabled':'')+'>';
      f.opts.forEach(o=>{const [v,t]=o.split(':');html+='<option value="'+v+'" '+(String(val)===v?'selected':'')+'>'+(t||v)+'</option>';});
      html+='</select></div>';
    } else if(f.t==='icon'){
      const em=ICONS[val]||'🔹';
      html+='<div class="field"><label>'+f.l+'（可用：'+Object.keys(ICONS).join(' ')+'）</label><input type="text" name="'+f.k+'" value="'+esc(val)+'" '+(f.ro?'disabled':'')+'><div class="preview" id="pv_'+f.k+'">'+em+'</div></div>';
    } else {
      html+='<div class="field"><label>'+f.l+(f.req?' *':'')+'</label><input type="text" name="'+f.k+'" value="'+esc(val)+'" '+(f.ro?'disabled':'')+'></div>';
    }
  });
  if(id) html+='<input type="hidden" name="id" value="'+id+'">';
  document.getElementById('editForm').innerHTML=html;
  document.querySelectorAll('#editForm input[name]').forEach(inp=>{
    if(inp.name && TYPES[curType].fields.find(f=>f.k===inp.name && f.t==='icon')){
      inp.oninput=()=>{document.getElementById('pv_'+inp.name).textContent=ICONS[inp.value]||'🔹';};
    }
  });
  mask.classList.add('open');
}

document.getElementById('addBtn').onclick=()=>openModal(null);

document.getElementById('saveBtn').onclick=function(){
  /* 双保险: 若已选择图片但尚未上传, 先上传(内部会自动保存), 不重复提交 */
  const fi=document.querySelector('#editForm input[type=file]');
  if(fi && fi.files && fi.files.length){ swtUploadImage(fi); return; }
  const fd=new FormData(document.getElementById('editForm'));
  fd.append('action','save'); fd.append('type',curType); fd.append('swt_csrf',CSRF);
  fetch('/api.php',{method:'POST',body:fd}).then(r=>r.json()).then(res=>{
    toast(res.msg||(res.code===0?'保存成功':'保存失败'));
    if(res.code===0){mask.classList.remove('open');loadList();}
  });
};

function del(id){
  if(!confirm('确定删除该项？'))return;
  const fd=new FormData(); fd.append('action','delete'); fd.append('type',curType); fd.append('id',id); fd.append('swt_csrf',CSRF);
  fetch('/api.php',{method:'POST',body:fd}).then(r=>r.json()).then(res=>{toast(res.msg);if(res.code===0)loadList();});
}

/* 站点设置里的图片字段上传 (logo / favicon)
   —— 选中文件即自动上传并保存到数据库, 刷新也保留; 无需再点任何按钮 */
function swtUploadImage(el){
  const field=el.closest('.field');
  const fileInput=field.querySelector('input[type=file]');
  const prev=field.querySelector('.preview');
  if(!fileInput || !fileInput.files.length){toast('请先选择图片');return;}
  const fd=new FormData(); fd.append('file',fileInput.files[0]); fd.append('swt_csrf',CSRF);
  if(prev) prev.innerHTML='<span style="color:#888;font-size:13px">上传中...</span>';
  fetch('/api.php?action=upload',{method:'POST',body:fd}).then(r=>r.json()).then(res=>{
    if(res.code!==0){toast(res.msg||'上传失败');if(prev)prev.innerHTML='<span style="color:#e0533d;font-size:13px">上传失败，请重试</span>';return;}
    const url=res.data.url;
    field.querySelector('input[name=setting_value]').value=url;
    if(prev) prev.innerHTML='<img src="'+esc(url)+'" style="max-height:80px;border-radius:8px"><div style="font-size:12px;color:#0a8a4a;margin-top:4px;word-break:break-all">✓ 已上传到服务器并保存：'+esc(url)+'</div>';
    try{fileInput.value='';}catch(e){}
    autoSaveImage();
  }).catch(()=>{toast('网络错误');if(prev)prev.innerHTML='<span style="color:#e0533d;font-size:13px">上传失败，请重试</span>';});
}

/* 上传后自动把当前设置项保存到数据库 */
function autoSaveImage(){
  const fd=new FormData(document.getElementById('editForm'));
  fd.append('action','save'); fd.append('type',curType); fd.append('swt_csrf',CSRF);
  fetch('/api.php',{method:'POST',body:fd}).then(r=>r.json()).then(res=>{
    if(res.code===0){toast('✓ 已上传并保存，刷新也保留');loadList();}
    else{toast(res.msg||'自动保存失败，请点「保存」按钮');}
  });
}

selectType(curType);
</script>
</body>
</html>
