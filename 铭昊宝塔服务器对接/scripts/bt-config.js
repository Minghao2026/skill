#!/usr/bin/env node
'use strict';
/**
 * 服务器配置管理：把面板连接信息存入 ~/.openclaw/minghao-baota.yaml
 * 用法:
 *   node scripts/bt-config.js add -n prod -H https://IP:PORT/entry -u user -p pass \
 *        [--api-key KEY] [--remote-root /www/wwwroot/site] [--database dbname] [--site-url https://x.com] [--no-verify-ssl]
 *   node scripts/bt-config.js list
 *   node scripts/bt-config.js remove -n prod
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { loadConfig, saveConfig, getServers } = require('../bt_common/config');

const CONFIG_PATH = path.join(os.homedir(), '.openclaw', 'minghao-baota.yaml');

function ensureDir() {
  const d = path.dirname(CONFIG_PATH);
  if (!fs.existsSync(d)) fs.mkdirSync(d, { recursive: true });
}

function readCfg() {
  if (!fs.existsSync(CONFIG_PATH)) return { servers: [] };
  return loadConfig();
}

function cmdAdd(args) {
  if (!args.name || !args.H || !args.u || !args.p) {
    console.error('缺少参数：需要 -n <名称> -H <面板地址含安全入口> -u <账号> -p <密码>');
    process.exit(1);
  }
  ensureDir();
  const cfg = readCfg();
  cfg.servers = cfg.servers || [];
  cfg.servers = cfg.servers.filter(s => s.name !== args.name);
  cfg.servers.push({
    name: args.name,
    panel_url: args.H,
    username: args.u,
    password: args.p,
    api_key: args['api-key'] || '',
    remote_root: args['remote-root'] || '',
    database: args.database || '',
    site_url: args['site-url'] || '',
    verify_ssl: args['no-verify-ssl'] ? false : true,
  });
  saveConfig(cfg);
  console.log('✅ 已保存服务器配置:', args.name);
  console.log('   面板地址:', args.H);
  if (!args['api-key']) console.log('   ⚠️  未提供 api_key：数据库写操作(DoSql)将自动改用"一次性执行器"兜底。');
}

function cmdList() {
  let cfg;
  try { cfg = readCfg(); } catch (e) { console.error(e.message); process.exit(1); }
  const servers = cfg.servers || [];
  if (!servers.length) { console.log('暂无服务器配置'); return; }
  console.log('\n📡 已配置服务器\n');
  for (const s of servers) {
    console.log(`- ${s.name}`);
    console.log(`    面板: ${s.panel_url}`);
    console.log(`    账号: ${s.username}   库: ${s.database || '(未设)'}   API密钥: ${s.api_key ? '已配置' : '未配置'}`);
    console.log(`    远程根: ${s.remote_root || '(未设)'}   站点: ${s.site_url || '(未设)'}`);
  }
}

function cmdRemove(args) {
  if (!args.name) { console.error('需要 -n <名称>'); process.exit(1); }
  const cfg = readCfg();
  const before = (cfg.servers || []).length;
  cfg.servers = (cfg.servers || []).filter(s => s.name !== args.name);
  if (cfg.servers.length === before) { console.log('未找到:', args.name); return; }
  saveConfig(cfg);
  console.log('🗑️  已删除:', args.name);
}

function parseArgs(argv) {
  const a = {};
  for (let i = 0; i < argv.length; i++) {
    const t = argv[i];
    if (t === 'add' || t === 'list' || t === 'remove') a._cmd = t;
    else if (t === '-n') a.name = argv[++i];
    else if (t === '-H') a.H = argv[++i];
    else if (t === '-u') a.u = argv[++i];
    else if (t === '-p') a.p = argv[++i];
    else if (t === '--api-key') a['api-key'] = argv[++i];
    else if (t === '--remote-root') a['remote-root'] = argv[++i];
    else if (t === '--database') a.database = argv[++i];
    else if (t === '--site-url') a['site-url'] = argv[++i];
    else if (t === '--no-verify-ssl') a['no-verify-ssl'] = true;
  }
  return a;
}

function main() {
  const args = parseArgs(process.argv.slice(2));
  const cmd = args._cmd || 'list';
  if (cmd === 'add') cmdAdd(args);
  else if (cmd === 'remove') cmdRemove(args);
  else cmdList();
}

if (require.main === module) main();
module.exports = { parseArgs };
