/* AAYKAY Electricals — page behaviour.
   Progressive enhancement: the page is complete without this file. Each feature
   looks for its own elements and fails quietly, so one problem never blocks the rest.
   Motion is a light reveal of sections as they scroll into view (CSS transitions started
   by an IntersectionObserver), skipped for visitors who ask for reduced motion. There are
   no third-party scripts and no smooth-scrolling library: the browser scrolls natively. */
(function () {
  'use strict';

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
  var root = document.documentElement;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function safely(name, fn) {
    try { return fn(); } catch (err) { if (window.console) console.warn('[aaykay] ' + name + ' disabled:', err); }
  }

  /* ---------- scrolling helpers ---------- */
  function headerOffset() {
    var h = $('.site-header');
    return h ? h.getBoundingClientRect().height : 0;
  }
  // Document y at which `el` sits just below the fixed header. Uses the element's CSS
  // scroll-margin-top (header height + a little air), so script-driven scrolling and the
  // browser's own #anchor jumps land in exactly the same place.
  function targetY(el) {
    var margin = parseFloat(window.getComputedStyle(el).scrollMarginTop) || headerOffset();
    return Math.max(0, Math.round(el.getBoundingClientRect().top + window.pageYOffset - margin));
  }
  // instant: jump without animation (page load, Back/Forward).
  function scrollToY(y, instant) {
    window.scrollTo({ top: y, behavior: (instant || reduceMotion) ? 'auto' : 'smooth' });
  }
  function scrollToEl(el, instant) { scrollToY(targetY(el), instant); }
  // The element a "#id" hash points to, or null.
  function hashTarget(hash) {
    if (!hash || hash.length < 2) return null;
    try { return document.getElementById(decodeURIComponent(hash.slice(1))); } catch (_) { return null; }
  }

  /* ---------- history helpers ----------
     In-page navigation adds history entries so Back and Forward move between sections,
     but the address stays clean (no #section is added), so a link copied from the
     address bar always opens the page at the top. Before leaving an entry we store the
     scroll position in it (state.y) so Back returns the reader to exactly where they were. */
  var STORE_KEY = 'aaykay-scroll:' + location.pathname;
  function saveScroll() {
    var y = Math.round(window.pageYOffset);
    try {
      var st = history.state && typeof history.state === 'object' ? history.state : {};
      var copy = {}; for (var k in st) copy[k] = st[k];
      copy.y = y;
      history.replaceState(copy, '');
    } catch (_) { /* sandboxed viewers */ }
    try { sessionStorage.setItem(STORE_KEY, String(y)); } catch (_) { /* private mode */ }
  }
  function pushEntry(url) {
    try { saveScroll(); history.pushState({}, '', url); } catch (_) { /* sandboxed viewers */ }
  }

  /* ---------- 0. where the page starts ----------
     history.scrollRestoration is "manual" (set in <head>), so this decides:
       - a fresh visit (typed, bookmarked or shared link) starts at the top;
       - a link that names a section (/#contact) or a filter (?sector=…) starts there;
       - a reload returns to exactly where the reader was (saved in sessionStorage,
         which lasts for the tab and is cleared when it closes);
       - Back/Forward returns to the position saved in that history entry.
     The position is re-applied after load and after web fonts settle, in case anything
     above it changed height, unless the visitor has started scrolling by then. */
  function initScrollPosition() {
    var navEntry = window.performance && performance.getEntriesByType ? performance.getEntriesByType('navigation')[0] : null;
    var navType = navEntry ? navEntry.type : 'navigate';
    var userMoved = false;
    var stop = function () { userMoved = true; };
    ['wheel', 'touchstart', 'keydown', 'mousedown'].forEach(function (ev) {
      window.addEventListener(ev, stop, { passive: true, once: true });
    });
    var saved = null;
    try { saved = sessionStorage.getItem(STORE_KEY); } catch (_) { /* private mode */ }

    function wanted() {
      var st = history.state;
      if (navType === 'reload' && saved !== null) return parseInt(saved, 10) || 0;
      if (navType === 'back_forward') {
        if (st && typeof st.y === 'number') return st.y;
        if (saved !== null) return parseInt(saved, 10) || 0;
      }
      var el = hashTarget(location.hash) || (/[?&]sector=/.test(location.search) ? document.getElementById('record') : null);
      return el ? targetY(el) : 0;
    }
    function place() {
      if (userMoved) return;
      var y = Math.min(wanted(), document.documentElement.scrollHeight - window.innerHeight);
      if (Math.abs(window.pageYOffset - y) >= 1) scrollToY(Math.max(0, y), true);
    }
    place();
    if (document.readyState !== 'complete') window.addEventListener('load', place);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(place);

    // Keep the saved position current: shortly after scrolling stops, and when the page is
    // hidden (switching apps on a phone, closing the tab, reloading).
    var saveTimer = null;
    window.addEventListener('scroll', function () {
      clearTimeout(saveTimer);
      saveTimer = setTimeout(saveScroll, 150);
    }, { passive: true });
    window.addEventListener('pagehide', function () {
      try { sessionStorage.setItem(STORE_KEY, String(Math.round(window.pageYOffset))); } catch (_) { /* private mode */ }
    });
    document.addEventListener('click', function (e) {
      if (e.target.closest && e.target.closest('a[href]')) saveScroll();
    }, true);

    // Back/Forward between in-page entries: return to the saved position, else the target.
    window.addEventListener('popstate', function (e) {
      var st = e.state || {};
      if (typeof st.y === 'number') { scrollToY(st.y, true); return; }
      var el = st.target ? document.getElementById(st.target) : hashTarget(location.hash);
      scrollToY(el ? targetY(el) : 0, true);
    });
  }

  /* ---------- 1. motion ----------
     Section headings rise in and photographs are uncovered once, as they scroll into
     view. Only content still below the fold when the page starts is prepared (given
     .is-pending, see site.css), so nothing already on screen ever disappears and fades
     back in (a deep link, a reload part-way down, Back). Without JavaScript, or with
     reduced motion, everything is simply shown. */
  function initMotion() {
    if (reduceMotion || !('IntersectionObserver' in window)) return;
    var foldY = window.innerHeight;
    var items = $$('[data-reveal], [data-reveal-media]').filter(function (el) {
      return el.getBoundingClientRect().top >= foldY;
    });
    if (!items.length) return;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        en.target.classList.add('is-revealed');
        en.target.classList.remove('is-pending');
        io.unobserve(en.target);
      });
    }, { rootMargin: '0px 0px -8% 0px' });
    items.forEach(function (el) { el.classList.add('is-pending'); io.observe(el); });
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
    // Everything except the menu and its button is made inert while the menu is open, so
    // screen readers and keyboard users cannot reach the page hidden behind it.
    var behind = [$('.skip-link'), $('.brand', header), $('.site-nav', header), $('.header-cta', header), $('main'), $('.site-footer')]
      .filter(Boolean);
    var setInert = function (on) { behind.forEach(function (el) { if (on) el.setAttribute('inert', ''); else el.removeAttribute('inert'); }); };
    function open() {
      menu.hidden = false;
      setInert(true);
      btn.setAttribute('aria-expanded', 'true');
      header.classList.add('is-open');
      root.classList.add('menu-open');
      updateHeader();
      var first = $('a', menu);
      if (first) first.focus();
    }
    function close(returnFocus) {
      menu.hidden = true;
      setInert(false);
      btn.setAttribute('aria-expanded', 'false');
      header.classList.remove('is-open');
      root.classList.remove('menu-open');
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
      // A history entry for Back, but no #section in the address (see history helpers).
      pushEntry(location.pathname + location.search);
      try { var st = {}; for (var k in (history.state || {})) st[k] = history.state[k]; st.target = id; history.replaceState(st, ''); } catch (_) { /* sandboxed viewers */ }
      scrollToEl(target);
      if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
      target.focus({ preventScroll: true });
    });
  }

  /* ---------- 5. project record ----------
     Rows are rendered by WordPress (inc/template-data.php), so the record is complete
     without JavaScript. The sector filter lives in the URL (?sector=healthcare), so a
     filtered view can be shared, opened in a new tab, and restored by Back/Forward. */
  // On phones the record is shown as stacked cards (site.css changes the table's display).
  // Some browsers then stop exposing it as a table to screen readers; explicit roles keep
  // the rows, row headers and cells announced as before. Harmless at desktop widths.
  function keepTableRoles(table) {
    table.setAttribute('role', 'table');
    $$('thead, tbody', table).forEach(function (g) { g.setAttribute('role', 'rowgroup'); });
    $$('tr', table).forEach(function (r) { r.setAttribute('role', 'row'); });
    $$('thead th', table).forEach(function (c) { c.setAttribute('role', 'columnheader'); });
    $$('tbody th', table).forEach(function (c) { c.setAttribute('role', 'rowheader'); });
    $$('td', table).forEach(function (c) { c.setAttribute('role', 'cell'); });
  }

  function initRecord() {
    var table = $('#record-table');
    if (!table) return;
    var rows = $$('tbody tr', table);
    var filters = $('.filters');
    var chips = $$('.chip[data-filter]');
    var status = $('#record-status');
    var moreBtn = $('#record-more');
    var LIMIT = 12;
    var keys = chips.map(function (c) { return c.getAttribute('data-filter'); });
    var state = { filter: filterFromUrl(), expanded: false };
    if (filters) filters.hidden = false;
    keepTableRoles(table);

    function filterFromUrl() {
      var m = /[?&]sector=([^&#]*)/.exec(location.search);
      var key = m ? decodeURIComponent(m[1]) : 'all';
      return keys.indexOf(key) !== -1 ? key : 'all';
    }
    // URL for the current filter, keeping any other query parameters.
    function urlFor(filter, hash) {
      var params = location.search.replace(/^\?/, '').split('&').filter(function (p) { return p && p.indexOf('sector=') !== 0; });
      if (filter !== 'all') params.push('sector=' + encodeURIComponent(filter));
      return location.pathname + (params.length ? '?' + params.join('&') : '') + hash;
    }

    function render() {
      var matching = rows.filter(function (r) { return state.filter === 'all' || r.getAttribute('data-sector') === state.filter; });
      var visible = (state.filter === 'all' && !state.expanded) ? matching.slice(0, LIMIT) : matching;
      rows.forEach(function (r) { r.hidden = visible.indexOf(r) === -1; });
      chips.forEach(function (c) { c.setAttribute('aria-pressed', String(c.getAttribute('data-filter') === state.filter)); });
      var chip = chips.filter(function (c) { return c.getAttribute('data-filter') === state.filter; })[0];
      var name = chip ? $('.chip-label', chip).textContent : 'All sectors';
      if (status) status.textContent = 'Showing ' + visible.length + ' of ' + matching.length + ' projects · ' + name;
      if (moreBtn) {
        var canExpand = state.filter === 'all' && matching.length > LIMIT;
        moreBtn.hidden = !canExpand;
        moreBtn.textContent = state.expanded ? 'Show fewer projects' : 'Show all ' + matching.length + ' projects';
        moreBtn.setAttribute('aria-expanded', String(state.expanded));
      }
    }

    chips.forEach(function (c) {
      c.addEventListener('click', function () {
        state.filter = c.getAttribute('data-filter');
        state.expanded = false;
        render();
        // Changing a chip updates the address without adding a history entry.
        try { history.replaceState(history.state, '', urlFor(state.filter, location.hash)); } catch (_) { /* sandboxed viewers */ }
      });
    });
    if (moreBtn) moreBtn.addEventListener('click', function () {
      state.expanded = !state.expanded;
      render();
      if (!state.expanded) scrollToEl($('#record'));
    });
    $$('.sector-row[data-filter]').forEach(function (link) {
      link.addEventListener('click', function (e) {
        // Let modified clicks (new tab, new window) follow the real ?sector= link.
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        e.preventDefault();
        state.filter = link.getAttribute('data-filter');
        state.expanded = false;
        render();
        pushEntry(urlFor(state.filter, ''));
        try { history.replaceState({ target: 'record' }, ''); } catch (_) { /* sandboxed viewers */ }
        scrollToEl($('#record'));
        var chip = chips.filter(function (c) { return c.getAttribute('data-filter') === state.filter; })[0];
        if (chip) chip.focus({ preventScroll: true });
      });
    });
    // Back/Forward: re-apply the filter recorded in the URL (registered before the
    // scroll handler, so the page has its final height before it scrolls).
    window.addEventListener('popstate', function () {
      var f = filterFromUrl();
      if (f !== state.filter) { state.filter = f; state.expanded = false; render(); }
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
      });
    });
    $('.lightbox-close', dlg).addEventListener('click', function () { dlg.close(); });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });
    dlg.addEventListener('close', function () { img.removeAttribute('src'); });
  }

  /* ---------- 7. copy buttons ---------- */
  function initCopy() {
    var live = $('#copy-status'); // role="status": announces the result to screen readers
    $$('[data-copy]').forEach(function (btn) {
      var timer = null;
      btn.hidden = false;
      btn.addEventListener('click', function () {
        var text = btn.getAttribute('data-copy');
        // The copy icon turns into a tick for a moment (data-state="copied", see .copy-btn
        // in site.css); the result is announced through the #copy-status live region.
        var flash = function (msg) {
          if (msg === 'Copied') { btn.setAttribute('data-state', 'copied'); btn.title = 'Copied'; }
          if (live) live.textContent = msg === 'Copied' ? text + ' copied to the clipboard.' : text + ' selected. Press Control+C or Command+C to copy.';
          clearTimeout(timer);
          timer = setTimeout(function () { btn.removeAttribute('data-state'); btn.title = 'Copy'; if (live) live.textContent = ''; }, 1800);
        };
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

  /* ---------- 9. current section in the navigation ---------- */
  // The header link for the section being read gets aria-current="location", so the
  // navigation shows where you are in the page. Sections are watched with an
  // IntersectionObserver (no scroll handler); the active one is the last whose top has
  // passed a line just under the header.
  function initNavSpy() {
    var links = $$('.nav-list a[href^="#"]');
    if (!links.length || !('IntersectionObserver' in window)) return;
    var map = links.map(function (a) { return { a: a, el: hashTarget(a.getAttribute('href')) }; })
      .filter(function (m) { return m.el; });
    var update = function () {
      var line = headerOffset() + 8, current = null;
      map.forEach(function (m) { if (m.el.getBoundingClientRect().top <= line) current = m; });
      // Past the last section (pre-qualification), at the contact form: nothing is current.
      var contact = $('#contact');
      if (contact && contact.getBoundingClientRect().top <= line) current = null;
      map.forEach(function (m) {
        if (m === current) m.a.setAttribute('aria-current', 'location');
        else m.a.removeAttribute('aria-current');
      });
    };
    var io = new IntersectionObserver(update, { rootMargin: '-' + Math.round(headerOffset()) + 'px 0px 0px 0px', threshold: [0, 1] });
    map.forEach(function (m) { io.observe(m.el); });
    var contact = $('#contact'); if (contact) io.observe(contact);
    update();
  }

  /* ---------- 10. delivery stages: progress along the track ---------- */
  // The line joining the five stages fills as the list scrolls through the viewport, and
  // each stage's marker turns solid once the fill reaches it (--fill and .is-reached). Without JavaScript, or with
  // reduced motion, the track is shown complete (CSS default), so nothing depends on this.
  function initStages() {
    var list = $('.stages');
    if (!list || reduceMotion || !('IntersectionObserver' in window)) return;
    var stages = $$('.stage', list), ticking = false, active = false;
    list.setAttribute('data-progress', '');
    var update = function () {
      ticking = false;
      var r = list.getBoundingClientRect(), vh = window.innerHeight;
      // 0 when the top of the list is 80% down the viewport, 1 when its bottom reaches 55%.
      var p = (vh * 0.8 - r.top) / Math.max(1, r.height + vh * 0.25);
      p = Math.min(1, Math.max(0, p));
      // Spread across the n-1 joins: stage i's line is full once p * (n-1) passes i + 1.
      var at = p * (stages.length - 1);
      stages.forEach(function (s, i) {
        s.style.setProperty('--fill', Math.min(1, Math.max(0, at - i)).toFixed(3));
        s.classList.toggle('is-reached', at >= i - 0.001);
      });
    };
    var onScroll = function () { if (active && !ticking) { ticking = true; requestAnimationFrame(update); } };
    new IntersectionObserver(function (entries) {
      active = entries[0].isIntersecting;
      if (active) update();
    }).observe(list);
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    update();
  }

  /* ---------- 8. enquiry form ----------
     Checks the fields in place, then sends the form in the background to WordPress
     (admin-post.php, see inc/enquiry.php) and shows the answer under the button.
     Without JavaScript the browser's own validation applies and the form posts normally. */
  function initForm() {
    var form = $('#enquiry');
    if (!form) return;
    form.setAttribute('novalidate', '');
    var status = $('#form-status');
    var button = $('button[type="submit"]', form);
    var fields = $$('input, select, textarea', form).filter(function (f) { return f.type !== 'hidden' && f.name !== 'website'; });
    var opened = Date.now();
    var sending = false;
    // Back from a no-JavaScript submission (?enquiry=sent): the message is already on the
    // page, so drop the parameter to stop it reappearing on reload or in a shared link.
    if (/[?&]enquiry=/.test(location.search)) {
      try {
        var rest = location.search.replace(/^\?/, '').split('&').filter(function (p) { return p && p.indexOf('enquiry=') !== 0; });
        history.replaceState(history.state, '', location.pathname + (rest.length ? '?' + rest.join('&') : '') + location.hash);
      } catch (_) { /* sandboxed viewers */ }
    }

    function setError(f, msg) {
      var err = document.getElementById(f.id + '-error');
      if (err) err.textContent = msg;
      if (f.required) f.setAttribute('aria-invalid', msg ? 'true' : 'false');
    }
    // The same format rules as the server (aaykay_enquiry_check_formats in inc/enquiry.php),
    // so mistakes are pointed out before sending. Keep the two in step.
    var letters = function (v) { return (v.match(/\p{L}/gu) || []).length; };
    var rules = {
      name: function (v) { return /^\p{L}[\p{L}\p{M}\s.'’-]*$/u.test(v) && v.length >= 2 ? '' : 'Please use letters only for your name (no numbers or symbols).'; },
      company: function (v) { return /^[\p{L}\p{N}][\p{L}\p{M}\p{N}\s&.,()'’\/+-]*$/u.test(v) && letters(v) >= 2 ? '' : 'Please enter your company’s name.'; },
      email: function (v) { return /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(v) ? '' : 'Please enter an email address like name@company.com.'; },
      phone: function (v) { var d = v.replace(/\D/g, '').length; return /^\+?[\d\s()-]+$/.test(v) && d >= 7 && d <= 15 ? '' : 'Please enter a phone number using digits only, e.g. +91 98480 12345.'; },
      city: function (v) { return /^\p{L}[\p{L}\p{M}\s.'’-]*$/u.test(v) ? '' : 'Please use letters only for the city.'; },
      message: function (v) { return v.length >= 10 && letters(v) >= 5 ? '' : 'Please describe the scope and timeline in a few words.'; }
    };
    function check(f) {
      var msg = '';
      var v = f.value.trim();
      if (f.validity.valueMissing) msg = 'Please enter your ' + (f.getAttribute('data-label') || 'details') + '.';
      else if (v && rules[f.name]) msg = rules[f.name](v);
      setError(f, msg);
      return !msg;
    }
    // Buttons such as "Request pre-qualification documents" choose a project type.
    document.addEventListener('click', function (e) {
      var a = e.target.closest && e.target.closest('[data-enquiry-type]');
      var select = form.elements.type;
      if (!a || !select) return;
      var want = a.getAttribute('data-enquiry-type');
      for (var i = 0; i < select.options.length; i++) {
        if (select.options[i].text === want) { select.selectedIndex = i; break; }
      }
    });
    // Phone: only digits, spaces, + ( ) - can be typed or pasted.
    var phone = form.elements.phone;
    if (phone) phone.addEventListener('input', function () {
      var clean = phone.value.replace(/[^\d\s()+-]/g, '');
      if (clean !== phone.value) phone.value = clean;
    });
    function fallback() {
      return 'Please email ' + form.getAttribute('data-email') + ' or call ' + form.getAttribute('data-phone') + '.';
    }
    fields.forEach(function (f) {
      f.addEventListener('blur', function () { if (f.value) check(f); });
      f.addEventListener('input', function () { if (f.getAttribute('aria-invalid') === 'true') check(f); });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (sending) return;
      var results = fields.map(check);
      if (results.indexOf(false) !== -1) {
        var invalid = fields.filter(function (f) { return f.getAttribute('aria-invalid') === 'true'; });
        var missing = invalid.some(function (f) { return f.validity.valueMissing; });
        if (invalid[0]) invalid[0].focus();
        status.textContent = missing
          ? 'Some required details are missing. They are marked above.'
          : 'Please check the details marked above.';
        return;
      }
      if (!window.fetch || !window.FormData) { form.submit(); return; }

      form.elements.elapsed.value = String(Date.now() - opened);
      sending = true;
      // aria-disabled rather than disabled, so keyboard focus stays on the button.
      button.setAttribute('aria-disabled', 'true');
      status.textContent = 'Sending your enquiry…';
      // getAttribute: form.action would return the hidden input named "action", not the URL.
      fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then(function (res) { return res.json().catch(function () { return { ok: false }; }); })
        .then(function (data) {
          if (data && data.ok) {
            form.reset();
            fields.forEach(function (f) { setError(f, ''); });
            opened = Date.now();
            status.textContent = data.message;
            return;
          }
          var errors = (data && data.errors) || {};
          var first = null;
          fields.forEach(function (f) {
            if (errors[f.name]) { setError(f, errors[f.name]); if (!first) first = f; }
          });
          if (first) first.focus();
          status.textContent = (data && data.message) || ('Sorry, your enquiry could not be sent. ' + fallback());
        })
        .catch(function () {
          status.textContent = 'Sorry, your enquiry could not be sent. Check your connection and try again, or ' + fallback().charAt(0).toLowerCase() + fallback().slice(1);
        })
        .then(function () {
          sending = false;
          button.removeAttribute('aria-disabled');
        });
    });
  }

  function start() {
    safely('motion', initMotion);
    var updateHeader = safely('header', initHeader) || function () {};
    safely('menu', function () { initMenu(updateHeader); });
    safely('record', initRecord); // before positioning: it changes the page height
    safely('scroll position', initScrollPosition);
    safely('anchors', initAnchors);
    safely('lightbox', initLightbox);
    safely('copy', initCopy);
    safely('nav spy', initNavSpy);
    safely('stages', initStages);
    safely('form', initForm);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
