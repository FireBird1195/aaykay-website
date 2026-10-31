# Hostinger deployment guide — AAYKAY Electricals website

This guide takes the site from this repository to a live WordPress site on Hostinger. It
was written on 31 Oct 2026 against Hostinger's own documentation (links at the end). It has
**not** been carried out on a real Hostinger account yet, because none exists. Every step
marked **Verify** must be checked on the real account the first time.

Hostinger's Node.js guides do not apply here. This is a **PHP application (WordPress)**, not
a Node.js app, so the Node.js hosting option, `npm build` and start commands are not used on
the server.

---

## 1. Architecture

```
                         ┌───────────────── Hostinger Web Hosting (LiteSpeed, PHP 8.3) ────────────────┐
 visitor ──HTTPS──► akepl.in ──► public_html/  (WordPress core, installed by hPanel)                  │
                         │        ├── wp-config.php        ← secrets + AAYKAY_* constants (never in Git)│
                         │        ├── .htaccess            ← WordPress rules + hardening block (§8)     │
                         │        └── wp-content/                                                       │
                         │            ├── themes/aaykay/   ← THIS REPOSITORY'S theme/ (Git auto-deploy)  │
                         │            ├── uploads/         ← photos AAYKAY uploads (backups, not Git)   │
                         │            └── plugins/         ← LiteSpeed Cache only (Hostinger's)         │
                         │        MariaDB/MySQL database   ← pages, projects, settings, enquiries      │
                         └──────────────────────────────────────────────────────────────────────────────┘
 GitHub: main ──Action "deploy"──► branch "deploy" (= theme/ only) ──Hostinger Git auto-deploy──┘
 Optional out: enquiry email (SMTP mailbox) · Google Sheet (Apps Script web app) · Cloudflare Web Analytics
```

