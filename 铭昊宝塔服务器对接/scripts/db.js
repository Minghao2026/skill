#!/usr/bin/env node
'use strict';
/**
 * 数据库工具（经宝塔面板）
 *   list                      列出所有数据库
 *   tables --db <name>        列出某库的数据表
 *   apply --file x.sql        执行 SQL 文件（部署/迁移）
 *
 * 执行通道（apply / tables 的读取兜底）:
 *   1) 若配置了面板 API 密钥(api_key)，优先用标准 DoSql 接口（带签名）。
 *   2) 否则（或无密钥时接口被拒），改用"一次性执行器"：
 *      上传 SQL + 一个调用项目自身 PDO 配置的执行脚本到 public/，经站点 URL 触发，
 *      执行后立即自删（随机文件名，不接受任何外部输入）。
 *      读语句(SELECT/SHOW/...)用 query() 返回结果集；写语句用 exec() 返回影响行数。
 */
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const { getServer } = require('../bt_common/config');
const { withPanel, datalist, doSql, callApi, createFile, deleteFile } = require('../bt_common/client');

function cmdList(server) {
  return withPanel(server, async ({ page, token }) => {
    const r = await datalist(page, token, 'databases');
    const rows = (r.j && (r.j.data || [])) || [];
    if (!rows.length) { console.log('（无数据库或无权读取）'); return; }
    console.log('\n📊 数据库列表\n');
    console.log(`${'ID'.padEnd(5)} ${'名称'.padEnd(22)} ${'用户'.padEnd(22)} ${'权限'.padEnd(14)} 大小`);
    for (const d of rows) {
      console.log(`${String(d.id || '').padEnd(5)} ${String(d.name || '').padEnd(22)} ${String(d.username || '').padEnd(22)} ${String(d.accept || '').padEnd(14)} ${d.quota && d.quota.used || 0}`);
    }
  });
}

function cmdTables(server, dbName) {
  if (!dbName) { console.error('需要 --db <库名>'); process.exit(1); }
  return withPanel(server, async ({ page, token }) => {
    // 先试读接口 GetInfo
    const r = await callApi(page, token, 'database?action=GetInfo&db_name=' + encodeURIComponent(dbName), null, 'GET');
    const tables = (r.j && r.j.tables) || [];
    if (tables.length) {
      console.log(`\n📋 数据库 ${dbName} 的表（共 ${tables.length}）\n`);
      for (const t of tables) console.log(`- ${t.table_name}  (${t.type || ''}, ${t.rows_count || 0} 行)`);
      return;
    }
    // 兜底：经一次性执行器跑 SHOW TABLES（只读）
    if (!server.site_url) { console.log('（该接口需 API 密钥；配置 site_url 可走执行器兜底列出表）'); return; }
    console.log('（GetInfo 需 API 密钥，改用执行器兜底列出表）');
    const json = await runViaRunner(server, 'SHOW TABLES FROM `' + dbName + '`', dbName);
    if (json && json.ok && Array.isArray(json.results)) {
      const names = [];
      for (const res of json.results) {
        if (Array.isArray(res.rows)) for (const row of res.rows) { const v = Object.values(row)[0]; if (v) names.push(v); }
      }
      console.log(`\n📋 数据库 ${dbName} 的表（共 ${names.length}）\n`);
      for (const n of names) console.log('- ' + n);
    } else {
      console.log('未能列出表：', JSON.stringify(json).slice(0, 200));
    }
  });
}

function runnerPhp(tag) {
  return `<?php declare(strict_types=1);
/* 一次性 SQL 执行器：由部署工具生成，执行后自删。不接受任何外部输入。 */
$tag = '${tag}';
$cfgPath = dirname(__DIR__) . '/config/database.php';
if (!is_file($cfgPath)) { http_response_code(500); echo json_encode(['ok'=>false,'msg'=>'config missing']); exit; }
$cfg = require $cfgPath;
$sqlFile = __DIR__ . '/__swt_sql_' . $tag . '.sql';
if (!is_file($sqlFile)) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'sql missing']); exit; }
function clean(){ @unlink(__DIR__ . '/__swt_sql_' . '${tag}' . '.sql'); @unlink(__FILE__); }
function isRead($s){ return (bool)preg_match('/^\\s*(SELECT|SHOW|DESCRIBE|DESC|EXPLAIN|USE)\\b/i', $s); }
try {
    $dsn = 'mysql:host=' . $cfg['host'] . ';port=' . (int)$cfg['port'] . ';dbname=' . $cfg['dbname'] . ';charset=' . $cfg['charset'];
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $sql = file_get_contents($sqlFile);
    $stmts = array_filter(array_map('trim', explode(';', $sql)), function($s){ return $s !== ''; });
    $results = [];
    foreach ($stmts as $s) {
        if (isRead($s)) {
            $q = $pdo->query($s);
            $rows = $q->fetchAll();
            $results[] = ['sql' => mb_substr($s, 0, 160), 'rows' => array_slice($rows, 0, 50), 'rowCount' => count($rows)];
        } else {
            $n = $pdo->exec($s);
            $results[] = ['sql' => mb_substr($s, 0, 160), 'affected' => $n];
        }
    }
    clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok'=>true, 'count'=>count($results), 'results'=>$results]);
} catch (Throwable $e) {
    clean();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['ok'=>false, 'msg'=>$e->getMessage()]);
}
`;
}

