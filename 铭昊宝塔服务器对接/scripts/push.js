#!/usr/bin/env node
'use strict';
/**
 * 代码部署：把本地文件/目录推送到服务器（经宝塔面板 SaveFileBody）
 *
 * 用法:
 *   单文件:
 *     node scripts/push.js -s prod --src /workspace/shiwangtuan/public/assets/css/style.css \
 *           --dest /www/wwwroot/shiwangtuan/public/assets/css/style.css
 *   整目录同步（保留相对路径）:
 *     node scripts/push.js -s prod --sync /workspace/shiwangtuan --remote /www/wwwroot/shiwangtuan
 *   预览（不写入）:
 *     ... --dry-run
 *   仅推送匹配的文件:
 *     ... --only "*.php"
 */
const fs = require('fs');
const path = require('path');
const { getServer } = require('../bt_common/config');
const { withPanel, saveFileBody, getFileBody, createFile, deleteFile, callApi } = require('../bt_common/client');

function walk(dir, base, out, skip) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (skip.test(entry.name)) continue;
    if (entry.isDirectory()) walk(full, base, out, skip);
    else if (entry.isFile()) out.push(path.relative(base, full));
  }
}

async function ensureRemoteDir(page, token, dir) {
  const parts = dir.replace(/\/+$/, '').split('/').filter(Boolean);
  let cur = '';
  for (const p of parts) {
    cur += '/' + p;
    await callApi(page, token, 'files?action=CreateDir&path=' + encodeURIComponent(cur), null, 'GET').catch(() => {});
  }
}

function buildPlan(args) {
  const plan = [];
  if (args.src && args.dest) {
    if (!fs.existsSync(args.src) || !fs.statSync(args.src).isFile()) { console.error('源文件不存在:', args.src); process.exit(1); }
    plan.push({ local: args.src, remote: args.dest });
  } else if (args.sync && args.remote) {
    const skip = /(^|\/)\.git$|node_modules|vendor|\.DS_Store$/;
    const rels = [];
    walk(args.sync, args.sync, rels, skip);
    for (const rel of rels) {
      if (args.only && !minimatch(rel, args.only)) continue;
      plan.push({ local: path.join(args.sync, rel), remote: path.posix.join(args.remote.replace(/\/+$/, ''), rel.split(path.sep).join('/')) });
    }
  } else {
    console.error('需要 --src/--dest 或 --sync/--remote');
    process.exit(1);
  }
  return plan;
}

// 极简 glob（仅支持 * 与 ** 近似）
function minimatch(rel, pat) {
  const re = new RegExp('^' + pat.split('*').map(s => s.replace(/[.+?^${}()|[\]\\]/g, '\\$&')).join('.*') + '$');
  return re.test(rel);
}

async function main() {
  const args = parseArgs(process.argv.slice(2));
  const server = getServer(args.s);
  const plan = buildPlan(args);
  if (!plan.length) { console.log('没有需要推送的文件'); return; }

  console.log(`\n🚀 准备向 [${server.name}] 推送 ${plan.length} 个文件\n`);
  if (args['dry-run']) {
    for (const f of plan) console.log('  (预览) ', f.local, '→', f.remote);
    console.log('\n--dry-run 完成，未做任何写入。');
    return;
  }

  let ok = 0, fail = 0;
  await withPanel(server, async ({ page, token }) => {
    // 先确保目标根目录存在
    const roots = new Set(plan.map(f => path.posix.dirname(f.remote.replace(/\/+$/, ''))));
    for (const r of roots) { await ensureRemoteDir(page, token, r); }
    for (const f of plan) {
      const content = fs.readFileSync(f.local, 'utf8');
      // SaveFileBody 要求目标文件已存在：不存在则先 CreateFile
      const exist = await getFileBody(page, token, f.remote);
      if (!exist || exist.status === false) {
        await createFile(page, token, f.remote);
      }
      const res = await saveFileBody(page, token, f.remote, content, 'utf-8');
      const status = res.j && (res.j.status === true || res.j.status === undefined && !res.j.msg);
      // 回读校验字节数
      const back = await getFileBody(page, token, f.remote);
      const remoteLen = back && back.data != null ? String(back.data).length : -1;
      const localLen = content.length;
      const verified = remoteLen === localLen;
      if (status && verified) {
        ok++; console.log(`  ✅ ${f.remote}  (${localLen} 字符已校验)`);
      } else {
        fail++; console.log(`  ❌ ${f.remote}  status=${status} 本地=${localLen} 远程回读=${remoteLen}  msg=${res.j && res.j.msg}`);
      }
    }
  });

  console.log(`\n结果: 成功 ${ok} / 失败 ${fail} / 共 ${plan.length}`);
  process.exit(fail ? 3 : 0);
}

function parseArgs(argv) {
  const a = {};
  for (let i = 0; i < argv.length; i++) {
    const t = argv[i];
    if (t === '-s' || t === '--server') a.s = argv[++i];
    else if (t === '--src') a.src = argv[++i];
    else if (t === '--dest') a.dest = argv[++i];
    else if (t === '--sync') a.sync = argv[++i];
    else if (t === '--remote') a.remote = argv[++i];
    else if (t === '--only') a.only = argv[++i];
    else if (t === '--dry-run') a['dry-run'] = true;
  }
  return a;
}

if (require.main === module) main();
module.exports = { parseArgs, buildPlan };
