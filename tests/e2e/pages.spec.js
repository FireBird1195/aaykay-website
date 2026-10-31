// Every page loads cleanly: status, one h1, unique title and description, canonical, social
// tags, no console errors, no failed requests, no broken images, security headers, no cookies.
const { test, expect } = require('@playwright/test');
const { PAGES, watch } = require('./helpers');

test.describe('pages @smoke', () => {
  const titles = new Map();
  const descriptions = new Map();

  for (const p of PAGES) {
    test(`${p.name} loads cleanly`, async ({ page, context }) => {
      const problems = watch(page);
      const res = await page.goto(p.path, { waitUntil: 'load' });
      expect(res.status(), 'HTTP status').toBe(200);

      // Headers set by the theme (inc/setup.php).
      const h = res.headers();
      expect(h['x-content-type-options']).toBe('nosniff');
      expect(h['x-frame-options']).toBe('SAMEORIGIN');
      expect(h['referrer-policy']).toBe('strict-origin-when-cross-origin');
      expect(h['content-security-policy']).toContain("frame-ancestors 'self'");
      expect(h['x-powered-by'] || '', 'no PHP version disclosed by WordPress').not.toMatch(/WordPress/i);

      await expect(page.locator('h1')).toHaveCount(1);
      const title = await page.title();
      expect(title.length).toBeGreaterThan(5);
      titles.set(title, (titles.get(title) || 0) + 1);

      const desc = await page.locator('meta[name="description"]').getAttribute('content');
      expect(desc && desc.length, 'meta description').toBeGreaterThan(50);
      descriptions.set(desc, (descriptions.get(desc) || 0) + 1);
      await expect(page.locator('link[rel="canonical"]')).toHaveCount(1);
      await expect(page.locator('meta[property="og:title"]')).toHaveCount(1);
      await expect(page.locator('meta[property="og:image"]')).toHaveCount(1);
      await expect(page.locator('html')).toHaveAttribute('lang', 'en-IN');

      // Scroll through so lazy images load, then check none is broken.
      await page.evaluate(async () => {
        for (let y = 0; y < document.body.scrollHeight; y += 500) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 40)); }
      });
      await page.waitForLoadState('networkidle');
      const broken = await page.evaluate(() => [...document.images]
        .filter((i) => i.getAttribute('src') && i.complete && i.naturalWidth === 0).map((i) => i.currentSrc || i.src));
      expect(broken, 'broken images').toEqual([]);

      // Every image a visitor sees has alt text (empty alt only on decorative ones).
      const noAlt = await page.evaluate(() => [...document.images].filter((i) => !i.hasAttribute('alt')).map((i) => i.src));
      expect(noAlt, 'images without alt').toEqual([]);

      // No cookies for visitors (nothing to consent to).
      expect(await context.cookies()).toEqual([]);
      expect(problems, 'console errors / failed requests').toEqual([]);
    });
  }

  test('titles and descriptions are unique', async () => {
    test.skip(titles.size === 0, 'runs after the page tests');
    for (const [t, n] of titles) expect(n, `title used twice: ${t}`).toBe(1);
    for (const [d, n] of descriptions) expect(n, `description used twice: ${d}`).toBe(1);
  });

  test('an unknown address is a real 404 with a way back', async ({ page }) => {
    const res = await page.goto('/this-page-does-not-exist/');
    expect(res.status()).toBe(404);
    await expect(page.locator('h1')).toHaveText(/not found/i);
    const links = page.locator('main .page-tabs a');
    expect(await links.count()).toBeGreaterThanOrEqual(5);
    await links.filter({ hasText: 'Projects' }).click();
    await expect(page).toHaveURL(/\/projects\/$/);
  });

  test('search, feeds, author and attachment addresses do not expose anything', async ({ request }) => {
    expect((await request.get('/?s=test')).status()).toBe(404);
    expect((await request.get('/feed/')).status()).toBe(404);
    const author = await request.get('/?author=1', { maxRedirects: 0 });
    expect(author.status()).toBe(301);
    expect((await request.get('/wp-json/wp/v2/users')).status()).toBe(404);
    expect((await request.get('/xmlrpc.php', { method: 'POST' })).status()).not.toBe(200);
  });

  test('developer files in the theme are not downloadable', async ({ request }) => {
    for (const f of ['seed/content.json', 'README.md', 'assets/THIRD-PARTY.md']) {
      expect((await request.get('/wp-content/themes/aaykay/' + f)).status(), f).toBe(403);
    }
  });

  test('robots.txt and the sitemap answer', async ({ request }) => {
    expect((await request.get('/robots.txt')).status()).toBe(200);
    const sm = await request.get('/wp-sitemap.xml');
    expect(sm.status()).toBe(200);
    expect(await sm.text()).not.toContain('wp-sitemap-users');
  });

  test('health endpoint answers ok and is not cached', async ({ request }) => {
    const r = await request.get('/wp-json/aaykay/v1/health');
    expect(r.status()).toBe(200);
    expect(await r.json()).toEqual({ status: 'ok' });
    expect(r.headers()['cache-control']).toContain('no-store');
  });
});