/** 经一次性执行器在服务器上跑一段 SQL（无需 API 密钥）。返回解析后的 JSON。 */
async function runViaRunner(server, sql, dbName) {
  const tag = crypto.randomBytes(8).toString('hex');
  const runnerName = '__swt_sql_' + tag + '.php';
  const sqlName = '__swt_sql_' + tag + '.sql';
  const pubRoot = (server.remote_root || '').replace(/\/+$/, '') + '/public';
  const runnerRemote = pubRoot + '/' + runnerName;
  const sqlRemote = pubRoot + '/' + sqlName;
  // 注入库名到 SQL 头部（仅用于无 db_name 上下文的语句；带 db_name 的语句不受影响）
  const fullSql = 'USE `' + dbName + '`;' + "\n" + sql;

  await withPanel(server, async ({ page, token }) => {
    await createFile(page, token, runnerRemote);
    await createFile(page, token, sqlRemote);
    await callApi(page, token, 'files?action=SaveFileBody&path=' + encodeURIComponent(runnerRemote), 'data=' + encodeURIComponent(runnerPhp(tag)) + '&encoding=utf-8', 'POST');
    await callApi(page, token, 'files?action=SaveFileBody&path=' + encodeURIComponent(sqlRemote), 'data=' + encodeURIComponent(fullSql) + '&encoding=utf-8', 'POST');
  });

  const base = server.site_url.replace(/\/+$/, '');
  let json;
  try {
    const resp = await fetch(base + '/' + runnerName, { redirect: 'manual' });
    const txt = await resp.text();
    try { json = JSON.parse(txt); } catch (e) { json = { ok: false, msg: txt.slice(0, 200) }; }
  } catch (e) {
    json = { ok: false, msg: 'HTTP 触发失败: ' + e.message };
  }
  // 清理
  await withPanel(server, async ({ page, token }) => {
    await deleteFile(page, token, runnerRemote).catch(() => {});
    await deleteFile(page, token, sqlRemote).catch(() => {});
  });
  return json;
}

async function cmdApply(server, sqlFile, dbName) {
  if (!fs.existsSync(sqlFile)) { console.error('SQL 文件不存在:', sqlFile); process.exit(1); }
  const sql = fs.readFileSync(sqlFile, 'utf8');
  if (!dbName) dbName = server.database;
  if (!dbName) { console.error('需要 --db <库名> 或在配置中设置 database'); process.exit(1); }

  // 通道 1：API 密钥签名 DoSql
  if (server.api_key) {
    console.log('▶ 尝试 DoSql（API 密钥签名）...');
    const res = await withPanel(server, async ({ page, token }) => doSql(page, token, dbName, sql, server.api_key));
    if (res.j && res.j.status === true) {
      console.log('✅ DoSql 执行成功');
      console.log(JSON.stringify(res.j).slice(0, 400));
      return;
    }
    console.log('⚠️  DoSql 未成功（', res.j && res.j.msg, '），改用一次性执行器兜底');
  }

  // 通道 2：一次性执行器
  if (!server.site_url) { console.error('缺少 site_url（配置中设置），无法使用执行器兜底。请配置面板 API 密钥，或补充 site_url。'); process.exit(1); }
  console.log('▶ 上传并通过一次性执行器执行 SQL ...');
  const json = await runViaRunner(server, sql, dbName);
  console.log(JSON.stringify(json, null, 2).slice(0, 1500));
  if (!json || !json.ok) process.exit(3);
}

async function main() {
  const args = parseArgs(process.argv.slice(2));
  const server = getServer(args.s);
  const sub = args._cmd;
  if (sub === 'list') await cmdList(server);
  else if (sub === 'tables') await cmdTables(server, args.db);
  else if (sub === 'apply') await cmdApply(server, args.file, args.db, args);
  else { console.log('用法: node scripts/db.js [list|tables|apply] ...'); process.exit(1); }
}

function parseArgs(argv) {
  const a = {};
  for (let i = 0; i < argv.length; i++) {
    const t = argv[i];
    if (t === 'list' || t === 'tables' || t === 'apply') a._cmd = t;
    else if (t === '-s' || t === '--server') a.s = argv[++i];
    else if (t === '--db') a.db = argv[++i];
    else if (t === '--file') a.file = argv[++i];
  }
  return a;
}

if (require.main === module) main();
module.exports = { parseArgs, runnerPhp, runViaRunner };
