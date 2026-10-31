// Navigation and interactive parts: menus, logo, skip link, contact links, project list
// filters, image viewer, slideshow, Back/Forward.
const { test, expect } = require('@playwright/test');
const { PAGES } = require('./helpers');

// The header shows the menu button instead of the menu at 1240 px and below (site.css,
// NAV_BREAKPOINT in site.js), which includes tablets: decide by width, not device name.
const isMobile = (testInfo) => (testInfo.project.use.viewport || { width: 1366 }).width <= 1240;
// WebKit, like Safari, leaves links out of the Tab order unless the user turns it on, so
// Tab-key tests run in Chromium and Firefox only.
const noTabToLinks = (browserName) => browserName === 'webkit';

test.describe('navigation', () => {
  test('every menu page is reachable from the menu, and the logo goes home @smoke', async ({ page }, testInfo) => {
    await page.goto('/careers/');
    for (const p of PAGES.slice(1)) {
      if (isMobile(testInfo)) {
        await page.getByRole('button', { name: 'Menu' }).click();
        await page.locator('#mobile-menu').getByRole('link', { name: p.name === 'Contact Us' ? /Contact/ : p.name, exact: p.name !== 'Contact Us' }).click();
      } else if (p.name === 'Contact Us') {
        await page.locator('.header-cta').click();
      } else {
        await page.locator('.site-nav').getByRole('link', { name: p.name, exact: true }).click();
      }
      await expect(page).toHaveURL(new RegExp(p.path.replace(/\//g, '\\/') + '$'));
      await expect(page.locator('h1')).toHaveCount(1);
    }
    await page.locator('.site-header .brand').click();
    await expect(page).toHaveURL(/\/$/);
  });

  test('refreshing a nested page works (no 404 on reload)', async ({ page }) => {
    await page.goto('/projects/?sector=healthcare');
    const res = await page.reload();
    expect(res.status()).toBe(200);
    await expect(page.locator('#record-table')).toBeVisible();
  });

  test('skip link moves focus to the main content', async ({ page, browserName }) => {
    test.skip(noTabToLinks(browserName), 'WebKit does not Tab to links by default');
    await page.goto('/');
    await page.keyboard.press('Tab');
    const skip = page.locator('.skip-link');
    await expect(skip).toBeFocused();
    await expect(skip).toBeVisible();
    await page.keyboard.press('Enter');
    // site.js keeps the address clean (no #main), and moves focus to the target.
    await expect(page.locator('#main')).toBeFocused();
  });

  test('phone numbers and email addresses are tel: and mailto: links', async ({ page }) => {
    await page.goto('/contact-us/');
    const tel = page.locator('main a[href^="tel:"]');
    expect(await tel.count()).toBeGreaterThanOrEqual(1);
    for (const href of await tel.evaluateAll((as) => as.map((a) => a.getAttribute('href')))) expect(href).toMatch(/^tel:\+\d{8,15}$/);
    const mail = page.locator('main a[href^="mailto:"]');
    expect(await mail.count()).toBeGreaterThanOrEqual(1);
    await expect(page.locator('footer a[href^="tel:"]').first()).toBeVisible();
    await expect(page.locator('footer a[href^="mailto:"]').first()).toBeVisible();
  });

  test('external links open safely', async ({ page }) => {
    for (const p of PAGES) {
      await page.goto(p.path);
      const unsafe = await page.locator('a[target="_blank"]').evaluateAll((as) => as
        .filter((a) => !/noopener|noreferrer/.test(a.getAttribute('rel') || '')).map((a) => a.href));
      expect(unsafe, p.path).toEqual([]);
    }
  });
});

test.describe('phone menu', () => {
  test.beforeEach(async ({}, testInfo) => { test.skip(!isMobile(testInfo), 'phone layout only'); });

  test('opens, traps focus, closes with Escape and returns focus', async ({ page, browserName }) => {
    test.skip(noTabToLinks(browserName), 'WebKit does not Tab to links by default');
    await page.goto('/');
    const btn = page.getByRole('button', { name: 'Menu' });
    await expect(btn).toHaveAttribute('aria-expanded', 'false');
    await btn.click();
    await expect(btn).toHaveAttribute('aria-expanded', 'true');
    await expect(page.locator('#mobile-menu')).toBeVisible();
    await expect(page.locator('main')).toHaveAttribute('inert', '');
    // Tab many times: focus never leaves the menu or its button.
    for (let i = 0; i < 15; i++) {
      await page.keyboard.press('Tab');
      const inside = await page.evaluate(() => !!document.activeElement.closest('#mobile-menu, .menu-btn'));
      expect(inside).toBe(true);
    }
    await page.keyboard.press('Escape');
    await expect(page.locator('#mobile-menu')).toBeHidden();
    await expect(btn).toBeFocused();
    await expect(page.locator('main')).not.toHaveAttribute('inert', '');
  });

  test('no horizontal scrolling while the menu is open', async ({ page }) => {
    await page.goto('/');
    await page.getByRole('button', { name: 'Menu' }).click();
    const over = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    expect(over).toBeLessThanOrEqual(0);
  });
});

test.describe('desktop dropdowns', () => {
  test.beforeEach(async ({}, testInfo) => { test.skip(isMobile(testInfo), 'desktop header only'); });

  test('a dropdown opens with the keyboard and Escape closes it', async ({ page }) => {
    await page.goto('/quality-safety/');
    const btn = page.locator('.site-nav .has-sub .sub-btn').first();
    await btn.focus();
    await page.keyboard.press('Enter');
    await expect(btn).toHaveAttribute('aria-expanded', 'true');
    const first = page.locator('.site-nav .has-sub').first().locator('.sub-menu a').first();
    await expect(first).toBeFocused();
    await page.keyboard.press('Escape');
    await expect(btn).toHaveAttribute('aria-expanded', 'false');
    await expect(btn).toBeFocused();
  });

  test('a dropdown entry goes to its section on the other page', async ({ page }) => {
    await page.goto('/careers/');
    const projects = page.locator('.site-nav .has-sub').filter({ has: page.getByRole('link', { name: 'Projects', exact: true }) });
    await projects.locator('.sub-btn').click();
    const link = projects.locator('.sub-menu a').first();
    const href = await link.getAttribute('href');
    await link.click();
    const id = href.split('#')[1];
    await expect(page).toHaveURL(/\/projects\//);
    await expect(page.locator('#' + id)).toBeInViewport();
  });
});

test.describe('project list', () => {
  test('sector filters, counts and "show all" work, and the filter is in the address', async ({ page }) => {
    await page.goto('/projects/');
    const rows = page.locator('#record-table tbody tr');
    const total = await rows.count();
    expect(total).toBeGreaterThan(12);
    await expect(page.locator('#record-table tbody tr:visible')).toHaveCount(12);
    await page.locator('#record-more').click();
    await expect(page.locator('#record-table tbody tr:visible')).toHaveCount(total);

    const chip = page.locator('.chip[data-filter]').nth(1);
    const key = await chip.getAttribute('data-filter');
    const expected = Number(await chip.locator('.chip-count').innerText());
    await chip.click();
    await expect(chip).toHaveAttribute('aria-pressed', 'true');
    await expect(page.locator('#record-table tbody tr:visible')).toHaveCount(expected);
    await expect(page.locator(`#record-table tbody tr:visible:not([data-sector="${key}"])`)).toHaveCount(0);
    await expect(page).toHaveURL(new RegExp('sector=' + key));
    await expect(page.locator('#record-status')).toContainText(`${expected} of ${expected}`);

    // A shared filtered link opens filtered.
    await page.goto('/projects/?sector=' + key);
    await expect(page.locator('#record-table tbody tr:visible')).toHaveCount(expected);
  });

  test('a sector row filters the list and Back restores the previous view', async ({ page }) => {
    await page.goto('/projects/');
    const row = page.locator('.sector-row[data-filter]').first();
    const key = await row.getAttribute('data-filter');
    await row.click();
    await expect(page).toHaveURL(new RegExp('sector=' + key));
    await page.goBack();
    await expect(page).not.toHaveURL(/sector=/);
    await expect(page.locator('.chip[data-filter="all"]')).toHaveAttribute('aria-pressed', 'true');
  });

  test('the list is complete without JavaScript', async ({ browser }) => {
    const ctx = await browser.newContext({ javaScriptEnabled: false });
    const page = await ctx.newPage();
    await page.goto('/projects/');
    const n = await page.locator('#record-table tbody tr').count();
    expect(n).toBeGreaterThan(12);
    await expect(page.locator('#record-table tbody tr:visible')).toHaveCount(n);
    await ctx.close();
  });
});

test.describe('image viewer', () => {
  test('opens, steps through a set with the arrow keys and closes with Escape', async ({ page }) => {
    await page.goto('/quality-safety/');
    const trigger = page.locator('[data-gallery] [data-lightbox]').first();
    await trigger.scrollIntoViewIfNeeded();
    await trigger.click();
    const dlg = page.locator('#lightbox');
    await expect(dlg).toBeVisible();
    await expect(dlg.locator('.lightbox-count')).toHaveText(/^1 of \d+$/);
    await page.keyboard.press('ArrowRight');
    await expect(dlg.locator('.lightbox-count')).toHaveText(/^2 of \d+$/);
    const loaded = await dlg.locator('img').evaluate((img) => new Promise((r) => (img.complete ? r(img.naturalWidth) : img.addEventListener('load', () => r(img.naturalWidth)))));
    expect(loaded).toBeGreaterThan(0);
    await page.keyboard.press('Escape');
    await expect(dlg).toBeHidden();
  });
});

test.describe('slideshow', () => {
  test('arrows change the photo and pause stops it', async ({ page }) => {
    await page.goto('/');
    const controls = page.locator('.hero-controls');
    await expect(controls).toBeVisible();
    await page.locator('.hero-arrow[data-step="1"]').click();
    await expect(page.locator('.hero-dot').nth(1)).toHaveAttribute('aria-current', 'true');
    const pause = page.locator('.hero-pause');
    await pause.click();
    await expect(pause).toHaveAttribute('aria-label', 'Play the slideshow');
    await expect(controls).not.toHaveClass(/is-running/);
  });

  test('does not play by itself with reduced motion', async ({ browser }) => {
    const ctx = await browser.newContext({ reducedMotion: 'reduce' });
    const page = await ctx.newPage();
    await page.goto('/');
    await expect(page.locator('.hero-controls')).not.toHaveClass(/is-running/);
    await expect(page.locator('.hero-pause')).toHaveAttribute('aria-label', 'Play the slideshow');
    await ctx.close();
  });
});
