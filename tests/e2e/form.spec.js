// The enquiry form, end to end: browser validation, the request, what the server stores,
// the message the visitor sees, and the failure paths. With WP_PATH set, the stored
// enquiry is checked in the database through WP-CLI.
const { test, expect } = require('@playwright/test');
const { wp, waitFillTime } = require('./helpers');

const unique = () => 'E2E ' + Date.now().toString(36);

async function fill(page, company) {
  const f = page.locator('#enquiry');
  await f.locator('#f-name').fill('Asha Rao');
  await f.locator('#f-company').fill(company);
  await f.locator('#f-email').fill('asha.rao@example.com');
  await f.locator('#f-phone').fill('+91 98480 12345');
  await f.locator('#f-type').selectOption({ index: 1 });
  await f.locator('#f-city').fill('Hyderabad');
  await f.locator('#f-message').fill('New hospital block: LT panels, busduct and lighting, start Q1 2027.');
}

function stored(company) {
  const out = wp(['post', 'list', '--post_type=aaykay_enquiry', '--post_status=any', '--format=json', '--fields=ID,post_title']);
  if (out === null) return null;
  return JSON.parse(out).filter((p) => p.post_title.startsWith(company + ' '));
}

test.describe('enquiry form', () => {
  test.beforeAll(() => { wp(['transient', 'delete', '--all']); }); // fresh per-connection limits

  test('shows field errors and sends nothing while details are missing or wrong', async ({ page }) => {
    let posted = false;
    page.on('request', (r) => { if (r.method() === 'POST' && r.url().includes('admin-post.php')) posted = true; });
    await page.goto('/contact-us/');
    await page.getByRole('button', { name: /Send enquiry/ }).click();
    await expect(page.locator('#form-status')).toHaveText(/required details are missing/);
    await expect(page.locator('#f-name')).toBeFocused();
    await expect(page.locator('#f-name-error')).toHaveText(/enter your name/);
    await expect(page.locator('#f-name')).toHaveAttribute('aria-invalid', 'true');

    await page.locator('#f-name').fill('R2D2');
    await page.locator('#f-email').fill('not-an-email');
    await page.locator('#f-email').blur();
    await expect(page.locator('#f-email-error')).toHaveText(/email address like/);
    await page.locator('#f-phone').pressSequentially('abc');
    await expect(page.locator('#f-phone')).toHaveValue(''); // letters are not accepted
    expect(posted).toBe(false);
  });

  test('a valid enquiry is sent once, stored, and confirmed @smoke', async ({ page }) => {
    const company = unique();
    await page.goto('/contact-us/');
    await fill(page, company);
    await waitFillTime(page);
    const posts = [];
    page.on('request', (r) => { if (r.method() === 'POST' && r.url().includes('admin-post.php')) posts.push(r); });
    const button = page.getByRole('button', { name: /Send enquiry/ });
    // A double click must not send twice.
    await button.dblclick();
    await expect(page.locator('#form-status')).toHaveText(/Thank you/);
    expect(posts.length).toBe(1);
    await expect(page.locator('#f-name')).toHaveValue(''); // the form is cleared
    await expect(button).not.toHaveAttribute('aria-disabled', 'true');

    const rows = stored(company);
    if (rows !== null) {
      expect(rows.length, 'stored exactly once').toBe(1);
      const meta = JSON.parse(wp(['post', 'meta', 'list', String(rows[0].ID), '--format=json']));
      const get = (k) => (meta.find((m) => m.meta_key === k) || {}).meta_value;
      expect(get('_aaykay_email')).toBe('asha.rao@example.com');
      expect(get('_aaykay_phone')).toBe('+91 98480 12345');
      expect(get('_aaykay_city')).toBe('Hyderabad');
      expect(get('_aaykay_mailed')).toMatch(/^[01]$/); // emailed, or recorded as not sent
      // Refreshing the page does not resend.
      await page.reload();
      expect(stored(company).length).toBe(1);
    }
  });

  test('a server error is shown as an error, never as success', async ({ page }) => {
    await page.goto('/contact-us/');
    await fill(page, unique());
    await waitFillTime(page);
    await page.route('**/admin-post.php', (route) => route.fulfill({ status: 500, contentType: 'text/html', body: '<h1>Error</h1>' }));
    await page.getByRole('button', { name: /Send enquiry/ }).click();
    await expect(page.locator('#form-status')).toHaveText(/could not be sent.*email/i);
    await expect(page.locator('#form-status')).not.toHaveText(/Thank you/);
    await expect(page.locator('#f-company')).not.toHaveValue(''); // nothing typed is lost
  });

  test('server-side field errors are shown on the fields', async ({ page }) => {
    await page.goto('/contact-us/');
    await fill(page, unique());
    await waitFillTime(page);
    await page.route('**/admin-post.php', (route) => route.fulfill({ status: 422, contentType: 'application/json', body: JSON.stringify({ ok: false, message: 'Some details need a correction. They are marked above.', errors: { email: 'Please enter an email address like name@company.com.' } }) }));
    await page.getByRole('button', { name: /Send enquiry/ }).click();
    await expect(page.locator('#f-email-error')).toHaveText(/email address like/);
    await expect(page.locator('#f-email')).toBeFocused();
  });

  test('a network failure is reported with another way to reach the company', async ({ page }) => {
    await page.goto('/contact-us/');
    await fill(page, unique());
    await waitFillTime(page);
    await page.route('**/admin-post.php', (route) => route.abort('internetdisconnected'));
    await page.getByRole('button', { name: /Send enquiry/ }).click();
    await expect(page.locator('#form-status')).toHaveText(/Check your connection.*(email|call)/i);
  });

  test('"Sending…" never stays on screen for ever', async ({ page }) => {
    await page.clock.install();
    await page.goto('/contact-us/');
    await fill(page, unique());
    await page.clock.fastForward(4000); // past the minimum fill time
    await page.route('**/admin-post.php', () => { /* never answers */ });
    await page.getByRole('button', { name: /Send enquiry/ }).click();
    await expect(page.locator('#form-status')).toHaveText(/Sending/);
    await page.clock.fastForward(31000);
    await expect(page.locator('#form-status')).toHaveText(/taking longer.*may still have reached us/i);
    await expect(page.getByRole('button', { name: /Send enquiry/ })).not.toHaveAttribute('aria-disabled', 'true');
  });

  test('without JavaScript the form still works and the visitor sees the result', async ({ browser }) => {
    const ctx = await browser.newContext({ javaScriptEnabled: false });
    const page = await ctx.newPage();
    const company = unique();
    await page.goto('/contact-us/');
    await fill(page, company);
    await page.getByRole('button', { name: /Send enquiry/ }).click();
    await expect(page).toHaveURL(/\/contact-us\/\?enquiry=sent#enquiry$/);
    await expect(page.locator('#form-status')).toHaveText(/Thank you/);
    const rows = stored(company);
    if (rows !== null) expect(rows.length).toBe(1);
    await ctx.close();
  });

  test('a bot that fills the hidden field is told "thank you" but nothing is stored', async ({ request }) => {
    const company = unique();
    const r = await request.post('/wp-admin/admin-post.php', {
      headers: { Accept: 'application/json' },
      form: { action: 'aaykay_enquiry', elapsed: '5000', website: 'http://spam.example', name: 'Spam Bot', company, email: 'bot@example.com', message: 'Buy cheap things now please' },
    });
    expect((await r.json()).ok).toBe(true);
    const rows = stored(company);
    if (rows !== null) expect(rows.length).toBe(0);
  });

  test('a very fast sender is kept for checking (held), not emailed and not lost', async ({ request }) => {
    const company = unique();
    const r = await request.post('/wp-admin/admin-post.php', {
      headers: { Accept: 'application/json' },
      form: { action: 'aaykay_enquiry', elapsed: '400', name: 'Quick Person', company, email: 'quick@example.com', message: 'Autofilled enquiry text here.' },
    });
    expect((await r.json()).ok).toBe(true);
    const rows = stored(company);
    if (rows !== null) {
      expect(rows.length).toBe(1);
      expect(wp(['post', 'meta', 'get', String(rows[0].ID), '_aaykay_mailed'])).toBe('held');
    }
  });

  test('markup in the fields is stored as plain text', async ({ request }) => {
    const company = 'Tag Test ' + Date.now().toString(36);
    const r = await request.post('/wp-admin/admin-post.php', {
      headers: { Accept: 'application/json' },
      form: { action: 'aaykay_enquiry', elapsed: '5000', name: 'Asha Rao', company, email: 'asha@example.com', message: '<script>alert(1)</script> We need LT panels for a new block.' },
    });
    expect(r.status()).toBe(200);
    const rows = stored(company);
    if (rows !== null) {
      const msg = wp(['post', 'meta', 'get', String(rows[0].ID), '_aaykay_message']);
      expect(msg).not.toContain('<script>');
      expect(msg).toContain('LT panels');
    }
  });
});
