// Layout at the widths people use, and accessibility (axe-core, WCAG 2.2 A/AA) on every page.
const { test, expect } = require('@playwright/test');
const { AxeBuilder } = require('@axe-core/playwright');
const { PAGES } = require('./helpers');

const WIDTHS = [
  [320, 640], [360, 780], [390, 844], [430, 932], // phones
  [768, 1024], [1024, 768], // tablet portrait and landscape
  [1366, 768], [1920, 1080], // laptop, wide desktop
];

test.describe('responsive layout', () => {
  test.beforeEach(async ({}, testInfo) => { test.skip(testInfo.project.name !== 'desktop-chromium', 'widths are set explicitly; one browser is enough'); });

  for (const [w, h] of WIDTHS) {
    test(`no sideways scrolling and nothing off-screen at ${w}px`, async ({ page }) => {
      await page.setViewportSize({ width: w, height: h });
      for (const p of PAGES) {
        await page.goto(p.path);
        const r = await page.evaluate(() => {
          const vw = document.documentElement.clientWidth;
          const off = [];
          document.querySelectorAll('main *, header *, footer *').forEach((el) => {
            const b = el.getBoundingClientRect();
            if (!b.width || el.closest('[hidden], dialog:not([open]), .visually-hidden')) return;
            // Content inside a box that clips or scrolls sideways (a photo crop, the project
            // table's scroll area) is not "off-screen": the box itself is checked instead.
            for (let a = el.parentElement; a && a !== document.body; a = a.parentElement) {
              if (/hidden|clip|auto|scroll/.test(getComputedStyle(a).overflowX)) return;
            }
            if (b.right > vw + 1 || b.left < -1) off.push(el.tagName.toLowerCase() + '.' + String(el.className).split(' ')[0] + ' ' + Math.round(b.left) + '…' + Math.round(b.right));
          });
          return { over: document.documentElement.scrollWidth - vw, off: off.slice(0, 5) };
        });
        expect(r.over, `${p.path} scrolls sideways at ${w}px`).toBeLessThanOrEqual(0);
        expect(r.off, `${p.path} has content off-screen at ${w}px`).toEqual([]);
      }
    });
  }

  test('touch targets are at least 24×24 px on a phone (WCAG 2.5.8)', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    for (const p of PAGES) {
      await page.goto(p.path);
      const small = await page.evaluate(() => [...document.querySelectorAll('a, button, input, select, textarea, summary')]
        .filter((el) => el.offsetParent !== null && !el.closest('p, li, dd, td, address, .prose') && getComputedStyle(el).display !== 'inline')
        .map((el) => ({ el, b: el.getBoundingClientRect() }))
        .filter(({ b }) => b.width > 0 && (b.width < 24 || b.height < 24))
        .map(({ el, b }) => `${el.tagName} "${(el.textContent || el.getAttribute('aria-label') || '').trim().slice(0, 30)}" ${Math.round(b.width)}×${Math.round(b.height)}`));
      expect(small, p.path).toEqual([]);
    }
  });
});

test.describe('accessibility', () => {
  for (const p of PAGES.concat([{ path: '/no-such-page/', name: 'Not found' }])) {
    test(`${p.name}: no axe violations (WCAG 2.2 AA)`, async ({ page }) => {
      await page.goto(p.path);
      // Reveal everything that animates in, so axe sees final colours.
      await page.evaluate(async () => {
        for (let y = 0; y < document.body.scrollHeight; y += 400) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 30)); }
        window.scrollTo(0, 0);
      });
      await page.waitForTimeout(1200);
      const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze();
      const summary = results.violations.map((v) => `${v.id} (${v.impact}): ${v.nodes.slice(0, 3).map((n) => n.target.join(' ')).join(' | ')}`);
      expect(summary).toEqual([]);
    });
  }

  test('keyboard focus is always visible', async ({ page, browserName }) => {
    test.skip(browserName === 'webkit', 'WebKit does not Tab to links by default');
    await page.goto('/contact-us/');
    for (let i = 0; i < 25; i++) {
      await page.keyboard.press('Tab');
      const f = await page.evaluate(() => {
        const el = document.activeElement;
        if (!el || el === document.body) return { ok: true };
        const cs = getComputedStyle(el);
        const visible = (cs.outlineStyle !== 'none' && parseFloat(cs.outlineWidth) > 0) || cs.boxShadow !== 'none';
        return { ok: visible, what: el.tagName + ' ' + (el.textContent || el.name || '').trim().slice(0, 30) };
      });
      expect(f.ok, 'no visible focus on ' + f.what).toBe(true);
    }
  });

  test('form fields have labels and errors are tied to them', async ({ page }) => {
    await page.goto('/contact-us/');
    const unlabeled = await page.evaluate(() => [...document.querySelectorAll('#enquiry input:not([type=hidden]), #enquiry select, #enquiry textarea')]
      .filter((f) => f.name !== 'website' && !(f.labels && f.labels.length)).map((f) => f.name));
    expect(unlabeled).toEqual([]);
    const describedBy = await page.locator('#f-email').getAttribute('aria-describedby');
    expect(describedBy).toContain('f-email-error');
    await expect(page.locator('#form-status')).toHaveAttribute('role', 'status');
  });
});
