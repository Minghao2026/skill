/* 视旺团官网 · 前端交互 (原生 ES6, 零依赖) v3 */
(function () {
  'use strict';

  /* 导航滚动加深 */
  var nav = document.getElementById('nav');
  if (nav) {
    var onScroll = function () {
      if (window.scrollY > 30) nav.classList.add('scrolled');
      else nav.classList.remove('scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* 移动端菜单 */
  var toggle = document.getElementById('navToggle');
  var links = document.getElementById('navLinks');
  if (toggle && links) {
    toggle.addEventListener('click', function () {
      var open = links.classList.toggle('open');
      toggle.classList.toggle('active', open);
      document.body.style.overflow = open ? 'hidden' : '';
    });
    links.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () {
        links.classList.remove('open');
        toggle.classList.remove('active');
        document.body.style.overflow = '';
      });
    });
  }

  /* 移动端底部操作条：滚过首屏后出现 */
  var mBar = document.getElementById('mBar');
  if (mBar) {
    var barOn = false;
    var barScroll = function () {
      var y = window.scrollY;
      var show = y > 420;
      if (show !== barOn) { barOn = show; mBar.classList.toggle('show', show); }
    };
    window.addEventListener('scroll', barScroll, { passive: true });
    barScroll();
  }

  /* 锚点平滑滚动 (固定导航偏移) */
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var id = a.getAttribute('href');
      if (id && id.length > 1) {
        var el = document.querySelector(id);
        if (el) {
          e.preventDefault();
          var y = el.getBoundingClientRect().top + window.scrollY - 70;
          window.scrollTo({ top: y, behavior: 'smooth' });
        }
      }
    });
  });

  /* 滚动驱动: 揭示 + 数字滚动
     - 优先用 IntersectionObserver (性能更好, 不随滚动高频触发)
     - 兜底: 若 2.5s 后仍有未揭示元素(例如 observer 失效/布局异常), 强制全部显示
     - 绝不让内容因脚本异常而永久不可见 */
  function animateCount(el) {
    var raw = el.getAttribute('data-count') || el.textContent;
    var num = parseFloat(String(raw).replace(/[^\d.]/g, ''));
    if (isNaN(num)) return;
    var dur = 1500, start = null;
    function step(ts) {
      if (!start) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      var val = Math.floor((1 - Math.pow(1 - p, 3)) * num);
      el.textContent = val.toLocaleString('zh-CN');
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = raw;
    }
    requestAnimationFrame(step);
  }

  var revealAll = function () {
    document.querySelectorAll('.reveal:not(.in)').forEach(function (el) { el.classList.add('in'); });
  };

  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        var el = en.target;
        el.classList.add('in');
        el.querySelectorAll('.stat-num:not(.counted)').forEach(function (n) {
          n.classList.add('counted'); animateCount(n);
        });
        if (el.classList.contains('stat-num') && !el.classList.contains('counted')) {
          el.classList.add('counted'); animateCount(el);
        }
        io.unobserve(el);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.01 });
    document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
    document.querySelectorAll('.stat-num').forEach(function (el) { io.observe(el); });
  } else {
    // 老浏览器兜底: 直接全部显示
    revealAll();
    document.querySelectorAll('.stat-num').forEach(function (n) { animateCount(n); });
  }

  // 安全网: 无论任何原因导致部分元素未被观察到, 2.5 秒后兜底显示
  setTimeout(function () {
    var stuck = document.querySelectorAll('.reveal:not(.in)');
    if (stuck.length) {
      stuck.forEach(function (el) {
        el.classList.add('in');
        el.querySelectorAll('.stat-num:not(.counted)').forEach(function (n) {
          n.classList.add('counted'); animateCount(n);
        });
      });
    }
  }, 2500);

  /* 通用表单提交到 /api.php?action=contact
     onState(loading) 统一驱动按钮加载态：true=提交中，false=结束
     无论成功、失败还是网络异常都一定会回调 false，避免按钮永久卡住 */
  function submitContact(fd, msgEl, onOk, onState) {
    if (onState) onState(true);
    if (msgEl) { msgEl.textContent = '提交中...'; msgEl.className = 'form-msg'; }
    return fetch('/api.php?action=contact', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.code === 0) {
          if (msgEl) { msgEl.textContent = '✅ ' + (res.msg || '提交成功'); msgEl.className = 'form-msg ok'; }
          if (onOk) onOk();
        } else if (msgEl) {
          msgEl.textContent = '⚠️ ' + (res.msg || '提交失败'); msgEl.className = 'form-msg err';
        }
      })
      .catch(function () {
        if (msgEl) { msgEl.textContent = '⚠️ 网络错误, 请稍后重试'; msgEl.className = 'form-msg err'; }
      })
      .then(function () { if (onState) onState(false); },
            function () { if (onState) onState(false); });
  }

  /* 底部大 CTA: 仅手机号快速咨询 */
  var ctaForm = document.getElementById('ctaForm');
  if (ctaForm) {
    var ctaMsg = document.getElementById('ctaMsg');
    ctaForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(ctaForm);
      var phone = (fd.get('phone') || '').toString().trim();
      if (!/^1[3-9]\d{9}$/.test(phone)) {
        if (ctaMsg) { ctaMsg.textContent = '⚠️ 请填写正确的手机号'; ctaMsg.className = 'form-msg err'; }
        return;
      }
      var payload = new FormData();
      payload.append('name', '快速咨询');
      payload.append('phone', phone);
      payload.append('content', '来自首页底部「免费获取方案」，请尽快联系我');
      var ctaBtn = ctaForm.querySelector('.btn');
      submitContact(
        payload, ctaMsg,
        function () { ctaForm.reset(); },
        function (loading) {
          if (!ctaBtn) return;
          ctaBtn.classList.toggle('loading', !!loading);
          ctaBtn.disabled = !!loading;
        }
      );
    });
  }

  /* 联系我们完整表单
     校验原则：出错时绝不清空用户输入，只高亮边框 + 内联提示 */
  var form = document.getElementById('contactForm');
  if (form) {
    var msg = document.getElementById('formMsg');
    var submitBtn = form.querySelector('.cf-submit');

    /* 单字段校验：返回 true 表示通过 */
    function checkField(wrap, ok, text) {
      if (!wrap) return true;
      var old = wrap.querySelector('.err');
      if (old) old.remove();
      if (ok) { wrap.classList.remove('bad'); return true; }
      wrap.classList.add('bad');
      var p = document.createElement('span');
      p.className = 'err';
      p.textContent = text;
      wrap.appendChild(p);
      return false;
    }

    function wrapOf(input) { return input.closest('.field'); }

    function validate() {
      var ok = true;
      var name = form.elements.name, phone = form.elements.phone;
      var content = form.elements.content;

      var nv = (name.value || '').trim();
      ok = checkField(wrapOf(name), nv.length > 0, '请填写您的称呼') && ok;

      var pv = (phone.value || '').replace(/\s|-/g, '');
      ok = checkField(wrapOf(phone), /^1[3-9]\d{9}$/.test(pv), '请填写正确的 11 位手机号') && ok;

      var cv = (content.value || '').trim();
      ok = checkField(wrapOf(content), cv.length >= 5, '简单描述下您的行业和需求（至少 5 个字）') && ok;
      return ok;
    }

    /* 输入时实时清除该字段的错误态 */
    ['name', 'phone', 'content'].forEach(function (n) {
      var el = form.elements[n];
      if (!el) return;
      el.addEventListener('input', function () {
        var w = wrapOf(el);
        if (w && w.classList.contains('bad')) checkField(w, true);
      });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!validate()) {
        if (msg) { msg.textContent = '⚠️ 请完善标红项'; msg.className = 'form-msg err'; }
        var firstBad = form.querySelector('.field.bad input, .field.bad textarea');
        if (firstBad) firstBad.focus();
        return;
      }
      if (msg) { msg.textContent = ''; msg.className = 'form-msg'; }
      var fd = new FormData(form);
      submitContact(
        fd, msg,
        function () { form.reset(); form.querySelectorAll('.field').forEach(function (w) { w.classList.remove('bad'); }); },
        function (loading) { if (submitBtn) submitBtn.classList.toggle('loading', !!loading); }
      );
    });
  }

  /* 复制微信号：Clipboard API 优先，降级用临时 textarea */
  var copyBtns = document.querySelectorAll('.copy-btn[data-copy]');
  copyBtns.forEach && copyBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var text = btn.getAttribute('data-copy') || '';
      var ok = function () {
        var old = btn.textContent;
        btn.textContent = '已复制';
        btn.classList.add('done');
        setTimeout(function () { btn.textContent = old; btn.classList.remove('done'); }, 1800);
      };
      var fail = function () { btn.textContent = '复制失败'; setTimeout(function () { btn.textContent = '复制'; }, 1800); };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(ok).catch(function () { legacy(); });
      } else { legacy(); }
      function legacy() {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.cssText = 'position:fixed;top:-999px;opacity:0';
        document.body.appendChild(ta);
        ta.select();
        var done = false;
        try { done = document.execCommand('copy'); } catch (x) {}
        document.body.removeChild(ta);
        done ? ok() : fail();
      }
    });
  });
})();
