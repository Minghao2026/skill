---
name: 铭昊宝塔服务器对接
description: 铭昊宝塔服务器对接技能（账号级长期技能，默认常驻）。当用户提供宝塔面板信息（面板地址含安全入口、账号、密码）并表达"更新代码 / 部署到服务器 / 同步文件 / 执行数据库变更 / 跑 SQL 迁移 / 更新线上"等意图时，应**自动调用本技能**。它通过铭昊的宝塔面板完成：① 代码推送（SaveFileBody 写文件 + 字节校验）；② 列库/列表（只读）；③ 执行 SQL 迁移（DoSql 或一次性执行器兜底，无需 API 密钥）。服务器连接已预配置（别名 prod），位于 ~/.openclaw/minghao-baota.yaml，通常无需重新填账号密码。
user-invocable: true
disable-model-invocation: false
icon: icon/bt-logo.svg
metadata:
  openclaw:
    requires:
      bins:
        - node
    keywords:
      - 铭昊
      - 宝塔面板
      - BT-Panel
      - 宝塔登录
      - 宝塔面板信息
      - 代码部署
      - 服务器更新
      - 代码同步
      - 数据库更新
      - SQL迁移
      - 文件推送
      - 服务器对接
---

# 铭昊宝塔服务器对接 · 代码与数据库更新

> **账号级长期技能**：本技能已常驻于本账号的 skills 目录，跨会话保留。当对话中出现"宝塔面板信息 + 要更新代码/数据库"的意图时，应**自动加载并调用本技能**，无需用户重复解释机制。若用户本次提供了新的面板地址/账号，先用 `node scripts/bt-config.js add` 登记后再执行。

本技能用于**通过铭昊的宝塔面板**把本地改动同步到服务器：既能部署前端/后端代码，也能对数据库执行结构或数据更新。它把之前"人工 + Playwright 脚本"的部署方式沉淀为可复用、可审计的标准流程。

## 一、对接机制（这套技能到底是怎么连上服务器的）

核心事实（已逐项实测）：

1. **登录**：用真实浏览器（Playwright，无头）打开面板**安全入口**地址（形如 `https://IP:PORT/安全入口`），填入账号密码登录。宝塔面板是 SPA，纯 HTTP 请求无法通过它的"安全入口"校验，所以必须用真浏览器——这也是为什么早期脚本用 Playwright 而不是 `requests`。
2. **拿令牌**：登录后从页面里读取会话令牌 `window.vite_public_request_token`，后续接口在请求头带 `x-http-token`。
3. **代码更新**：调用面板**文件管理器内部接口** `POST /files?action=SaveFileBody` 写入文件内容；写完后用 `GetFileBody` 回读、比对字符数做完整性校验。这正是之前 `deploy_all.js` 用的那条路，已实测可用。
4. **数据库读取**：用同一个会话令牌 + `datalist/data/get_data_list`（`table=databases`）即可列出库表，**已实测可用**。
5. **数据库执行 SQL**：`database?action=DoSql` 接口对"写操作"要求**宝塔 API 接口密钥**签名（web 会话令牌只能读、不能写）。因此：
   - 若配置里填了 `api_key`，`apply` 走标准 `DoSql` 签名通道；
   - 若没有 `api_key`，自动改用**一次性执行器兜底**：把 SQL 和一个调用项目自身 PDO 配置的执行脚本上传到 `public/`，经站点 URL 触发执行，执行后脚本自删（随机文件名、不接受任何外部输入）。这条兜底通道**已端到端实测可用**。

> ⚠️ 与 `btpanel-phpsite` 技能的区别：那个技能走的是宝塔**官方 OpenAPI**（MD5 签名 + API Token），需要先在面板"API 接口"里开启并生成 Token。本技能走的是**网页登录 + 会话令牌**，不需要额外开启 API 接口，适配"你直接给了一个面板账号"的场景。两者可并存。

## 二、前置条件

