// Shared helpers for the browser tests.
const { execFileSync } = require('child_process');

// The website's pages (theme/inc/pages.php). Addresses as created by the theme.
const PAGES = [
  { path: '/', name: 'Home' },
  { path: '/capabilities/', name: 'Our Capabilities' },
  { path: '/projects/', name: 'Projects' },
  { path: '/quality-safety/', name: 'Quality & Safety' },
  { path: '/careers/', name: 'Careers' },
  { path: '/contact-us/', name: 'Contact Us' },
];

// Collect console errors, page errors and failed requests while a test runs.
function watch(page) {
  const problems = [];
  page.on('console', (m) => { if (m.type() === 'error') problems.push('console: ' + m.text()); });
  page.on('pageerror', (e) => problems.push('pageerror: ' + e.message));
  page.on('requestfailed', (r) => {
    const why = r.failure() ? r.failure().errorText : '';
    // Navigations and image swaps the page cancels on purpose are not failures.
    if (!/ERR_ABORTED|NS_BINDING_ABORTED|cancelled/i.test(why)) problems.push('failed: ' + r.url() + ' ' + why);
  });
  page.on('response', (r) => {
    if (r.status() >= 400 && !r.request().isNavigationRequest()) problems.push(r.status() + ' ' + r.url());
  });
  return problems;
}

// WP-CLI against the test site, when WP_PATH is set (local/CI only).
function wp(args) {
  if (!process.env.WP_PATH) return null;
  const bin = process.env.WP_CLI || 'wp';
  return execFileSync(bin, ['--path=' + process.env.WP_PATH, '--allow-root', ...args], { encoding: 'utf8' }).trim();
}

// Wait out the form's minimum fill time (3 s, inc/enquiry.php) without slowing every test.
async function waitFillTime(page) { await page.waitForTimeout(3100); }

module.exports = { PAGES, watch, wp, waitFillTime };