| Question | Answer for this site |
|---|---|
| Type | WordPress 6.5+ (tested on 7.1.4) with a custom classic theme. No custom plugins and no build step on the server. |
| Runtime | PHP 8.2 to 8.5 (Hostinger's choices). Tested locally on 8.3, which is Hostinger's default. CI runs 8.3, 8.4 and 8.5. |
| Server processes, workers, WebSockets | None. Plain PHP requests only. |
| Long requests | None over a few seconds. The enquiry request waits for the email (15 s SMTP timeout) and the Google Sheet (8 s). |
| Cron | WordPress cron: a daily retention job, used only when retention is set. A real cron job is recommended (§10). |
| Database | One MySQL/MariaDB database, created by Hostinger's WordPress installer. |
| Persistent files | `wp-content/uploads` (photos) and the database. A theme deploy never touches either. |
| Plan | Any Hostinger **Web Hosting** plan that offers WordPress and Git. Daily backups (Business and up, or the add-on) are strongly recommended for a lead-collecting site. **Do not buy anything without the company's approval.** |

---

## 2. What has to exist before you start (external blockers)

These cannot be done from the repository. They are the company's or the developer's actions.

1. **The domain.** `akepl.in` did not resolve when the site was last checked (Oct 2026 audit). It must be registered in **AAYKAY's own name and account**, with its DNS pointing to Hostinger.
2. **A Hostinger Web Hosting plan in AAYKAY's account.** Give the developer access with Hostinger's *Account sharing* feature, not by sharing passwords.
3. **This repository moved to a company GitHub account or made private.** It is currently public under a personal account and includes client names and photos.
4. **Written approvals** listed under "Launch blockers" in `PRODUCTION_READINESS_REPORT.md`.
5. **Do not change DNS, MX, SPF or DKIM records for `akelectricals.in`.** That domain is the company's email and is not part of the website. Never link `akee.in`: it redirects to an unrelated site.

---

## 3. One-time setup: WordPress on Hostinger

1. **hPanel → Websites → Add website → WordPress.**
   - Choose the domain `akepl.in`.
   - Use a strong admin username (not `admin`) and a unique password.
   - Skip all of Hostinger's optional plugins and themes **except LiteSpeed Cache**.
2. **hPanel → Websites → Manage → PHP Configuration:** choose **8.3** (the tested version). Later moves to 8.4 or 8.5 are fine once the CI e2e job is green on that version.
3. **Security → SSL → Install SSL**, then switch on **Force HTTPS** in the same place. **Verify:** `http://akepl.in` answers with a 301 to `https://akepl.in`.
4. **WordPress → Settings → General:** set both *WordPress Address* and *Site Address* to `https://akepl.in`. This choice makes the bare domain canonical. WordPress then redirects `www.akepl.in` to it, provided `www` points to the same site. **Verify** with `tests/bin/smoke.sh https://akepl.in`.
5. **Settings → Reading:** tick **"Discourage search engines"** until launch day.
6. **Settings → Permalinks:** choose **Post name** (`/%postname%/`).
7. **Plugins:** delete every plugin except **LiteSpeed Cache**. The theme needs none. Fewer plugins means a smaller attack surface.

## 4. One-time setup: connect GitHub (theme deploys)

The GitHub Action `deploy` publishes `theme/` to the branch **`deploy`**. On every merge to `main` it runs the checks first. Hostinger pulls that branch into the theme folder.

1. In GitHub, merge this branch into `main`, or push `main`. Wait for **Actions → deploy** to turn green. The `deploy` branch then exists.
2. **hPanel → Websites → Manage → Advanced → Git → Connect with GitHub.** Install the Hostinger GitHub App for this repository only.
3. Repository: this one. Branch: **`deploy`**. Deploy directory: **`public_html/wp-content/themes/aaykay`**.
   - ⚠️ Type the directory exactly. Hostinger *replaces the files in the deploy directory on every deploy*. If this is left as `public_html`, the next deploy wipes WordPress itself.
   - SSH deployments need an empty folder for the first deploy, so make sure `wp-content/themes/aaykay` does not exist yet.
4. Click **Deploy** and watch the log until it shows *Completed*. Leave **Auto-deployment** on.
5. **WordPress → Appearance → Themes → activate "AAYKAY Electricals".** On first activation, the theme imports the launch content and creates the pages:
   - 95 projects
   - 6 sectors
   - 14 clients
   - 31 firms
   - Site settings
   - the 6 pages, with Home as the front page
6. **Fallback if Git deployment is not available:** zip the `theme/` folder as `aaykay/` and upload it under *Appearance → Themes → Add New → Upload Theme*. Do this only from a commit where CI is green.

## 5. Runtime configuration: `wp-config.php`

WordPress on Hostinger reads its settings from `public_html/wp-config.php`, **not** from a `.env` file. `.env.example` in this repository lists every setting, with an explanation. Add these lines to `wp-config.php` above `/* That's all, stop editing! */`. Use *hPanel → Files → File Manager*.

```php
// Environment and safety
define( 'WP_ENVIRONMENT_TYPE', 'production' );   // the test script refuses to run against production
define( 'DISALLOW_FILE_EDIT', true );              // the theme also blocks the code editors
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );                   // if you ever need it: a path OUTSIDE public_html, e.g. '/home/uXXXXXXXX/domains/akepl.in/logs/wp-debug.log'
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_POST_REVISIONS', 10 );
define( 'DISABLE_WP_CRON', true );                 // only together with the cron job in §10

// Enquiry email through a real mailbox (see §6)
define( 'AAYKAY_SMTP_HOST', 'smtp.hostinger.com' );
define( 'AAYKAY_SMTP_PORT', 465 );                 // SSL. If blocked: 587 and AAYKAY_SMTP_SECURE 'tls'
define( 'AAYKAY_SMTP_USER', 'website@akepl.in' );
define( 'AAYKAY_SMTP_PASS', '…mailbox password…' );

// HTTPS only, for six months (set only after §3 step 3–4 is verified on both akepl.in and www)
// define( 'AAYKAY_HSTS', true );
```

The database name, user, password and the salts are already in `wp-config.php` from Hostinger's installer. Do not copy them anywhere else.

## 6. Email (enquiry notifications)

Each enquiry is first **saved** under *Dashboard → Enquiries*, then emailed, then copied to the Google Sheet if one is connected. Mail from PHP's own `mail()` is often filtered as spam, so use a mailbox:

1. **hPanel → Emails** for `akepl.in`. Create the mailbox `website@akepl.in`. Hostinger adds the SPF, DKIM and DMARC records when the domain's DNS is at Hostinger. **Verify** them in *Emails → Email deliverability* or a DNS checker.
2. Put its details in `wp-config.php` (§5). The SMTP host is `smtp.hostinger.com`, port 465 with SSL, or 587 with STARTTLS.
3. **Site settings → Send enquiries to:** the company inbox(es). For example, `projects@akelectricals.in` was the launch value; confirm it.
4. Send a test enquiry from the Contact Us page.
   - **Verify** that it arrives, and that *Enquiries* shows "Email notification: Sent".
   - **Tools → Site Health** shows "Enquiries are emailed through a mailbox (SMTP)".

## 7. Google Sheet (optional)

Follow the steps in `docs/google-sheets-apps-script.gs`:

1. Create the sheet.
2. Paste in the script.
3. Deploy it as a web app.
4. Paste the web app URL into *Site settings → Leads*, then press **Send a test row**.

Only `https://script.google.com/…` addresses are accepted. A secret code stops anyone else adding rows.

## 8. Server hardening (root `.htaccess`)

Add this block **above** `# BEGIN WordPress` in `public_html/.htaccess`, using the File Manager. Hostinger's LiteSpeed reads `.htaccess` the same way Apache does.

```apache
# BEGIN AAYKAY hardening
Options -Indexes
<FilesMatch "^(wp-config\.php|readme\.html|license\.txt|debug\.log|\.user\.ini|php\.ini)$">
  Require all denied
</FilesMatch>
<IfModule mod_headers.c>
  Header always unset X-Powered-By
</IfModule>
# END AAYKAY hardening
```

**Verify** with `tests/bin/smoke.sh https://akepl.in`. The checks for "uploads folder not listed", "debug.log not public" and "no X-Powered-By" must pass.

## 9. LiteSpeed Cache

1. Keep **page cache on**. The theme clears it whenever Site settings, Homepage content, projects, clients, firms or sectors change.
2. In *LiteSpeed Cache → Page Optimization*, leave CSS/JS **minify, combine, defer and "Guest mode"** **off**. The theme is already small and ordered on purpose, and combining scripts can break the form and the menu.
3. Image optimisation (QUIC.cloud) is optional; it needs a QUIC.cloud account.
4. The health check `/wp-json/aaykay/v1/health` sends `X-LiteSpeed-Cache-Control: no-cache`.
5. **Verify** that the endpoint answers fresh: change nothing, call it twice, and both calls return `{"status":"ok"}`.

## 10. Cron (retention and WordPress's own jobs)

1. With `DISABLE_WP_CRON` true (§5), add **hPanel → Advanced → Cron Jobs:** `*/15 * * * *` with the command `php /home/uXXXXXXXX/domains/akepl.in/public_html/wp-cron.php` (Hostinger's usual layout; copy the exact path from the File Manager).
2. If you prefer to keep WordPress's built-in cron (it runs on visits), leave `DISABLE_WP_CRON` out.

## 11. Content and launch day

1. **Site settings:** confirm phones, email, address and the figures.
   - Set **Delete enquiries after** to the period in the privacy notice. Leave it empty to keep enquiries until they are deleted by hand.
   - Untick **Placeholders** once every "To come" item is supplied.
2. **Settings → Privacy:** complete the draft privacy notice (bracketed parts) and have it reviewed, then publish it. The form and footer link to it automatically.
3. **Tools → Site Health:** the three "AAYKAY website" checks should be green, or explained.
4. **Settings → Reading:** untick "Discourage search engines".
   - Google Search Console: verify the domain by DNS, or by the HTML tag in *Site settings → Search engines*.
   - Then submit `https://akepl.in/wp-sitemap.xml`.
5. Run `LAUNCHED=1 tests/bin/smoke.sh https://akepl.in`. All checks must pass.
6. Run PageSpeed Insights on the home page and on Quality & Safety, and file the results in `tests/reports/`.

## 12. Post-deployment smoke test (after every deploy)

```bash
tests/bin/smoke.sh https://akepl.in        # read-only; safe on the live site
```

Then do one manual check in a phone browser:

1. Open the menu.
2. Open Projects and filter a sector.
3. Open a certificate.
4. Send one real test enquiry, and delete it afterwards under *Enquiries*.

## 13. Rollback

- **Theme code.** In GitHub, go to *Actions → rollback → Run workflow*. Enter the last good commit on `main` and type `ROLLBACK`. Hostinger's auto-deployment then applies it within a minute or two.
  - **Verify** that hPanel → Git → Deployments shows the new deployment.
  - Then `git revert` the bad change on `main`, or it comes back with the next deploy.
  - If GitHub is unavailable: use hPanel → Git → Deployments → **Redeploy** for a known-good state, or upload the theme zip from a good commit (§4 step 6).
- **Content and database.** Use *hPanel → Backups → Restore → Database only*, choosing the backup from before the problem.
  - Restoring overwrites everything newer, **including enquiries received since then**.
  - Before restoring, export *Enquiries → Download all as CSV*. See `OPERATIONS_RUNBOOK.md`.

## 14. Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| White screen or 500 after a deploy | PHP error in the theme | Run the rollback (§13). Read *hPanel → Advanced → Error logs*. Check that CI was green. |
| Deploy shows Completed but nothing changed | Page cache | LiteSpeed Cache → Purge All. Also check the right branch (`deploy`) and folder. |
| Whole site gone after connecting Git | Deploy directory was `public_html` | Restore the files from Backups. Reconnect Git with `public_html/wp-content/themes/aaykay`. |
| Enquiries saved but "Could not be sent" | No SMTP, or a wrong password or port | Check §6. Read the PHP error log for `[aaykay] email could not be sent: …`. |
| "Too many enquiries…" for real visitors | Many visitors behind one IP (e.g. a Cloudflare proxy) | Limits are 20 emailed and 60 accepted per IP per hour (`inc/enquiry.php`). Behind a proxy, make sure the real visitor IP reaches PHP. |
| Pages 404 except Home | Permalinks not saved | Settings → Permalinks → Save. |
| The no-JS form is blocked by the browser (CSP form-action) | WordPress and Site addresses differ (www vs bare) | Make both addresses identical (§3 step 4). |
| `www` doesn't redirect | `www` not pointed at the site | In hPanel DNS, make `www` a CNAME to the main domain. |

## Sources

- [Hostinger docs: Git deployment](https://docs.hostinger.com/websites/git)
- [Hostinger: How to deploy a Git repository](https://www.hostinger.com/support/1583302-how-to-deploy-a-git-repository-in-hostinger/)
- [Hostinger: Changing the PHP version (8.2–8.5, default 8.3)](https://www.hostinger.com/support/1575755-how-to-change-the-php-version-of-your-hosting-plan)
- [Hostinger docs: SSL and Force HTTPS](https://docs.hostinger.com/websites/ssl)
- [Hostinger docs: Backups](https://docs.hostinger.com/websites/backups)
- [Hostinger docs: Cron jobs](https://docs.hostinger.com/websites/cron-jobs)
- [Hostinger: Email client settings](https://www.hostinger.com/support/4305847-how-to-set-up-hostinger-emails-on-your-devices/)
- [Hostinger: Node.js hosting options (not used)](https://www.hostinger.com/support/node-js-hosting-options-at-hostinger/)
