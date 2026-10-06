/* AAYKAY Electricals — page behaviour.
   Progressive enhancement: the page is complete without this file. Each feature
   looks for its own elements and fails quietly, so one problem never blocks the rest.
   Motion uses GSAP + ScrollTrigger + Lenis when they are present and the visitor
   has not asked for reduced motion. */
(function () {
  'use strict';

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
  var root = document.documentElement;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var lenis = null;

  function safely(name, fn) {
    try { return fn(); } catch (err) { if (window.console) console.warn('[aaykay] ' + name + ' disabled:', err); }
  }

  /* ---------- scrolling helpers ---------- */
  function headerOffset() {
    var h = $('.site-header');
    return h ? h.getBoundingClientRect().height : 0;
  }
  function scrollToEl(el) {
    var y = el.getBoundingClientRect().top + window.pageYOffset - headerOffset() + 1;
    if (lenis) lenis.scrollTo(y, { duration: 1.1 });
    else window.scrollTo({ top: y, behavior: reduceMotion ? 'auto' : 'smooth' });
  }

  /* ---------- 1. motion ---------- */
  function initMotion() {
    var gsap = window.gsap;
    if (reduceMotion || !gsap) return;
    var ST = window.ScrollTrigger;
    if (ST) gsap.registerPlugin(ST);

    // Inertial scrolling for mouse and trackpad only; touch keeps native scrolling.
    if (window.Lenis && !window.matchMedia('(pointer: coarse)').matches) {
      lenis = new window.Lenis({ lerp: 0.1, smoothWheel: true });
      if (ST) lenis.on('scroll', ST.update);
      gsap.ticker.add(function (t) { lenis.raf(t * 1000); });
      gsap.ticker.lagSmoothing(0);
    }

    if (!ST) return;

    // Hero photo drifts slower than the page.
    gsap.to('.hero-media', {
      yPercent: 10, ease: 'none',
      scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true }
    });

    // Section headings rise in once.
    $$('[data-reveal]').forEach(function (el) {
      gsap.from(el, {
        y: 28, opacity: 0, duration: 1, ease: 'power3.out',
        scrollTrigger: { trigger: el, start: 'top 90%', once: true }
      });
    });

    // Photographs are uncovered from the bottom edge, once.
    $$('[data-reveal-media]').forEach(function (el) {
      var img = $('img', el);
      var t = gsap.timeline({ scrollTrigger: { trigger: el, start: 'top 92%', once: true } });
      t.fromTo(el, { clipPath: 'inset(0 0 100% 0)' }, { clipPath: 'inset(0 0 0% 0)', duration: 1.15, ease: 'power3.inOut' });
      if (img) t.fromTo(img, { scale: 1.12 }, { scale: 1, duration: 1.6, ease: 'power3.out' }, 0);
    });
  }

  /* ---------- 2. header ---------- */
  function initHeader() {
    var header = $('.site-header');
    if (!header) return function () {};
    var update = function () {
      var solid = window.pageYOffset > 24 || header.classList.contains('is-open');
      header.setAttribute('data-state', solid ? 'solid' : 'top');
    };
    update();
    window.addEventListener('scroll', update, { passive: true });
    return update;
  }

  /* ---------- 3. mobile menu ---------- */
  function initMenu(updateHeader) {
    var btn = $('.menu-btn');
    var menu = $('#mobile-menu');
    var header = $('.site-header');
    if (!btn || !menu || !header) return;
    var isOpen = function () { return btn.getAttribute('aria-expanded') === 'true'; };
    function open() {
      menu.hidden = false;
      btn.setAttribute('aria-expanded', 'true');
      header.classList.add('is-open');
      root.classList.add('menu-open');
      if (lenis) lenis.stop();
      updateHeader();
      var first = $('a', menu);
      if (first) first.focus();
    }
    function close(returnFocus) {
      menu.hidden = true;
      btn.setAttribute('aria-expanded', 'false');
      header.classList.remove('is-open');
      root.classList.remove('menu-open');
      if (lenis) lenis.start();
      updateHeader();
      if (returnFocus) btn.focus();
    }
    btn.addEventListener('click', function () { isOpen() ? close(true) : open(); });
    menu.addEventListener('click', function (e) { if (e.target.closest('a')) close(false); });
    document.addEventListener('keydown', function (e) {
      if (!isOpen()) return;
      if (e.key === 'Escape') { close(true); return; }
      if (e.key !== 'Tab') return;
      // keep focus inside the open menu (button + menu links)
      var items = [btn].concat($$('a', menu));
      var i = items.indexOf(document.activeElement);
      if (e.shiftKey && i <= 0) { e.preventDefault(); items[items.length - 1].focus(); }
      else if (!e.shiftKey && i === items.length - 1) { e.preventDefault(); items[0].focus(); }
    });
    window.matchMedia('(min-width: 1081px)').addEventListener('change', function (e) {
      if (e.matches && isOpen()) close(false);
    });
  }

  /* ---------- 4. in-page links ---------- */
  function initAnchors() {
    document.addEventListener('click', function (e) {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey) return;
      var a = e.target.closest('a[href^="#"]');
      if (!a) return;
      var id = a.getAttribute('href').slice(1);
      var target = id ? document.getElementById(id) : null;
      if (!target) return;
      e.preventDefault();
      scrollToEl(target);
      if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
      target.focus({ preventScroll: true });
      try { history.replaceState(null, '', '#' + id); } catch (_) { /* sandboxed viewers */ }
    });
  }

  /* ---------- 5. project record ---------- */
  function initRecord() {
    var table = $('#record-table');
    if (!table) return;
    var rows = $$('tbody tr', table);
    var filters = $('.filters');
    var chips = $$('.chip[data-filter]');
    var status = $('#record-status');
    var moreBtn = $('#record-more');
    var LIMIT = 12;
    var state = { filter: 'all', expanded: false };
    if (filters) filters.hidden = false;

    function render() {
      var matching = rows.filter(function (r) { return state.filter === 'all' || r.getAttribute('data-sector') === state.filter; });
      var visible = (state.filter === 'all' && !state.expanded) ? matching.slice(0, LIMIT) : matching;
      rows.forEach(function (r) { r.hidden = visible.indexOf(r) === -1; });
      chips.forEach(function (c) { c.setAttribute('aria-pressed', String(c.getAttribute('data-filter') === state.filter)); });
      var chip = chips.filter(function (c) { return c.getAttribute('data-filter') === state.filter; })[0];
      var name = chip ? chip.getAttribute('data-label') : 'All sectors';
      if (status) status.textContent = 'Showing ' + visible.length + ' of ' + matching.length + ' projects · ' + name;
      if (moreBtn) {
        var canExpand = state.filter === 'all' && matching.length > LIMIT;
        moreBtn.hidden = !canExpand;
        moreBtn.textContent = state.expanded ? 'Show fewer projects' : 'Show all ' + matching.length + ' projects';
        moreBtn.setAttribute('aria-expanded', String(state.expanded));
      }
      if (window.ScrollTrigger) window.ScrollTrigger.refresh();
    }

    chips.forEach(function (c) {
      c.addEventListener('click', function () { state.filter = c.getAttribute('data-filter'); state.expanded = false; render(); });
    });
    if (moreBtn) moreBtn.addEventListener('click', function () {
      state.expanded = !state.expanded;
      render();
      if (!state.expanded) scrollToEl($('#record'));
    });
    $$('.sector-row[data-filter]').forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        state.filter = link.getAttribute('data-filter');
        state.expanded = false;
        render();
        scrollToEl($('#record'));
        var chip = chips.filter(function (c) { return c.getAttribute('data-filter') === state.filter; })[0];
        if (chip) chip.focus({ preventScroll: true });
      });
    });
    render();
  }

  /* ---------- 6. document viewer ---------- */
  function initLightbox() {
    var dlg = $('#lightbox');
    if (!dlg || typeof dlg.showModal !== 'function') return; // links still open the image
    var img = $('img', dlg);
    var cap = $('.lightbox-caption', dlg);
    $$('[data-lightbox]').forEach(function (trigger) {
      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        img.src = trigger.getAttribute('data-lightbox');
        img.alt = (trigger.querySelector('img') || {}).alt || '';
        cap.textContent = trigger.getAttribute('data-caption') || '';
        dlg.showModal();
        if (lenis) lenis.stop();
      });
    });
    $('.lightbox-close', dlg).addEventListener('click', function () { dlg.close(); });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });
    dlg.addEventListener('close', function () { if (lenis) lenis.start(); img.removeAttribute('src'); });
  }

  /* ---------- 7. copy buttons ---------- */
  function initCopy() {
    $$('[data-copy]').forEach(function (btn) {
      btn.hidden = false;
      btn.addEventListener('click', function () {
        var text = btn.getAttribute('data-copy');
        var flash = function (msg) { btn.textContent = msg; setTimeout(function () { btn.textContent = 'Copy'; }, 1800); };
        var selectFallback = function () {
          var t = document.getElementById(btn.getAttribute('data-copy-target'));
          if (!t) return;
          var range = document.createRange();
          range.selectNodeContents(t);
          var sel = window.getSelection();
          sel.removeAllRanges();
          sel.addRange(range);
          flash('Selected');
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(function () { flash('Copied'); }, selectFallback);
        } else selectFallback();
      });
    });
  }

  /* ---------- 8. enquiry form ---------- */
  function initForm() {
    var form = $('#enquiry');
    if (!form) return;
    form.setAttribute('novalidate', '');
    var status = $('#form-status');
    var fields = $$('input, select, textarea', form);

    function check(f) {
      var msg = '';
      if (f.validity.valueMissing) msg = 'Please enter your ' + (f.getAttribute('data-label') || 'details') + '.';
      else if (f.validity.typeMismatch && f.type === 'email') msg = 'Please enter an email address like name@company.com.';
      var err = document.getElementById(f.id + '-error');
      if (err) err.textContent = msg;
      if (f.required) f.setAttribute('aria-invalid', msg ? 'true' : 'false');
      return !msg;
    }
    fields.forEach(function (f) {
      f.addEventListener('blur', function () { if (f.required && f.value) check(f); });
      f.addEventListener('input', function () { if (f.getAttribute('aria-invalid') === 'true') check(f); });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var results = fields.map(check);
      if (results.indexOf(false) !== -1) {
        var first = fields.filter(function (f) { return f.getAttribute('aria-invalid') === 'true'; })[0];
        if (first) first.focus();
        status.textContent = 'A few required details are missing. They are marked above.';
        return;
      }
      var v = function (name) { var el = form.elements[name]; return el ? el.value.trim() : ''; };
      var to = form.getAttribute('data-to');
      var subject = 'Project enquiry: ' + v('company') + (v('type') ? ' (' + v('type') + ')' : '');
      var lines = ['Name: ' + v('name'), 'Company: ' + v('company'), 'Email: ' + v('email')];
      if (v('phone')) lines.push('Phone: ' + v('phone'));
      if (v('type')) lines.push('Project type: ' + v('type'));
      if (v('city')) lines.push('City: ' + v('city'));
      lines.push('', v('message'));
      status.textContent = 'Your email app should open with this enquiry filled in. If it doesn’t, write to ' + to + ' or call ' + form.getAttribute('data-phone') + '.';
      window.location.href = 'mailto:' + to + '?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(lines.join('\n'));
    });
  }

  /* Motion libraries are an enhancement for content below the fold, so the hosted
     build fetches them after the page has loaded instead of competing with the
     stylesheet, fonts and hero image. (The Artifact build loads them up front.) */
  function loadScripts(list) {
    return list.reduce(function (chain, src) {
      return chain.then(function () {
        return new Promise(function (resolve, reject) {
          var s = document.createElement('script');
          s.src = src; s.onload = resolve; s.onerror = reject;
          document.head.appendChild(s);
        });
      });
    }, Promise.resolve());
  }
  function startMotion() {
    var libs = window.AAYKAY_MOTION_LIBS;
    if (window.gsap || !libs || reduceMotion) { safely('motion', initMotion); return; }
    var go = function () { loadScripts(libs).then(function () { safely('motion', initMotion); }, function () {}); };
    if (document.readyState === 'complete') go(); else window.addEventListener('load', go);
  }

  function start() {
    startMotion();
    var updateHeader = safely('header', initHeader) || function () {};
    safely('menu', function () { initMenu(updateHeader); });
    safely('record', initRecord);
    safely('anchors', initAnchors);
    safely('lightbox', initLightbox);
    safely('copy', initCopy);
    safely('form', initForm);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
