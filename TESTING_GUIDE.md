# Testing guide — AAYKAY Electricals website

How to check the site after any change, locally and in CI. The bar for merging is the same
locally and in CI: every check below passes.

## What exists

| Layer | Where | What it proves | Runs in CI |
|---|---|---|---|
| PHP syntax on 7.4, 8.2, 8.3, 8.4, 8.5 | `.github/workflows/check.yml` (php-lint) | Every PHP file parses on every PHP version Hostinger offers | yes |
| Code rules | `phpcs.xml.dist` (WordPress Coding Standards 3: Security, DB, WP, PHP; PHPCompatibilityWP) | Output escaped, input sanitised, nonces, no unsafe DB calls, no PHP-version traps | yes |
| JS syntax | `node --check` | `site.js` and the editor script parse | yes |
| Secrets | gitleaks over the whole Git history | No keys or passwords committed, ever | yes |
| Starter data | inline Python in `check.yml` | `seed/content.json` is valid; every photo, logo and document it names exists | yes |
| Integration (inside WordPress) | `tests/php/integration.php` (81 checks) | Setup and import, server-side validation, privacy export/erase/retention, CSV formula escaping, Google Sheets (mocked HTTP), settings and content sanitising (incl. path traversal), health endpoint, Site Health checks, upgrade lock | yes |
| Browser end-to-end | `tests/e2e/*.spec.js` (Playwright, 55 tests × 2 device profiles) | Every page, navigation, menus, project filters, viewer, slideshow, the enquiry form end to end (stored in the DB), failure paths, no-JS, 8 screen widths, touch targets, axe WCAG 2.2 AA, visible focus, security headers, no cookies | yes (PHP 8.3, 8.4, 8.5; Firefox + WebKit on 8.3) |
| Post-deploy smoke | `tests/bin/smoke.sh URL` | Read-only checks safe on the live site | after each deploy, by hand |
| Performance | Lighthouse 12 (see below), results in `tests/reports/` | Core Web Vitals in the lab | by hand |

## Local setup (once)

You need:

