# Production readiness report — AAYKAY Electricals website

- **Branch:** `release/production-readiness`, built on `feat/review-oct13` (f884b19, the latest work, 13 Oct 2026). `main` is 9 commits behind that branch and was not used as the base.
- **Prepared:** 31 Oct 2026.
- **Test environment:**
  - WordPress 7.1.4, PHP 8.3.6, MariaDB 10.11.14
  - Apache 2.4 with `.htaccess` (closest available stand-in for Hostinger's LiteSpeed), and PHP's built-in server with the CI router
  - Chromium (Playwright 1.63), Lighthouse 12.8.2

## Update (31 Oct 2026, later): CI green on GitHub

The pull request's checks all passed on GitHub after commit `c651d02`: PHP lint 7.4/8.2–8.5, phpcs security rules, gitleaks, and WordPress + MariaDB integration and browser tests on PHP 8.3, 8.4 and 8.5, with Firefox and WebKit (iPhone/iPad sizes) on 8.3. This closes the "Firefox/WebKit" and "PHP 8.4/8.5 at runtime" gaps in §5.8. Hostinger itself is still unverified (§1).

## Verdict

**The code is a release candidate. The website is NOT READY TO LAUNCH.**

- **Code:** no known P0 or P1 defects remain in the code. Every check that could be run here passes (§5).
- **Launch:** blocked by items only the company, or a person with its accounts, can do (§1).
- **Hostinger:** the deployment has **not** been carried out or verified. There is no account and no domain yet.

Nothing here should be called "production-ready" until §1 is cleared, and until the guide's **Verify** steps and the smoke test have passed on the real host.

## 1. Launch blockers (external; not fixable in code)

| # | Sev | Blocker | Owner / action |
|---|---|---|---|
| 1 | P0 | The domain `akepl.in` was not registered at the last check (NXDOMAIN, Oct audit). | AAYKAY: register it in the company's own account and point it to Hostinger. |
| 2 | P0 | There is no Hostinger account or plan, so nothing has been deployed or verified on the real host. | AAYKAY: buy the plan (Web Hosting with WordPress + Git; daily backups recommended) and share access with the developer. Then follow `HOSTINGER_DEPLOYMENT_GUIDE.md`. |
| 3 | P0 | Content that is not approved would be published:<br>• "To come" placeholders are switched on (director's message, director's portrait, 4 team photos, latest appreciation certificates)<br>• the **4 job openings on Careers are samples** written for approval, and would read as real vacancies<br>• the Fire Safety card text and the MD's message are copy-edited drafts | AAYKAY: supply or approve each item, or switch the section off (Homepage content). Then untick *Site settings → Placeholders*. Site Health turns red while placeholders show on an indexable site. |
| 4 | P0 | The bundled brochure PDF (`assets/docs/aaykay-company-profile.pdf`, 100 pages, 6.6 MB) contains **turnover pages**, although the client brief (10 Oct) says turnover is not published. Head counts, client names and site photos also need written approval, and so do publishing rights for photos and scans. | AAYKAY (MD): approve, or supply a redacted brochure (*Site settings → Brochure*), or empty the setting to hide the tab. |
| 5 | P1 | The privacy notice is a draft with bracketed parts: retention period, grievance contact, Google Sheet use. The form collects personal data (DPDP Act 2023). | AAYKAY with legal advice: complete it, publish it, and set *Delete enquiries after* to match it. *This report is not legal advice.* |
| 6 | P1 | The GitHub repository is **public** on a personal account and contains client names, photos and company documents. | Owner: make it private or move it to a company organisation **before** connecting Hostinger. |
| 7 | P1 | Contact details came from an older brochure and are unconfirmed. Also the SMTP mailbox for enquiries, and the uptime monitor account. | AAYKAY + developer: guide §6, runbook "Health checks". |
| 8 | P2 | Lighthouse, Core Web Vitals and headers have not been measured on the real host (lab results only, §5.4). | Developer: on launch day, run PageSpeed Insights + `tests/bin/smoke.sh`. |

## 2. What was wrong at the start (31 Oct baseline)

The prior work was strong:

- escaped output and sanitised input everywhere
- nonces and capability checks on admin actions
- a store-first enquiry design
- an accessible, responsive front end
- axe 0 and no sideways overflow at 320–1920 px, confirmed again here

The problems were in failure paths, operations and verification:

| # | Sev | Problem (evidence) | Status |
|---|---|---|---|
| F1 | P1 | **Without JavaScript, the enquiry confirmation was lost.** The handler redirected to `/?enquiry=sent#contact`, but the form and its message live only on Contact Us, so the visitor saw no result (curl: `303 → /?enquiry=sent#contact`). | Fixed: redirects to `/contact-us/?enquiry=…#enquiry`. E2E no-JS test (fails on the old code). |
| F2 | P1 | **Real leads could be silently discarded.** Any script submission under 3 s answered "Thank you" and stored nothing, and autofill can be that fast (curl: `elapsed=100` returned ok with no row). | Fixed: kept as "held" (not emailed or sent to the Sheet), with the reason shown in the dashboard. The honeypot still drops bots. |
| F3 | P1 | **"Sending…" could stay forever.** `fetch` had no timeout. | Fixed: 30 s abort with an honest "may still have reached us" message. Clock-controlled e2e test (fails on the old code). |
| F4 | P2 | **Duplicate element ids** `f-phone` and `f-email` on Contact Us: in the footer and in the form. The footer copy buttons selected the form field instead of the number. (Found by the new tests; axe 4.11 no longer reports duplicate ids.) | Fixed: footer ids renamed `ft-*`. |
| F5 | P1 | **Upgrade routine without a real lock.** It ran on every fresh install too, and `add_option()` is not atomic (core uses `ON DUPLICATE KEY UPDATE`), so two concurrent first requests could duplicate pages or projects. | Fixed: `INSERT IGNORE` lock, version re-read, fresh installs record the version. 8 parallel requests: no duplicates. |
| F6 | P2 | `fputcsv()` without `$escape` is deprecated on PHP 8.4+ (Hostinger offers 8.4/8.5). On a server that displays errors, the warning would corrupt the CSV download. | Fixed. |
| F7 | P1 | **No data-subject tooling.** Enquiries could not be exported or erased through WordPress's privacy tools, and there was no retention option. | Added `inc/privacy.php`: exporter, eraser (covers the bin), optional retention with a daily job. |
| F8 | P2 | **No monitoring hooks.** Failed emails were visible only per enquiry, and nothing alerted anyone. | Added `/wp-json/aaykay/v1/health` (only `{"status":"ok"}`, never cached) and three Site Health checks. Failed sends are logged without personal data. |
| F9 | P2 | **Missing headers:** no CSP at all, an opt-in path for HSTS, and a shortlink header that leaked `?p=` addresses. | Added a non-breaking CSP (`frame-ancestors`, `base-uri`, `object-src`, `form-action`), `AAYKAY_HSTS`, and removed the shortlink. |
| F10 | P2 | **Inner pages had no Open Graph tags.** Shared links showed no preview. | Added (title, URL, description, image). |
| F11 | P2 | **Performance:**<br>• home CLS **0.112** on a throttled phone: the slideshow controls appeared after the script ran<br>• Quality & Safety mobile LCP **4.4 s** (score 82): fonts were preloaded on Home only<br>• `sizes` hints overstated photo widths (up to 314 KiB extra) and understated the founder photo (blurry) | Fixed: CLS 0, LCP 2.5 s (96). Hints corrected. |
| F12 | P2 | **The 404 page said "Everything is on the home page"**, which was no longer true with six pages. | Now lists the pages. |
| F13 | P1 | **CI** only linted on PHP 7.4/8.3 and never ran the site. Hostinger offers 8.2–8.5. There were no tests, no rollback and no smoke test. | Added CI: lint 7.4 + 8.2–8.5, phpcs security, gitleaks, data check, and real WordPress + MariaDB e2e on 8.3/8.4/8.5 (Firefox/WebKit on 8.3), failing on PHP notices. Added `rollback.yml` and `smoke.sh`. |
| F14 | P3 | Stale comments ("Ten pages"), and dead code in `contact.php` (`$aaykay_extra[1]` is always empty). | Comment fixed. Dead code left, because it is harmless. |

## 3. What was changed (commits on this branch)

1. `fix:` enquiry reliability, privacy tools, health checks, header hardening (F1–F3, F6–F10, F12)
2. `test:` integration tests inside WordPress and Playwright browser tests
3. `perf+ci:` steady first screen, earlier fonts, right-sized photos; CI on PHP 8.2–8.5; rollback; smoke (F11, F13)
4. `fix:` independent review findings: atomic lock, the bin in privacy tools, forward-only rollback, safer test scripts, Site Health ids, a timeout edge case (F5 and follow-ups)
5. `docs:` this report, the deployment guide, the testing guide, the runbook, the research log, `.env.example`, README

Design, content and copy were not changed, apart from the 404 sentence and the new failure messages.

## 4. Security review

Method:

- Manual OWASP-style review of every request handler and every echo. Superglobal reads and capability/nonce checks were enumerated.
- PHPCS with the WordPress Security, DB and WP sniffs.
- Dynamic tests against the local site with curl and Playwright.
- Role tests with real logins: editor, author, contributor and subscriber.
- gitleaks over the full history.
- An independent second review of the diff.
- **Strix was not run** (no LLM key; see `ENGINEERING_RESEARCH.md`).

| Area | Result |
|---|---|
| XSS | All output escaped (`esc_html`/`esc_attr`/`esc_url`/`wp_kses`). Stored `<script>` in an enquiry is saved as text (e2e). Settings and content are sanitised (`javascript:` links refused; tests). |
| Injection (SQL / header / CSV) | No raw SQL with input; the one new query is prepared. The email `Reply-To` strips CR/LF/commas, and the email is validated by `is_email`. CSV cells starting `= + - @ TAB CR` are prefixed (tests). |
| CSRF | Every admin action has a nonce + capability check. The public form intentionally has no nonce (cached pages); it has a honeypot, timing and per-IP limits instead. |
| Access control / IDOR | Enquiries are a private, non-REST, non-queryable type. Authors and contributors see no enquiry data and cannot open one (403). Settings and content need `edit_others_posts`, the starter import needs `manage_options`, and the code editors are disabled. The user list is hidden from REST, author archives redirect, and `?p=` for an enquiry gives 404. |
| SSRF | The Google Sheet URL must start with `https://script.google.com/` (sanitiser + test). No other server-side fetch. |
| Path traversal | Image "launch defaults" accept only `assets/img/…` without `..` (tests). |
| File uploads | Visitors cannot upload. Staff use WordPress's Media Library (SVG blocked by core). |
| Secrets | None in the code or the 48-commit history (gitleaks). The SMTP password belongs only in `wp-config.php`. The Sheets secret is in the DB and editable. |
| Headers | `nosniff`, `SAMEORIGIN`, a referrer policy, a permissions policy and a CSP. HSTS is opt-in. `X-Powered-By`, directory listing and `debug.log` exposure are server-level, and the guide §8 covers them. They were **found exposed on the test Apache**; verify them on Hostinger with `smoke.sh`. |
| Rate limiting | 20 emailed and 60 accepted per IP per hour. Behind a proxy or CDN, the real visitor IP must reach PHP (documented). |
| Dependencies | The theme ships no third-party JS. The fonts (OFL) and icons (ISC) carry their licences. Test tooling is dev-only. |
| Remaining risks | WordPress core and LiteSpeed Cache must be kept updated (runbook). wp-login brute force is left to Hostinger's protection plus strong passwords; 2FA is recommended. A Strix scan is pending. |

## 5. Test results (evidence)

### 5.1 Static checks

- PHP lint passes on 8.3 locally; CI covers 7.4 and 8.2–8.5.
- `phpcs.xml.dist`: **0 errors, 0 warnings.**
- `node --check` passes.
- gitleaks: **0 leaks**.
- PHPStan level 5 (one-off): 22 benign findings, no defects.

### 5.2 Integration (inside WordPress)

`tests/php/integration.php`: **81 passed, 0 failed**.

### 5.3 Browser end-to-end

Playwright, Chromium, desktop 1366×900 and Pixel 7: **97 passed, 0 failed, 13 skipped** (by design: device-specific cases on the other profile).

It ran twice, under Apache and under the CI-style `php -S` server. Mutation checks confirmed the tests fail on the original F1, F3 and CSV code.

### 5.4 Lighthouse (lab, local server)

| Metric | Result |
|---|---|
| Accessibility, Best practices, SEO | 100 on all pages |
| Performance, desktop | 100 on all pages |
| Performance, mobile | 94 Home, 98 Capabilities, 98 Projects, 96 Quality & Safety, 98 Careers, 98 Contact |
| Home (mobile) | CLS 0, LCP 2.8 s |
| Page weight | 171–874 KiB |

Full tables: `tests/reports/lighthouse-before.md` and `tests/reports/lighthouse-after.md`.

### 5.5 Responsive

No sideways scroll and no off-screen content on all six pages at 320, 360, 390, 430, 768, 1024, 1366 and 1920 px. Touch targets are ≥ 24 px at 390 px.

### 5.6 Accessibility

| Check | Result |
|---|---|
| axe WCAG 2.2 AA | 0 violations, 7 pages × 2 profiles |
| Keyboard | Visible focus. Skip link, menu focus trap and inert background, dropdowns, viewer and form all work by keyboard. |
| Reduced motion | Respected: no autoplay, no reveal motion |

**Not done:** a manual screen-reader pass (NVDA/VoiceOver). That needs a person with the tools; recommended before launch.

### 5.7 Email path

A real SMTP sink received the enquiry with:

- `From: AAYKAY website <…>`
- `Reply-To: <visitor>`
- the subject, the fields and the dashboard link

### 5.8 Not verified here

| Item | Why |
|---|---|
| Firefox and WebKit | Not installed here; the CI job runs them |
| PHP 8.4/8.5 at runtime | Installing extra PHP builds was not permitted in this environment; the CI job runs them |
| Hostinger itself, LiteSpeed Cache behaviour, real email deliverability, HTTPS/www redirects, and the backup restore | No account exists |
| The CI workflows on GitHub | GitHub write access was not available from this session, so nothing was pushed (§7) |

## 6. SEO

Every page has:

- a unique title and description
- a canonical link
- Open Graph and Twitter tags
- `lang="en-IN"`
- one `h1`

Home also has Organization JSON-LD. `wp-sitemap.xml` lists the six pages only; users are excluded. Search, feeds, author and attachment archives return 404 or redirect. robots.txt comes from WordPress.

Indexing is controlled by *Settings → Reading* and must stay off until launch (Site Health warns). There are no per-project URLs (see research §5, B).

## 7. Outstanding risks and items, by priority

| Item | Priority |
|---|---|
| §1 blockers 1–7 | P0/P1, external |
| Push this branch and run CI on GitHub | P1, developer |
| A Strix scan on staging; a manual screen-reader pass; restore-test a backup on staging | P2 |
| Cloudflare/Turnstile only if spam appears; 2FA for admins | P2 |
| Authors and contributors can create Projects (WordPress `post` capabilities). Staff should be given Editor accounts only (runbook). | P3 |
| Per-project pages (SEO); a lighter brochure; the dead code in `contact.php` | P3 |

## 8. Deliverables

`HOSTINGER_DEPLOYMENT_GUIDE.md` · `TESTING_GUIDE.md` · `OPERATIONS_RUNBOOK.md` · `ENGINEERING_RESEARCH.md` · `.env.example` · `phpcs.xml.dist` · `.github/workflows/{check,deploy,rollback}.yml` · `tests/` · theme changes listed in §3.