- Node.js + Playwright（本环境已具备；其他会话若缺失：`npm i -g playwright` 并 `npx playwright install chromium`）。
- 一份服务器配置，存于 `~/.openclaw/minghao-baota.yaml`（**明文凭据不进 skill 本体**）。

## 三、配置服务器（一次性）

```bash
# 添加（面板地址要带"安全入口"那段路径）
node scripts/bt-config.js add -n prod \
  -H "https://面板地址:端口/安全入口" \
  -u 面板账号 -p "面板密码" \
  --remote-root /www/wwwroot/shiwangtuan \
  --database 数据库名 \
  --site-url https://你的站点域名 \
  --no-verify-ssl

# 可选：配置面板 API 密钥后，数据库写操作优先走 DoSql 标准通道
node scripts/bt-config.js add -n prod -H "..." -u ... -p ... --api-key "你的API密钥"

# 查看 / 删除
node scripts/bt-config.js list
node scripts/bt-config.js remove -n prod
```

## 四、常用命令

### 1) 连接自检（只读，上线前先跑）

```bash
node scripts/check.js -s prod
```

### 2) 代码部署

```bash
# 单文件
node scripts/push.js -s prod \
  --src /workspace/shiwangtuan/public/assets/css/style.css \
  --dest /www/wwwroot/shiwangtuan/public/assets/css/style.css

# 整目录同步（保留相对路径，自动建目录；跳过 .git/node_modules）
node scripts/push.js -s prod --sync /workspace/shiwangtuan --remote /www/wwwroot/shiwangtuan

# 只推送某类文件 / 预览不写入
node scripts/push.js -s prod --sync /workspace/shiwangtuan --remote /www/wwwroot/shiwangtuan --only "*.php"
node scripts/push.js -s prod --sync /workspace/shiwangtuan --remote /www/wwwroot/shiwangtuan --dry-run
```

每个文件写完后都会**回读校验字符数**，不一致会标记为失败，不会"以为成功其实没传上"。

### 3) 数据库

```bash
# 列库（只读）
node scripts/db.js list -s prod

# 列表（只读）
node scripts/db.js tables -s prod --db swtsite2026

# 执行 SQL 迁移（建表/加字段/改数据）
node scripts/db.js apply -s prod --file /workspace/shiwangtuan/sql/update_202610.sql --db swtsite2026
```

## 五、AI 使用约束（重要）

1. **先自检再动手**：执行任何写入前先跑 `check.js`，确认面板可达、令牌能拿到。
2. **代码部署前确认范围**：`--sync` 会覆盖整目录，务必让用户确认本地目录与远程根目录映射正确；建议先 `--dry-run`。
3. **数据库变更需谨慎**：
   - 执行 SQL 前确认是 `SELECT` 还是 `DDL/DML`；`DDL/DML` 属于破坏性操作，先在 `check.js`/备份确认。
   - 推荐在执行前对目标库做备份（面板"数据库→备份"或 `mysqldump`）。
   - 一次性执行器会在 `public/` 下临时落 PHP 文件并自删，**绝不**在 `apply` 之外手动保留该文件。
4. **隐私**：不主动泄露面板密码、API 密钥、数据库密码；配置只存本地 `~/.openclaw/minghao-baota.yaml`。
5. **不在生产做实验**：验证性 SQL（如 `SELECT 1`）可在兜底通道跑；真实迁移前请用户 review SQL 内容。

## 六、已知限制

- `push` 面向**文本/代码文件**（`utf-8`）。二进制（图片等）建议用面板"文件"功能或 `base64` 后再传。
- 一次性执行器用 `;` 简单拆分 SQL；若 SQL 内部含字符串里的 `;`（极少见于迁移脚本）需自行拆分，或用 `--api-key` 走标准 DoSql。
- 面板若开启**动态口令/APP 验证**登录，自动登录会失败，需先在面板设置调整登录方式。
- 面板"安全入口"路径变更后，需同步更新配置里的 `panel_url`。
