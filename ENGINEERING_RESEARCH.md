# Engineering research log — AAYKAY Electricals website

What was looked at to make this site production-grade, what was adopted, and why. Dates
are 31 Oct 2026 unless stated. Categories:

- **A Essential:** must be done before production.
- **B High-value:** significantly better; do soon.
- **C Optional:** later, only if a real need appears.

## 0. What the application is (and so what "excellent" means here)

The site is a marketing and lead site for an electrical contractor. It has six public pages,
a 95-row project list, galleries, and one enquiry form. Visitors never log in. The only
personal data is the enquiry. Staff edit content in WordPress.

So the risks that matter are, in order:

1. Losing a lead.
2. Leaking enquiry data or admin access.
3. Publishing something the company has not approved.
4. Being slow on Indian mobile networks.
5. Being hard to maintain without the original developer.

There is no commerce, payment, user account or upload by visitors. Enterprise
infrastructure would be out of proportion.

## 1. Architecture decision: keep WordPress + classic theme on Hostinger Web Hosting

| Option | For | Against | Decision |
|---|---|---|---|
| **WordPress classic theme, no plugins (current)** | The client edits everything in a familiar dashboard. Hostinger's main product. No build step. Backups and SSL are handled by the host. | WordPress core must be kept updated. PHP request per page (mitigated by LiteSpeed Cache). | **Keep.** The audit of 6–7 Oct already chose it. Re-checked here: still the simplest reliable fit. |
| Static site (the original preview) + form service | Fastest and smallest attack surface | The client cannot edit without a developer. The form needs a third party. | Rejected: editing by staff is a requirement. |
| Headless / Next.js on Hostinger Node.js hosting | Modern stack | Needs the Business plan or higher for Node. Adds a build, a second system, and more to maintain. No user need. | Rejected: overengineering for six pages. Hostinger docs: [Node.js options](https://www.hostinger.com/support/node-js-hosting-options-at-hostinger/). |
| Block theme (FSE) | WordPress's direction | A rewrite of working templates. Staff would get a page builder that can break the design. | Rejected for now (C). The classic theme is fully supported by WordPress 7.1. |

## 2. The four tools named in the brief

| Tool | Official source | What happened | Verdict |
|---|---|---|---|
| **Strix** (AI pentest) | [usestrix/strix](https://github.com/usestrix/strix) v1.7.0, cloned 31 Oct 2026 | Read the README and the `web-app-penetration-testing` and `owasp-top-10-testing` skills, and used their method for the manual dynamic tests (`PRODUCTION_READINESS_REPORT.md` §4). **The scan itself was not run.** Strix needs Docker and an LLM API key (`STRIX_LLM`, `LLM_API_KEY`). Docker ran here, but no LLM key was available, and the session's own model credentials must not be borrowed. | **B.** Run a bounded `quick` scan against staging with the company's key (command in `TESTING_GUIDE.md`). Not run against the live site. |
| **Graphify** (code knowledge graph) | [Graphify-Labs/graphify](https://github.com/Graphify-Labs/graphify), PyPI `graphifyy` | Installed with `uv tool install graphifyy`. Ran `graphify update theme`, the code-only mode that needs no LLM: 321 nodes, 550 edges, 39 communities. | Used for impact analysis. Hubs: `aaykay_setting()` (18 edges), `start()` in site.js, `aaykay_asset()`, `aaykay_import_starter_content()`. **No import cycles.** 25 template files yield no symbols (PHP templates), so the graph was checked against `grep` and reading. Output kept out of `theme/` and git-ignored (it would otherwise deploy). Not committed. |
| **Context7** (current docs) | [upstash/context7](https://github.com/upstash/context7) | The `context7.com` API and MCP endpoint are blocked by this environment's network policy. The Claude Code plugin install is not available in this harness. | Replaced by the primary sources directly: WordPress core source 7.1.4 (read in place, e.g. `add_option()`'s SQL), developer.wordpress.org, Hostinger docs, Playwright and axe docs. Recommended for future sessions where reachable. |
| **Agent Skills** | [addyosmani/agent-skills](https://github.com/addyosmani/agent-skills) (25 skills) | Cloned and read: `security-and-hardening`, `shipping-and-launch`, `ci-cd-and-automation`, `observability-and-instrumentation`, `performance-optimization`. Not installed into any agent folder or the repo (`npx skills add` writes agent config, and nothing in it was needed at runtime). | Its checklists drove three additions: data export, erasure and retention (security skill, "Personal data"), the health endpoint and Site Health checks (observability), and the rollback workflow (shipping). |

## 3. Tools and libraries evaluated for the build itself

| Need | Candidate | Evaluation | Adopted? | Verified by |
|---|---|---|---|---|
| Browser E2E | **Playwright 1.63** ([docs](https://playwright.dev)) | Official, maintained by Microsoft, Apache-2.0. Runs Chromium, Firefox and WebKit. Has a clock API (used for the 30 s timeout test). Dev-only, never deployed. | **A, yes** | 55 tests × 2 profiles pass |
| Accessibility rules | **axe-core 4.11 via @axe-core/playwright** ([deque](https://github.com/dequelabs/axe-core)) | De-facto standard, MPL-2.0, tags for WCAG 2.2 AA | **A, yes** | 0 violations, 7 pages × 2 profiles |
| PHP security lint | **WordPress Coding Standards 3.3** + **PHPCompatibilityWP 3 (alpha)** | Official WordPress sniffs for escaping, sanitising, nonces and DB. PHPCompatibility 10 is still alpha, but it is the only line that knows PHP 8.x and works with WPCS 3.3; 9.3.5 failed against WPCS 3.3 here. | **A, yes** (`phpcs.xml.dist`, CI) | 0 findings after fixes |
| PHP static types | **PHPStan 2.3 + szepeviktor/phpstan-wordpress** | Ran at level 5. 22 findings, all benign: `array_filter('strlen')` signatures, always-true guards, `esc_attr(float)`. One dead branch noted. Not added to CI: noise relative to value for a theme of this size. | **C, ran once** | Output reviewed |
| Secret scanning | **gitleaks 8.28** | Fast, maintained, GitHub Action available. No licence needed on a personal account. | **A, yes** (CI) | 48 commits, no leaks |
| Lab performance | **Lighthouse 12.8** | Standard. Results in `tests/reports/` | **A, yes** | Before/after tables |
| Mail sending | WP Mail SMTP plugin vs **theme constants (existing)** | The plugin is popular, but it is one more plugin to update, plus an upsell UI. The theme already supports SMTP through `phpmailer_init`. | **Kept theme constants.** Added `AAYKAY_SMTP_SECURE` and `AAYKAY_MAIL_FROM`, a 15 s timeout, and failure logging. | Real SMTP sink: headers and body checked |
| Forms | Contact Form 7 / WPForms vs **theme handler (existing)** | Plugins bring their own spam handling and UI, but also a large attack surface and frequent CVEs. The theme's handler is ~300 lines, tested, and stores before it sends. | **Kept** | Integration + e2e |
| Bot protection | Cloudflare Turnstile ([docs](https://developers.cloudflare.com/turnstile/)) | Strong and free, but adds a third-party script and needs a Cloudflare account and a privacy-notice line. Today: honeypot, timing (kept as "held") and per-IP limits. | **B3, not now.** Add if spam gets through after launch. | — |
| Security plugin | Wordfence / Solid Security | Heavy. Overlaps with Hostinger's own protection and this theme's hardening. | **C, no** | — |
| Uptime | UptimeRobot / Better Stack (external, free tiers) | Needs a company account. Works with the new `/wp-json/aaykay/v1/health`. | **B, recommended** (runbook) | Endpoint tested |
| Page cache | LiteSpeed Cache (Hostinger default) | Native to LiteSpeed. The theme already purges on its own content changes. Its JS/CSS "optimisation" should stay off. | **A, yes** (configuration) | Needs verification on Hostinger |
| Privacy tooling | WordPress core privacy exporters/erasers | Built into core. Only needs registering. | **A, yes** (`inc/privacy.php`) | Integration tests, incl. the bin |
| Rollback | Hostinger "Redeploy" vs **GitHub workflow** | Redeploy only re-pulls the latest commit. A rewound branch may not be applied by a pull, so the rollback commits forward. | **A, yes** (`rollback.yml`) | Simulated against a bare repo: deploy → bad → rollback → deploy |

**Evaluation criteria** used for any new dependency:

- solves a real problem here
- compatible with WordPress 6.5–7.1, PHP 8.2–8.5 and Hostinger Web Hosting
- maintained (a release within 12 months)
- licence compatible
- no install scripts run unreviewed
- smaller than writing it
- not shipped to visitors unless needed

All the tooling adopted is **dev/CI only**. Nothing new is deployed to the server.

## 4. Findings implemented from research (with verification)

| Finding | Source | Category | Implemented | Verified |
|---|---|---|---|---|
| `add_option()` is not an atomic lock (`INSERT … ON DUPLICATE KEY UPDATE`) | WordPress core 7.1.4 `wp-includes/option.php:1142` | A | `aaykay_upgrade_lock()` uses `INSERT IGNORE` + a conditional DELETE | Integration tests; 8 parallel requests on a v2 site gave no duplicates |
| WordPress `post_status => 'any'` skips the bin | Core `get_post_stati` / `exclude_from_search` | A | Privacy tools and retention use all statuses | Eraser test with a binned enquiry |
| PHP 8.4 deprecates `fputcsv()` without `$escape` | php.net 8.4 deprecations; PHPCompatibility | A | Explicit `''` escape | phpcs, integration |
| Hostinger offers PHP 8.2–8.5 only; default 8.3 | [Hostinger PHP versions](https://www.hostinger.com/support/1575755-how-to-change-the-php-version-of-your-hosting-plan) | A | CI lint on 8.2–8.5, e2e on 8.3/8.4/8.5 | CI (to run on GitHub) |
| Git deploy **replaces files in the deploy directory** | [Hostinger Git docs](https://docs.hostinger.com/websites/git) | A | Guide warns to use exactly `public_html/wp-content/themes/aaykay` | Docs |
| Backups exclude mailboxes; restore overwrites | [Hostinger backups](https://docs.hostinger.com/websites/backups) | A | Runbook: CSV export before restore | Docs |
| Font preloads only on Home delayed LCP elsewhere | Lighthouse trace (LCP render delay 90%) | B | Preload both fonts on every page | Quality & Safety mobile LCP 4.4 s → 2.5 s |
| JS-shown slideshow controls shifted the hero | Layout Instability API attribution | B | Reserve the row when `.js` is set | CLS 0.112 → 0 |
| `sizes` hints overstated widths (4-, 3-, 2-column grids; container cap) | Lighthouse `uses-responsive-images`, a custom width checker | B | Corrected hints with `min()` | Checker: no mismatch at 390/768/1366/1920 |
| WCAG 2.2 / OWASP checks for public forms | Agent Skills security; Strix OWASP skill | A | Covered by tests (`TESTING_GUIDE.md`) | 97 e2e pass |

## 5. Remaining opportunities (not done, with reasons)

- **B: Run Strix on staging** once an LLM key and a staging site exist.
- **B: Cloudflare in front** (DNS proxy, WAF, Turnstile). It needs the domain's DNS at Cloudflare, which is a business decision. If adopted, configure real client IPs for the enquiry rate limit.
- **B: Per-project pages** for SEO (one URL per project). It needs the client's approval of the content, photos and URLs.
- **C: Replace the 6.6 MB brochure PDF** with a lighter, approved version (also a content approval issue).
- **C: Move content defaults out of `seed/content.json`** into a smaller structure. It works today; there is no user benefit.
