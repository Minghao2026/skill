# 视旺团官网

视频号 POI 团购一站式代运营陪跑服务商官网。展示视旺团业绩、服务、AI 能力与案例，所有前端展示内容均可在**后台 CMS 灵活配置**，无需改动代码。

> 视旺团主营业务：从注册、搭建到运营陪跑提供一站式专业化服务，结合 AI 技术帮助实体商家降本增效、低成本精准拓客。

---

## 一、技术栈

| 层 | 技术 |
| --- | --- |
| 后端 | PHP 8.2+（兼容 8.0/8.1/8.2/8.3），原生 PHP + PDO，**无 Composer / 无框架依赖** |
| 数据库 | MySQL 5.7+（兼容 5.7 / 8.0），InnoDB，`utf8mb4` |
| 前端 | 原生 HTML5 + CSS3 + 原生 JavaScript(ES6+)，**零构建、打开即用** |
| 规范 | 后端 PSR-12 风格；所有 SQL 预处理防注入；输出 `htmlspecialchars` 防 XSS；密码 `password_hash`；CSRF 令牌校验 |
| 配色 | 主色 微信绿 `#07C160`；辅色 视频号橙 `#FF8000`；背景 白 |

统一 API 返回格式：`{"code":0,"msg":"","data":{}}`（`code=0` 成功，非 0 失败）。

---

## 二、目录结构

```
shiwangtuan/
├── public/                 # ★ 对外 Web 根目录 (站点根目录指向这里)
│   ├── index.php           # 官网前端入口
│   ├── admin.php           # 后台管理入口
│   ├── api.php             # JSON API 入口
│   ├── install.php         # 浏览器一键安装向导
│   ├── .htaccess           # Apache 配置 (Nginx 见下方)
│   └── assets/             # 前端静态资源 css/js/img/uploads
├── app/                    # 后端代码 (在 Web 根目录之外，禁止外网访问)
│   ├── Core/               # Database / Response / Request / Security / Auth / View
│   ├── Models/             # Content / AdminModel
│   └── Controllers/        # Home / Api / Admin
├── config/                 # database.php / app.php
├── sql/                    # install.sql (建表 + 示例数据)
├── views/                  # 视图模板 frontend/ admin/
├── logs/                   # 运行日志目录 (已禁止 Web 访问)
└── README.md
```

> 安全说明：`app/ config/ sql/ views/ logs/` 均位于 Web 根目录 `public/` **之外**，无法直接通过浏览器访问。

---

## 三、环境要求

- PHP ≥ 8.0（推荐 8.2），需开启 `pdo`、`pdo_mysql` 扩展
- MySQL ≥ 5.7
- 服务器可写：`config/`、`public/assets/uploads/`、`logs/`

---

## 四、部署步骤（宝塔面板）

1. **创建站点**：宝塔 → 网站 → 添加站点，域名如 `你的域名`，PHP 版本选 8.2。
2. **上传代码**：将本项目**全部文件**上传到站点目录 `/www/wwwroot/shiwangtuan`（保留 `public/` 等结构）。
3. **设置站点根目录**：宝塔站点 → 网站目录 → 将「运行目录 / 站点根目录」修改为
   `/www/wwwroot/shiwangtuan/public`（关键！否则框架文件会暴露）。
4. **创建数据库**：宝塔 → 数据库 → 新建，库名 `swtsite2026`，用户名 `swtsite2026`，密码（请自行设定，需与下方 `config/database.php` 保持一致）。
5. **一键安装**：浏览器访问 `https://你的域名/install.php`，确认数据库信息，设置**后台管理员账号密码**，点击「开始安装」。安装程序会自动建表、导入示例内容、写入配置。
6. **删除安装文件**：安装完成后**务必删除** `public/install.php`，或设置访问保护，防止被他人重装。
7. **访问**：
   - 官网前台：`https://你的域名/`
   - 后台管理：`https://你的域名/admin.php`（默认账号 `admin` / 密码 `admin888`，**请尽快修改**）

### Nginx 用户（无 .htaccess）
将站点 `root` 指向 `public/` 即可，无需额外伪静态规则（入口为独立文件 `index.php`/`admin.php`/`api.php`）。如需隐藏入口后缀可自行配置 rewrite。

---

## 五、后台内容配置

登录后台后，左侧菜单对应官网各板块，点击即可**新增 / 编辑 / 删除**，保存后前台实时生效：

| 后台菜单 | 对应官网板块 |
| --- | --- |
| 站点设置 | 站名、口号、电话、微信、邮箱、地址、备案号、业绩备注 |
| 导航菜单 | 顶部导航项与锚点 |
| 首屏轮播 | 首页首屏主/副标题与按钮（默认取第 1 条） |
| 板块标题 | 各板块的标题/副标题/简介 |
| 核心优势 | 「为什么选择视旺团」卡片 |
| 一站式服务 | 注册/搭建/运营陪跑/AI 四大模块 |
| 行业覆盖 | 可入驻行业标签 |
| **业绩数据** | **首页业绩展示区核心指标（数字可含 + / 万 等）** |
| 客户案例 | 业绩区商家案例（metrics 每行一条） |
| AI 能力 | AI 探店/文案/拓客/复盘卡片 |
| 服务流程 | 6 步开通流程 |
| 套餐方案 | 阶梯合作套餐（价格支持示例展示，标记热门） |
| 合作伙伴 | 页脚合作伙伴 |
| 团队成员 | 关于我们团队介绍 |
| 咨询留言 | 官网提交的咨询线索（可改状态/删除） |

> 图标字段填写名称即可（如 `rocket` `money` `ai` `food` `shop`…)，后台有可用图标提示。

---

## 六、同步与维护（本地 ↔ 服务器）

- **本地开发目录**：`/workspace/shiwangtuan`（沙箱开发副本）。
- **服务器目录**：`/www/wwwroot/shiwangtuan`，Web 根 `public/`。
- 修改代码后，将变动文件通过**宝塔文件管理器 / FTP / Git** 同步到服务器对应路径即可，无需重新安装（数据库结构已就绪）。
- 内容变更请在**后台 CMS** 操作，数据存于 `swtsite2026` 数据库，与代码分离，升级代码不影响内容。

---

## 七、安全提示

- 安装后删除 `public/install.php`。
- 尽快修改后台默认密码（`admin` / `admin888`）。
- 图片/文件上传目录已禁止脚本执行。
- 所有写操作均校验 CSRF 令牌，SQL 全部预处理。
- `config/database.php` 含数据库密码，已位于 Web 根之外，请勿移动到 `public/`。

---

## 八、默认账号

| 项 | 值 |
| --- | --- |
| 后台地址 | `/admin.php` |
| 管理员账号 | `admin` |
| 管理员密码 | `admin888`（安装时可修改） |
