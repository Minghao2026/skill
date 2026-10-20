'use strict';
/**
 * 宝塔面板客户端（基于 Playwright 真浏览器登录）
 *
 * 为什么用 Playwright 而不是官方 OpenAPI？
 *   本项目面板的"安全入口"是 SPA，纯 HTTP 请求无法通过校验；
 *   且文件管理接口(读/写)只需登录后的会话令牌 x-http-token 即可调用，
 *   因此用真实浏览器登录拿令牌是最稳、最少依赖的方式。
 *   数据库写操作(DoSql)若配置了面板 API 密钥则走签名，否则走"一次性执行器"兜底（见 db.js）。
 */
const crypto = require('crypto');
const path = require('path');

let _pw = null;
function loadPlaywright() {
  if (_pw) return _pw;
  const candidates = [
    'playwright',
    '/root/.nvm/versions/node/v22.13.1/lib/node_modules/playwright',
  ];
  for (const c of candidates) {
    try { _pw = require(c); return _pw; } catch (e) { /* try next */ }
  }
  throw new Error('未找到 playwright，请先安装：npm i -g playwright 或确保 nvm 下的 playwright 可用');
}

/** 登录面板，返回 {browser, context, page, token} */
async function login(server, opts = {}) {
  const { chromium } = loadPlaywright();
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  const context = await browser.newContext({ ignoreHTTPSErrors: true });
  const page = await context.newPage();
  const log = opts.log || (() => {});
  try {
    await page.goto(server.panel_url, { waitUntil: 'networkidle', timeout: 30000 });
    const bodyText = (await page.textContent('body')) || '';
    if (/安全入口校验失败|正确的入口/.test(bodyText)) {
      throw new Error('面板安全入口校验失败：' + server.panel_url + ' 不是正确的入口路径，请核对面板设置里的"安全入口"。');
    }
    const u = await page.$('input[name="username"], #username, input[placeholder*="用户"], input[placeholder*="账号"]');
    const p = await page.$('input[type="password"]');
    if (u) await u.fill(server.username || '');
    if (p) await p.fill(server.password || '');
    await page.click('button:has-text("登录"), input[type="submit"], button[type="submit"], .login-btn, #btn_login', { timeout: 8000 }).catch(() => {});
    // 二次等待：部分面板有动态口令/APP 验证步骤
    await page.waitForTimeout(2500);
    let token = null;
    for (let i = 0; i < 40; i++) {
      token = await page.evaluate(() => window.vite_public_request_token || window.request_token || null);
      if (token) break;
      await page.waitForTimeout(500);
    }
    if (!token) {
      const stillLogin = await page.$('input[type="password"]');
      if (stillLogin) throw new Error('登录失败：未获取到会话令牌，可能启用了动态口令/APP 验证，请在面板设置中确认登录方式。');
      throw new Error('登录失败：未获取到会话令牌 x-http-token');
    }
    log('已登录面板，令牌前缀', token.slice(0, 8) + '...');
    return { browser, context, page, token };
  } catch (e) {
    await browser.close().catch(() => {});
    throw e;
  }
}

/** 通用面板接口调用（在 page 上下文内执行 fetch） */
async function callApi(page, token, action, body, method = 'POST') {
  const result = await page.evaluate((o) => {
    return (async (a, b, m, t) => {
      const opt = { method: m, headers: { 'x-http-token': t, 'Content-Type': 'application/x-www-form-urlencoded' } };
      if (b) opt.body = b;
      const r = await fetch('/' + a, opt);
      const txt = await r.text();
      let j; try { j = JSON.parse(txt); } catch (e) { j = { _raw: txt.slice(0, 200) }; }
      return { status: r.status, j };
    })(o.a, o.b, o.m, o.t);
  }, { a: action, b: body, m: method, t: token });
  return result;
}

/** 写入文件内容（SaveFileBody）。返回面板原始响应。 */
async function saveFileBody(page, token, filePath, data, encoding = 'utf-8') {
  const body = 'path=' + encodeURIComponent(filePath) + '&data=' + encodeURIComponent(data) + '&encoding=' + encodeURIComponent(encoding);
  return callApi(page, token, 'files?action=SaveFileBody', body, 'POST');
}

/** 读取文件内容（GetFileBody）。返回 {data, st_mtime, size} 或 null */
async function getFileBody(page, token, filePath) {
  const r = await callApi(page, token, 'files?action=GetFileBody&path=' + encodeURIComponent(filePath), null, 'GET');
  return r.j;
}

/** 创建空文件（SaveFileBody 要求目标文件已存在，新建文件需先 CreateFile）。已存在时返回失败可忽略。 */
async function createFile(page, token, filePath) {
  return callApi(page, token, 'files?action=CreateFile&path=' + encodeURIComponent(filePath), null, 'GET');
}

/** 删除文件/目录（DeleteFile，移动到回收站） */
async function deleteFile(page, token, filePath) {
  return callApi(page, token, 'files?action=DeleteFile&path=' + encodeURIComponent(filePath), null, 'GET');
}

/** 列出目录（GetDirNew） */
async function listDir(page, token, dir = '/www') {
  const r = await callApi(page, token, 'files?action=GetDirNew&path=' + encodeURIComponent(dir) + '&p=1&showRow=200', null, 'GET');
  return r.j;
}

/** 数据列表（datalist，可列数据库/站点等）。table 如 databases/sites */
async function datalist(page, token, table, extra = '') {
  const body = 'table=' + encodeURIComponent(table) + '&p=1&limit=50' + (extra ? '&' + extra : '');
  return callApi(page, token, 'datalist/data/get_data_list', body, 'POST');
}

/** 执行 SQL。若提供 api_key 则按宝塔 OpenAPI 规则加签名，否则仅带会话令牌（写操作通常会被拒，需密钥或走执行器兜底）。 */
async function doSql(page, token, dbName, sql, apiKey) {
  let body = 'db_name=' + encodeURIComponent(dbName) + '&sql=' + encodeURIComponent(sql);
  if (apiKey) {
    const t = Math.floor(Date.now() / 1000);
    const tm = crypto.createHash('md5').update(apiKey).digest('hex');
    const rt = crypto.createHash('md5').update(t + tm).digest('hex');
    body += '&request_time=' + t + '&request_token=' + rt;
  }
  return callApi(page, token, 'database?action=DoSql', body, 'POST');
}

async function close(browser) {
  if (browser) await browser.close().catch(() => {});
}

/** 便捷封装：登录 -> 执行回调 -> 关闭 */
async function withPanel(server, fn, opts = {}) {
  const sess = await login(server, opts);
  try {
    return await fn(sess);
  } finally {
    await close(sess.browser);
  }
}

module.exports = {
  loadPlaywright, login, callApi, saveFileBody, getFileBody, createFile, deleteFile, listDir, datalist, doSql, close, withPanel,
};
