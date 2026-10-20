#!/usr/bin/env bash
# ============================================================
#  铭昊宝塔服务器对接 — 跨会话一键安装器
#  本脚本随 minghao-baota-handoff.zip 一同分发，自包含。
#  使用场景：你在「另一个会话」里拿到了这个 zip，想让那个
#  会话也具备「对接宝塔面板更新代码/数据库」的能力。
#
#  操作步骤（在目标会话的终端里执行）：
#    1) 把 minghao-baota-handoff.zip 上传到该会话（拖入对话框 / 文件上传）
#    2) 解压并运行本脚本：
#         cd /workspace && unzip -o minghao-baota-handoff.zip && bash install.sh
# ============================================================
set -euo pipefail

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SKILL_NAME="铭昊宝塔服务器对接"
SKILL_DST="$HOME/.codebuddy/skills/$SKILL_NAME"
CFG_DST="$HOME/.openclaw/minghao-baota.yaml"

echo "==> 来源目录: $HERE"

# 1) 安装技能本体
echo "==> [1/4] 安装技能到 $SKILL_DST"
mkdir -p "$HOME/.codebuddy/skills"
rm -rf "$SKILL_DST"
cp -r "$HERE/$SKILL_NAME" "$SKILL_DST"
echo "    完成。技能目录已就位。"

# 2) 安装配置模板（占位，无真实密码）
echo "==> [2/4] 安装配置模板到 $CFG_DST"
mkdir -p "$HOME/.openclaw"
if [ -f "$CFG_DST" ]; then
  echo "    检测到已有配置，备份为 ${CFG_DST}.bak"
  cp "$CFG_DST" "${CFG_DST}.bak"
fi
cp "$HERE/minghao-baota.yaml" "$CFG_DST"
echo "    完成。配置模板已写入（尚未填入真实凭据）。"

# 3) 项目文件提示
if [ -d "$HERE/shiwangtuan" ]; then
  echo "==> [3/4] 项目源码位于 $HERE/shiwangtuan"
  echo "    说明：已剔除 config/、logs/、node_modules/ 等敏感与冗余目录。"
  echo "    如需在本会话直接编辑/预览，可自行将其复制到工作区。"
fi

# 4) 依赖检查
echo "==> [4/4] 检查依赖"
if ! command -v node >/dev/null 2>&1; then
  echo "    [警告] 未检测到 node，请先安装 Node.js（https://nodejs.org）。"
else
  echo "    node: $(node -v 2>/dev/null || echo 未知)"
fi
if command -v npx >/dev/null 2>&1 && npx --no-install playwright --version >/dev/null 2>&1; then
  echo "    playwright: 已安装"
else
  echo "    [提示] 文件部署 / 数据库写操作依赖 Playwright，请安装："
  echo "           npm i -g playwright && npx playwright install chromium"
fi

echo
echo "============================================================"
echo "  安装完成 ✅"
echo "============================================================"
echo "  下一步（重要）："
echo "  1) 编辑 $CFG_DST ，把占位符替换为真实面板信息："
echo "       panel_url / username / password / remote_root / database / site_url"
echo "  2) 在对话中直接给出宝塔面板信息，技能会因关键词自动触发；"
echo "     或手动验证连接： node $SKILL_DST/scripts/check.js"
echo "  3) 自动触发关键词：铭昊 / 宝塔面板 / 服务器更新 / 代码同步 / 数据库更新"
echo "============================================================"
