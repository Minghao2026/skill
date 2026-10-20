# 铭昊宝塔服务器对接

通过铭昊的宝塔面板对接服务器，进行**代码部署**与**数据库更新**的技能。

## 依赖
- Node.js + Playwright（`npm i -g playwright && npx playwright install chromium`）

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

详见 `SKILL.md`。