- PHP 8.2+ with `mysqli`, `gd`, `intl` and `mbstring`
- MariaDB or MySQL
- [WP-CLI](https://wp-cli.org)
- Node 20+
- git

```bash
# A database for tests only: the script DROPS and recreates it.
mysql -uroot -e "CREATE DATABASE wp_test; CREATE USER 'wptest'@'localhost' IDENTIFIED BY 'local-test-only'; GRANT ALL ON wp_test.* TO 'wptest'@'localhost';"

export WP_PATH=/tmp/wp-test WP_URL=http://127.0.0.1:8080 BASE_URL=http://127.0.0.1:8080
export DB_NAME=wp_test DB_USER=wptest DB_PASS=local-test-only
tests/bin/fresh-site.sh                         # WordPress 7.1.4 + this theme, starter content imported
PHP_CLI_SERVER_WORKERS=4 php -S 127.0.0.1:8080 -t "$WP_PATH" tests/bin/router.php &   # or Apache/LiteSpeed with AllowOverride All

cd tests && npm ci && npx playwright install chromium   # add firefox webkit for ALL_BROWSERS=1
```

`fresh-site.sh` refuses to run if `wp-config.php` names another database, or if the site is
not `WP_ENVIRONMENT_TYPE` local/development.

## Running the checks

```bash
# from the repository root
find theme tests/php -name '*.php' -print0 | xargs -0 -n1 php -l      # syntax
phpcs --standard=phpcs.xml.dist                                     # needs WPCS 3 (see check.yml for the install line)
node --check theme/assets/js/site.js
wp --path="$WP_PATH" eval-file tests/php/integration.php            # 81 checks, exit code 0 = pass

cd tests
WP_PATH=$WP_PATH npx playwright test                 # all browser tests (~3.5 min)
WP_PATH=$WP_PATH npx playwright test --grep @smoke   # the quick subset
ALL_BROWSERS=1 npx playwright test                   # + Firefox, iPhone/iPad WebKit
npx playwright show-report                           # HTML report with traces of failures
```

Without `WP_PATH`, the form tests still check what the visitor sees. They skip the database
checks. Never set `WP_PATH` to a live site.

Between runs on the same site, the form tests clear transients themselves. These hold the
per-connection enquiry limits. To start completely clean, run `fresh-site.sh` again.

## The end-to-end journeys

1. Each page loads with:
   - status 200, one `h1`, and a unique title and description
   - a canonical link, Open Graph tags and `lang="en-IN"`
   - all images loaded, and every image with an `alt`
   - the security headers
   - no cookies, no console errors and no failed requests
2. An unknown address returns a real 404 with links to every page. Search, feeds, the author archive and the user list are not exposed. Theme developer files return 403. The sitemap does not list users.
3. Every page is reachable from the header menu, or the phone menu on mobile. The logo returns home, and a reload on a nested page works.
4. The skip link moves focus to `main`. Phone and email links are `tel:` and `mailto:`. Every `target=_blank` link has `noopener`.
5. The phone menu:
   - opens, keeps focus inside, and makes the page behind it inert
   - closes with Escape and returns focus to its button
   - never causes sideways scrolling
6. The desktop dropdowns open with the keyboard and close with Escape. Their entries land on the right section of another page.
7. The project list:
   - shows 12 rows, then all
   - chips filter it, the counts match, and the filter is in the address
   - shared filtered links work, and a sector row plus Back restores the view
   - it is complete without JavaScript
8. The image viewer opens, steps with the arrow keys, loads full images, and closes with Escape.
9. The slideshow arrows and pause work. It does not autoplay with reduced motion.
10. The enquiry form:
    - field errors with nothing sent
    - a valid enquiry sent **once** (double click) and **stored once** (checked in the DB), the form cleared
    - a reload does not resend
    - a server 500 shows an error and never "Thank you"; typed text is kept
    - a server 422 marks the right field
    - a network failure gives an alternative contact
    - "Sending…" gives up after 30 s
    - **without JavaScript** it returns to Contact Us with the message
    - honeypot bots are stored nowhere; very fast senders are kept as "held"
    - markup is stored as plain text
11. At 320, 360, 390, 430, 768, 1024, 1366 and 1920 px there is no sideways scrolling and no content off-screen. Touch targets are ≥ 24 px on a phone.
12. axe-core (WCAG 2.0/2.1/2.2 A and AA) finds no violations on all six pages and the 404. Keyboard focus is always visible. Every form field has a label and error descriptions.

## Latest results (31 Oct 2026, this branch)

- **Integration:** 81 passed, 0 failed (WordPress 7.1.4, PHP 8.3.6, MariaDB 10.11).
- **Playwright:** 97 passed, 13 skipped (by design: desktop-only or phone-only cases on the other profile), 0 failed. Chromium 1.63 on desktop 1366×900 and Pixel 7. Run twice: under Apache with `.htaccess`, and under `php -S` with `tests/bin/router.php`, as CI does.
- **Regression proof:** the tests fail if the original bugs are put back.
  - The no-JS redirect to the home page fails the no-JS test.
  - No fetch timeout fails the "Sending…" test.
  - No CSV formula escaping fails 4 integration checks.
- **PHPCS** (`phpcs.xml.dist`): 0 errors, 0 warnings. **gitleaks:** 48 commits scanned, no leaks.
- **Not run here:** Firefox and WebKit (only Chromium is installed in this environment), and PHP 8.4/8.5 at runtime. CI runs both.
- **Lighthouse** (`tests/reports/lighthouse-before.md` / `-after.md`, local server, simulated mobile throttling):
  - accessibility, best practices and SEO are 100 on every page
  - mobile performance is 94–98, desktop performance is 100
  - home CLS went from 0.112 to 0
  - Quality & Safety mobile LCP went from 4.4 s to 2.5 s

To repeat Lighthouse:

```bash
npx lighthouse@12 http://127.0.0.1:8080/quality-safety/ --only-categories=performance,accessibility,best-practices,seo --output=json --output-path=./qs-mobile.json
npx lighthouse@12 http://127.0.0.1:8080/ --preset=desktop --output=html --output-path=./home-desktop.html
```

On the live site, use PageSpeed Insights (field data + lab) and file the numbers in `tests/reports/`.

## Security testing (Strix)

[Strix](https://github.com/usestrix/strix) is an AI penetration-testing agent. It needs Docker and an LLM API key. Neither was available when this branch was prepared, so **no Strix scan has been run**. To run a bounded scan against a **local or staging** copy, never the live site:

```bash
pipx install strix-agent            # or: uv tool install strix-agent
export STRIX_LLM="<provider/model>" LLM_API_KEY="<your key>"   # never commit these
strix --target http://host.docker.internal:8080 --target ./theme \
      --instruction "WordPress theme. Test the enquiry form at /wp-admin/admin-post.php (action=aaykay_enquiry), the REST route /wp-json/aaykay/v1/health, and the admin screens with an Editor login you are given. Do not test wp-login brute force." \
      --scan-mode quick -n
```

Then fix what it proves, and re-run.

## After a change: the minimum

1. Run `php -l` on the files you touched, plus `phpcs` and `node --check`.
2. Run the integration tests.
3. Run `npx playwright test --grep @smoke`, then the full suite before merging.
4. Look at the page yourself at 390 px and 1440 px, with and without JavaScript, and with reduced motion.
5. Push a branch. CI runs everything on PHP 8.3, 8.4 and 8.5, and must be green before merging to `main`, because merging deploys.
