# 铭昊宝塔服务器对接 · 跨会话交付包

本压缩包把一个可复用的「宝塔面板对接」技能 + 项目源码 + 配置模板打包在一起，
方便你在**同一个账号下的其他会话**里快速获得「对接宝塔面板、更新代码和数据库」的能力。

> ⚠️ 重要说明：本平台的每个会话运行在**相互隔离的容器**中，本地文件系统
> （`~/.codebuddy/skills`、工作区等）**不会**跨会话共享。因此无法做到
> 「在一个会话装好、其他会话自动就有」。可行的做法是把本包交给目标会话、
> 跑一次安装脚本即可，约 10 秒搞定。

## 包内容
- `铭昊宝塔服务器对接/` — 技能本体（SKILL.md + 脚本 + 依赖），**不含任何真实密码**
- `minghao-baota.yaml` — 配置模板（占位符，需填入真实凭据）
- `shiwangtuan/` — 项目源码（已剔除 `config/`、`logs/`、`node_modules/` 等）
- `install.sh` — 一键安装器

## 在另一个会话里安装（仅需两条命令）
```bash
# 1) 把 minghao-baota-handoff.zip 上传到该会话（拖入对话框 / 文件上传）
# 2) 解压并安装
cd /workspace && unzip -o minghao-baota-handoff.zip && bash install.sh
```

## 安装后必须做
编辑 `~/.openclaw/minghao-baota.yaml`，把占位符替换为真实信息：
```yaml
servers:
  - name: prod
    panel_url: "https://面板地址:端口/安全入口"
    username: "面板账号"
    password: "面板密码"
    api_key: ""            # 可选；填了数据库写操作走标准通道
    remote_root: "/www/wwwroot/shiwangtuan"
    database: "数据库名"
    site_url: "https://你的站点域名"
    verify_ssl: false
```

## 如何触发
- 在该会话对话里直接说「用宝塔面板更新服务器代码/数据库」等，技能会因关键词自动触发；
- 或手动验证连接：`node ~/.codebuddy/skills/铭昊宝塔服务器对接/scripts/check.js`

## 常用命令（安装并配置后）
```bash
SK=~/.codebuddy/skills/铭昊宝塔服务器对接
node $SK/scripts/check.js                                   # 只读自检：登录+列目录+列数据库
node $SK/scripts/push.js --sync ./shiwangtuan --remote /www/wwwroot/shiwangtuan   # 同步代码
node $SK/scripts/db.js list                                 # 列出数据库
node $SK/scripts/db.js apply --file ./update.sql --db 数据库名   # 执行 SQL（走一次性执行器回退）
```
