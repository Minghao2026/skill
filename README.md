# 铭昊宝塔服务器对接（minghao-baota-server）

通过宝塔面板对接服务器，实现**代码部署**与**数据库自动同步更新**的 WorkBuddy skill。

## 仓库结构

```
.
├── 铭昊宝塔服务器对接/     # skill 本体（SKILL.md、scripts/、bt_common/ 等）
├── shiwangtuan/            # 示例站点项目（PHP），skill 的部署目标
├── minghao-baota.yaml      # 连接配置占位符模板
├── install.sh              # 一键安装脚本
└── HANDOFF.md              # 交接说明
```

## 快速开始

```bash
# 1. 配置服务器（明文凭据只存本地 ~/.openclaw/minghao-baota.yaml）
node scripts/bt-config.js add -n prod -H "https://IP:PORT/安全入口" -u 账号 -p 密码 \
  --remote-root /www/wwwroot/项目 --database 库名 --site-url https://域名 --no-verify-ssl

# 2. 自检（只读）
node scripts/check.js -s prod

# 3. 部署代码
node scripts/push.js -s prod --sync /workspace/项目 --remote /www/wwwroot/项目

# 4. 执行 SQL 迁移
node scripts/db.js apply -s prod --file sql/update.sql --db 库名
```

## 依赖

- Node.js
- Playwright（`npm i -g playwright && npx playwright install chromium`）

## 安全说明

- 面板账号/密码/API Key 等**明文凭据只存本地** `~/.openclaw/minghao-baota.yaml`，请勿提交到仓库。
- 仓库内的 `minghao-baota.yaml` 与 `config.yaml.example` 均为**占位符模板**，不含真实凭据。
