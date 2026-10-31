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
  // Width at and below which the header shows the menu button instead of the menu
  // (keep in step with the same number in site.css).
  var NAV_BREAKPOINT = 1240;

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
    var behind = [$('.skip-link'), $('.brand', header), $('.site-nav', header), $('.header-cta', header), $('main'), $('.site-footer'), $('.brochure-aside')]
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
      var items = [btn].concat($$('a, button', menu).filter(function (el) { return el.offsetParent !== null; }));
      var i = items.indexOf(document.activeElement);
      if (e.shiftKey && i <= 0) { e.preventDefault(); items[items.length - 1].focus(); }
      else if (!e.shiftKey && i === items.length - 1) { e.preventDefault(); items[0].focus(); }
    });
    window.matchMedia('(min-width: ' + (NAV_BREAKPOINT + 1) + 'px)').addEventListener('change', function (e) {
      if (e.matches && isOpen()) close(false);
    });
  }

  /* ---------- 4. in-page links ---------- */
  function initAnchors() {
    document.addEventListener('click', function (e) {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey) return;
      // "#record", or this page's own address + "#record" (the menu dropdowns).
      var a = e.target.closest('a[href*="#"]');
      if (!a || a.target === '_blank') return;
      if (a.getAttribute('href').charAt(0) !== '#' && (a.host !== location.host || a.pathname !== location.pathname)) return;
      var id = a.hash.slice(1);
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

  /* ---------- 6. document and photo viewer ----------
     A thumbnail with data-lightbox opens its full-size image in the <dialog> (footer.php).
     Thumbnails inside the same [data-gallery] element form a set: the viewer shows
     "3 of 8" and previous/next buttons, and the arrow keys step through the set (Escape
     closes, as for every <dialog>). Without JavaScript each link opens the image itself. */
  function initLightbox() {
    var dlg = $('#lightbox');
    if (!dlg || typeof dlg.showModal !== 'function') return;
    var img = $('img', dlg), cap = $('.lightbox-caption', dlg), count = $('.lightbox-count', dlg);
    var prev = $('.lightbox-prev', dlg), next = $('.lightbox-next', dlg);
    var set = [], at = 0;
    function show(i) {
      at = (i + set.length) % set.length;
      var t = set[at];
      img.src = t.getAttribute('data-lightbox');
      img.alt = (t.querySelector('img') || {}).alt || '';
      cap.textContent = t.getAttribute('data-caption') || '';
      var many = set.length > 1;
      prev.hidden = next.hidden = count.hidden = !many;
      count.textContent = many ? (at + 1) + ' of ' + set.length : '';
    }
    $$('[data-lightbox]').forEach(function (trigger) {
      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        var group = trigger.closest('[data-gallery]');
        set = group ? $$('[data-lightbox]', group) : [trigger];
        show(set.indexOf(trigger));
        dlg.showModal();
      });
    });
    prev.addEventListener('click', function () { show(at - 1); });
    next.addEventListener('click', function () { show(at + 1); });
    dlg.addEventListener('keydown', function (e) {
      if (set.length < 2) return;
      if (e.key === 'ArrowLeft') { e.preventDefault(); show(at - 1); }
      if (e.key === 'ArrowRight') { e.preventDefault(); show(at + 1); }
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

  /* ---------- 9. current section in the navigation ----------
     While a section of this page is on screen, its menu entry is marked: the dropdown
     link gets aria-current="location" and its menu item (About Us, Projects) is underlined
     (data-here). On the home page, Home stays marked over the opening screen and About Us
     takes over from the first About Us section. Sections are those the menu links to on
     this page; the current one is the last whose top has passed a line under the header.
     Scroll events are batched with requestAnimationFrame. */
  function initNavSpy() {
    var nav = $('.nav-list');
    if (!nav) return;
    var here = location.origin + location.pathname;
    var links = $$('a[href*="#"]', nav).filter(function (a) {
      return a.href.split('#')[0] === here && hashTarget(a.hash);
    });
    if (!links.length) return;
    var items = links.map(function (a) {
      return { a: a, el: hashTarget(a.hash), li: a.closest('.nav-list > li') };
    });
    var ticking = false;
    var update = function () {
      ticking = false;
      var line = headerOffset() + 24, current = null;
      items.forEach(function (m) { if (m.el.getBoundingClientRect().top <= line) current = m; });
      items.forEach(function (m) {
        var sub = m.a.closest('.sub-menu');
        if (current && m.el === current.el && sub) m.a.setAttribute('aria-current', 'location');
        else if (sub) m.a.removeAttribute('aria-current');
      });
      $$(':scope > li', nav).forEach(function (li) {
        if (current && li === current.li) li.setAttribute('data-here', '');
        else li.removeAttribute('data-here');
      });
    };
    var onScroll = function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    update();
  }

  /* ---------- 10. delivery stages: progress along the track ---------- */
  // The line joining the five stages fills as the list scrolls through the viewport, and
  // each stage's marker turns solid once the fill reaches it (--fill and .is-reached). Without JavaScript, or with
  // reduced motion, the track is shown complete (CSS default), so nothing depends on this.
  function initStages() {
    if (reduceMotion || !('IntersectionObserver' in window)) return;
    $$('.stages').forEach(initStageList);
  }
  function initStageList(list) {
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
      // Never leave "Sending…" on screen for ever: give up after 30 seconds (the server
      // saves the enquiry first, so it may still have arrived; the message says so).
      var timedOut = false;
      var ctrl = window.AbortController ? new AbortController() : null;
      var timer = setTimeout(function () { timedOut = true; if (ctrl) ctrl.abort(); }, 30000);
      // getAttribute: form.action would return the hidden input named "action", not the URL.
      fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, credentials: 'same-origin', signal: ctrl ? ctrl.signal : undefined })
        .then(function (res) {
          if (timedOut) throw new Error('timeout');
          // A timeout while the answer is still arriving is a timeout, not a refusal.
          return res.json().catch(function (err) { if (timedOut) throw err; return { ok: false }; });
        })
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
          status.textContent = timedOut
            ? 'This is taking longer than it should, so we stopped waiting. Your enquiry may still have reached us. If you do not hear back, ' + fallback().charAt(0).toLowerCase() + fallback().slice(1)
            : 'Sorry, your enquiry could not be sent. Check your connection and try again, or ' + fallback().charAt(0).toLowerCase() + fallback().slice(1);
        })
        .then(function () {
          clearTimeout(timer);
          sending = false;
          button.removeAttribute('aria-disabled');
        });
    });
  }


  /* ---------- 11. menu dropdowns ----------
     Desktop header: a page's dropdown opens on hover (CSS) and with its arrow button, for
     keyboard and touch. The button says whether it is open (aria-expanded); Escape closes
     the open dropdown and returns focus to its button; a click or focus elsewhere closes
     it. Without JavaScript the button stays hidden and the page links work as usual. */
  function initDropdowns() {
    var items = $$('.site-nav .has-sub');
    if (!items.length) return;
    function set(li, open) {
      li.classList.toggle('is-open', open);
      var b = $('.sub-btn', li);
      if (b) b.setAttribute('aria-expanded', String(open));
    }
    function closeAll(except) { items.forEach(function (li) { if (li !== except) set(li, false); }); }
    items.forEach(function (li) {
      var b = $('.sub-btn', li);
      if (!b) return;
      b.hidden = false;
      b.addEventListener('click', function () {
        var open = !li.classList.contains('is-open');
        closeAll(li);
        set(li, open);
        li.classList.remove('is-dismissed');
        if (open) { var first = $('.sub-menu a', li); if (first && keyboardUsed) first.focus(); }
      });
      // After Escape the panel stays shut even under the pointer, until the pointer leaves.
      li.addEventListener('mouseleave', function () { li.classList.remove('is-dismissed'); });
      li.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (li.classList.contains('is-open') || li.matches(':hover')) {
          e.preventDefault();
          set(li, false);
          li.classList.add('is-dismissed');
          b.focus();
        }
      });
      li.addEventListener('focusout', function (e) {
        if (!li.contains(e.relatedTarget)) set(li, false);
      });
      $$('.sub-menu a', li).forEach(function (a) { a.addEventListener('click', function () { set(li, false); li.classList.add('is-dismissed'); }); });
    });
    // Whether the last button press came from the keyboard (then focus moves into the list).
    var keyboardUsed = false;
    document.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') keyboardUsed = true; }, true);
    document.addEventListener('pointerdown', function () { keyboardUsed = false; }, true);
    document.addEventListener('click', function (e) { if (!e.target.closest('.site-nav .has-sub')) closeAll(); });
  }

  /* ---------- 12. opening-screen slideshow ----------
     The photos cross-fade (CSS transition on .is-active). The controls are shown only here:
     previous and next arrows, a progress bar per photo (like Instagram stories) and a pause
     button (WCAG 2.2.2: anything that moves by itself can be paused). The bar of the photo
     on screen fills over its time on screen; the CSS animation (--hero-dur in site.css) is
     the clock: when it ends (animationend) the next photo is shown, so the bar and the photo
     stay together, and pausing pauses the fill. It does not start for visitors who ask for
     reduced motion (they step through the photos; the current bar is shown full), and it
     pauses while the tab is hidden or the keyboard is on the controls. */
  function initSlideshow() {
    var hero = $('.hero');
    var slides = hero ? $$('.hero-slide', hero) : [];
    var controls = hero ? $('.hero-controls', hero) : null;
    if (slides.length < 2 || !controls) return;
    var dots = $$('.hero-dot', controls), pauseBtn = $('.hero-pause', controls), caption = $('#hero-caption');
    var current = 0, playing = !reduceMotion, held = false;
    controls.hidden = false;
    if (reduceMotion) controls.classList.add('is-static');
    // Photos after the first carry data-src until needed (see hero.php).
    function load(slide) {
      var img = $('img[data-src]', slide);
      if (!img) return;
      if (img.getAttribute('data-srcset')) img.setAttribute('srcset', img.getAttribute('data-srcset'));
      img.setAttribute('src', img.getAttribute('data-src'));
      img.removeAttribute('data-src'); img.removeAttribute('data-srcset');
    }
    // The fill runs only while the slideshow is playing, the tab is visible and focus is not on the controls.
    function sync() {
      controls.classList.toggle('is-running', playing && !held && !document.hidden);
    }
    function show(i) {
      current = (i + slides.length) % slides.length;
      slides.forEach(function (s, k) {
        var on = k === current;
        s.classList.toggle('is-active', on);
        if (on) s.removeAttribute('aria-hidden'); else s.setAttribute('aria-hidden', 'true');
        if (on) load(s);
      });
      dots.forEach(function (d, k) {
        d.classList.toggle('is-done', k < current);
        d.removeAttribute('aria-current');
      });
      void controls.offsetWidth; // so the fill of the new bar starts from empty, even if it is the same bar
      dots[current].setAttribute('aria-current', 'true');
      if (caption) caption.textContent = slides[current].getAttribute('data-caption') || '';
    }
    function setPlaying(on) {
      playing = on;
      pauseBtn.setAttribute('data-state', on ? 'playing' : 'paused');
      pauseBtn.setAttribute('aria-label', on ? 'Pause the slideshow' : 'Play the slideshow');
      sync();
    }
    controls.addEventListener('animationend', function (e) {
      if (e.animationName === 'hero-fill') show(current + 1);
    });
    dots.forEach(function (d, k) { d.addEventListener('click', function () { show(k); }); });
    $$('.hero-arrow', controls).forEach(function (b) {
      b.addEventListener('click', function () { show(current + Number(b.getAttribute('data-step'))); });
    });
    pauseBtn.addEventListener('click', function () { setPlaying(!playing); });
    controls.addEventListener('focusin', function (e) { held = e.target.matches(':focus-visible'); sync(); }); // keyboard focus only; a mouse click does not stall the bar
    controls.addEventListener('focusout', function () { held = false; sync(); });
    document.addEventListener('visibilitychange', sync);
    // The other photos start loading once the page has loaded, so the first is never delayed.
    var loadRest = function () { slides.forEach(load); };
    if (document.readyState === 'complete') loadRest(); else window.addEventListener('load', loadRest);
    setPlaying(playing);
  }

  /* ---------- 13. careers: filter positions by domain ----------
     The chips are shown only here; without JavaScript every position is listed. */
  function initJobs() {
    var bar = $('.job-filters');
    if (!bar) return;
    var chips = $$('[data-domain]', bar), jobs = $$('.job'), count = $('#jobs-count');
    bar.hidden = false;
    if (count) count.setAttribute('aria-live', 'polite');
    chips.forEach(function (c) {
      c.addEventListener('click', function () {
        var want = c.getAttribute('data-domain'), n = 0;
        chips.forEach(function (x) { x.setAttribute('aria-pressed', String(x === c)); });
        jobs.forEach(function (j) {
          var show = want === 'all' || j.getAttribute('data-domain') === want;
          j.hidden = !show;
          if (show) n++;
        });
        if (count) count.textContent = n + (n === 1 ? ' position' : ' positions');
      });
    });
  }

  function start() {
    safely('motion', initMotion);
    var updateHeader = safely('header', initHeader) || function () {};
    safely('menu', function () { initMenu(updateHeader); });
    safely('dropdowns', initDropdowns);
    safely('slideshow', initSlideshow);
    safely('jobs', initJobs);
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
