'use strict';
/**
 * 服务器配置管理
 * 配置存放于 ~/.openclaw/minghao-baota.yaml（由 bt-config.js 管理）
 * 绝不把明文凭据写进 skill 脚本本身。
 */
const fs = require('fs');
const path = require('path');
const os = require('os');

const CONFIG_PATH = path.join(os.homedir(), '.openclaw', 'minghao-baota.yaml');

function configPath(custom) {
  return custom || process.env.BT_DEPLOY_CONFIG || CONFIG_PATH;
}

function loadConfig(custom) {
  const p = configPath(custom);
  if (!fs.existsSync(p)) {
    throw new Error('未找到配置文件: ' + p + '\n请先运行: node scripts/bt-config.js add ...');
  }
  const raw = fs.readFileSync(p, 'utf8');
  const cfg = parseYaml(raw);
  cfg.__path = p;
  return cfg;
}

function getServers(custom) {
  const cfg = loadConfig(custom);
  const servers = cfg.servers || [];
  if (!servers.length) throw new Error('配置中没有任何服务器');
  return servers;
}

function getServer(name, custom) {
  const servers = getServers(custom);
  if (!name) return servers[0];
  const s = servers.find(x => x.name === name);
  if (!s) throw new Error('未找到服务器: ' + name + '；可用: ' + servers.map(x => x.name).join(', '));
  return s;
}

/** 极简 YAML 解析（仅支持本配置所需的扁平/列表结构） */
function parseYaml(text) {
  const lines = text.split('\n');
  const root = { servers: [] };
  let cur = null;
  for (const raw of lines) {
    if (!raw.trim() || raw.trim().startsWith('#')) continue;
    const indent = raw.length - raw.replace(/^\s+/, '').length;
    const line = raw.trim();
    if (line.startsWith('- ')) {
      const item = {};
      const kv = line.slice(2);
      const m = kv.match(/^(\w+):\s*(.*)$/);
      if (m) item[m[1]] = unquote(m[2]);
      cur = item;
      root.servers.push(item);
    } else if (indent === 0 && line.includes(':')) {
      const m = line.match(/^(\w+):\s*(.*)$/);
      if (m && m[1] !== 'servers') root[m[1]] = unquote(m[2]);
    } else if (cur && line.includes(':')) {
      const m = line.match(/^(\w+):\s*(.*)$/);
      if (m) cur[m[1]] = unquote(m[2]);
    }
  }
  return root;
}

function unquote(v) {
  if (v === undefined) return v;
  return v.replace(/^["']|["']$/g, '').trim();
}

function saveConfig(cfg, custom) {
  const p = configPath(custom);
  fs.writeFileSync(p, dumpYaml(cfg), 'utf8');
  return p;
}

function dumpYaml(cfg) {
  let out = '';
  if (cfg.panel_url_default) out += `panel_url_default: ${cfg.panel_url_default}\n`;
  out += 'servers:\n';
  for (const s of cfg.servers || []) {
    out += `  - name: ${s.name}\n`;
    out += `    panel_url: "${s.panel_url}"\n`;
    out += `    username: "${s.username}"\n`;
    out += `    password: "${s.password}"\n`;
    out += `    api_key: "${s.api_key || ''}"\n`;
    out += `    remote_root: "${s.remote_root || ''}"\n`;
    out += `    database: "${s.database || ''}"\n`;
    out += `    site_url: "${s.site_url || ''}"\n`;
    out += `    verify_ssl: ${s.verify_ssl === false ? 'false' : 'true'}\n`;
  }
  return out;
}

module.exports = { configPath, loadConfig, getServers, getServer, saveConfig, parseYaml };
