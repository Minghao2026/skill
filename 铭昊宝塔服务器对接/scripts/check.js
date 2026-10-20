#!/usr/bin/env node
'use strict';
/** 只读自检：确认面板可登录、文件接口与数据库读取可用 */
const { getServer } = require('../bt_common/config');
const { withPanel, listDir, datalist } = require('../bt_common/client');

async function main() {
  const args = {};
  for (let i = 2; i < process.argv.length; i++) {
    if (process.argv[i] === '-s' || process.argv[i] === '--server') args.s = process.argv[++i];
  }
  const server = getServer(args.s);
  console.log('🔍 自检服务器:', server.name, server.panel_url);
  await withPanel(server, async ({ page, token }) => {
    const dir = await listDir(page, token, (server.remote_root || '/www').replace(/\/+$/, ''));
    const dirs = (dir && dir.dir) || [];
    const files = (dir && dir.files) || [];
    console.log(`✅ 文件接口正常：目录 ${(server.remote_root||'/www')} 下 ${dirs.length} 个子目录 / ${files.length} 个文件`);
    const r = await datalist(page, token, 'databases');
    const rows = (r.j && (r.j.data || [])) || [];
    console.log(`✅ 数据库读取正常：共 ${rows.length} 个库` + (rows.length ? '（' + rows.slice(0,5).map(d=>d.name).join(', ') + (rows.length>5?'…':'') + '）' : ''));
  });
  console.log('\n🎉 自检通过，可进行代码推送与数据库读取。数据库写操作见 db.js apply。');
}

if (require.main === module) main();
