// Browser tests for the AAYKAY website. They run against a running copy of the site:
//   BASE_URL=http://localhost:8080 npx playwright test
// Form tests also check the database through WP-CLI when WP_PATH points at that site's
// WordPress folder (local or CI only; never set WP_PATH for a live site).
// See TESTING_GUIDE.md.
const { defineConfig, devices } = require('@playwright/test');

const all = process.env.ALL_BROWSERS === '1';

module.exports = defineConfig({
  testDir: './e2e',
  timeout: 60_000,
  expect: { timeout: 10_000 },
  fullyParallel: false, // one WordPress, shared rate limits and records
  workers: 1,
  retries: process.env.CI ? 1 : 0,
  reporter: [['list'], ['html', { open: 'never', outputFolder: 'playwright-report' }], ['json', { outputFile: 'test-results/results.json' }]],
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8080',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    { name: 'desktop-chromium', use: { ...devices['Desktop Chrome'], viewport: { width: 1366, height: 900 } } },
    { name: 'mobile-chromium', use: { ...devices['Pixel 7'] } },
    ...(all ? [
      { name: 'desktop-firefox', use: { ...devices['Desktop Firefox'], viewport: { width: 1366, height: 900 } } },
      { name: 'mobile-webkit', use: { ...devices['iPhone 13'] } },
      { name: 'tablet-webkit', use: { ...devices['iPad (gen 7)'] } },
    ] : []),
  ],
});
