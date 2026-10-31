# Operations runbook — AAYKAY Electricals website

What to watch on the live site, and what to do when something breaks. Hosting details are
in `HOSTINGER_DEPLOYMENT_GUIDE.md`.

## What the site depends on

| Part | Lives in | If it fails |
|---|---|---|
| Pages, projects, settings, enquiries | MariaDB on Hostinger | The site shows WordPress's "Error establishing a database connection". The health check returns non-200. |
| Photos AAYKAY uploaded | `wp-content/uploads` on Hostinger | Broken images. Restore the files from Backups. |
| Theme code | GitHub `main` → `deploy` branch → `wp-content/themes/aaykay` | Roll back (below). |
| Enquiry email | SMTP mailbox `website@akepl.in` (wp-config.php) | Enquiries are still saved; they show "Could not be sent". Site Health turns red. |
| Google Sheet (optional) | Google Apps Script | Enquiries are still saved and emailed. The enquiry shows "Could not be added". |
| Cloudflare Web Analytics (optional) | Cloudflare | Nothing visible to visitors. |
| Fonts, icons, scripts | The theme itself (no CDN) | Not applicable. |

Nothing on the page depends on a third-party server at load time.

## Health checks

- **Uptime monitor.** Use a free UptimeRobot, Better Stack or similar account in the company's name. Point it at `https://akepl.in/wp-json/aaykay/v1/health` every 5 minutes.
  - It answers `200 {"status":"ok"}` when PHP and the database work, and is never cached.
  - Add a second monitor on `https://akepl.in/` with the keyword "AAYKAY".
  - Send alerts to the developer and one office address.
- **Weekly (2 minutes).**
  - Open **Tools → Site Health** and check the three "AAYKAY website" checks: enquiry email, launch switches and privacy notice.
  - Open **Enquiries** and look for any marked "No" (not emailed) or "Held".
- **After every deploy:** run `tests/bin/smoke.sh https://akepl.in`.
- **Monthly.**
  - Check that **Dashboard → Updates** shows no pending WordPress core or LiteSpeed Cache updates. Apply them.
  - Check that a backup from the last week exists (**hPanel → Backups**).

## Logs

| Log | Where | Contents |
|---|---|---|
| PHP errors | **hPanel → Websites → Manage → Advanced → Error logs** (or the account's `logs/` folder in File Manager) | Fatal errors, and `[aaykay] email could not be sent: <reason>`. Addresses are redacted; no visitor data. |
| Access log | **hPanel → Advanced → Access logs** (if the plan offers it) | Traffic, 404s, attacks |
| WordPress debug log | Off in production. If needed for a short time: `WP_DEBUG_LOG` pointing **outside** `public_html` | Notices, deprecations |
| Deployments | **hPanel → Git → Deployments**, and **GitHub → Actions** | What was deployed and when |

## Common failures

**1. "The site is down" (monitor alert)**

1. Open `https://akepl.in/wp-json/aaykay/v1/health`.
   - **500 or white page.** Check the PHP error log for the file and line.
     - If the error is in `themes/aaykay`, roll back the theme (below).
     - If it is in WordPress or a plugin, deactivate the plugin by renaming its folder in File Manager.
   - **"Error establishing a database connection".** Check hPanel → Databases; the database may be over its limit or locked. Check `DB_*` in wp-config.php. Open a Hostinger support chat.
   - **503 / "Resource limit reached".** Check hPanel → Websites → Manage → Resource usage (CPU, entry processes). Check LiteSpeed Cache is on, and look for a bot flood in the access log. Block the bot's IPs in hPanel → Security → IP Manager.
2. Then run `tests/bin/smoke.sh https://akepl.in`.

**2. Enquiries not arriving by email**

1. Every enquiry is under **Dashboard → Enquiries**. Reply from there first.
2. Site Health says how many failed in the last 30 days.
3. The PHP error log says why, for example `Could not authenticate` (wrong password) or `Could not connect` (port blocked: try 587 with `AAYKAY_SMTP_SECURE 'tls'`).
4. Check the mailbox in hPanel → Emails: it may be out of quota or suspended.
5. Send a test enquiry.

**3. A spam wave**

- Enquiries marked **Held** were sent too fast, or are past the 20th from one connection in an hour. They were not emailed.
- More than 60 an hour from one connection are refused.
- Bin the spam.
- If spam keeps getting through, add Cloudflare Turnstile (`ENGINEERING_RESEARCH.md`, option B3). Do not add a nonce: cached pages would break real enquiries.

**4. Wrong content after an edit**

Use **Homepage content → Undo last save** (one step). For projects, use the post's revisions. For older states, see "Database restore" below.

**5. Third-party outage**

- **Google Sheets down:** nothing to do. Rows are not back-filled automatically; use **Enquiries → Download all as CSV** to fill the gap.
- **Hostinger mail down:** enquiries are still saved.

## Deploy and rollback

- **Deploy.** Merge to `main`. GitHub Action `check` runs: lint, phpcs, secrets, then integration and browser tests on PHP 8.3, 8.4 and 8.5. Only then does `deploy` publish `theme/` to the `deploy` branch. Hostinger auto-deploys it. Then purge LiteSpeed Cache and run the smoke test.
- **Roll back the theme:**
  1. In GitHub, go to **Actions → rollback → Run workflow**. Enter the last good commit SHA from `main` and type `ROLLBACK`.
  2. The workflow adds a commit on top of `deploy` holding that version's files. Hostinger pulls it like a normal deploy.
  3. Check **hPanel → Git → Deployments**, then run the smoke test.
  4. **Then `git revert` the bad commit on `main`.** The next normal deploy would otherwise bring it back.
- **No GitHub:** download the theme zip of a good commit (`git archive <sha> theme`). Upload it with File Manager into `wp-content/themes/aaykay`, replacing the files.

## Backups and restore

- **Hostinger automatic backups:** weekly on every plan, daily on plans or add-ons that include it. They cover files and databases, **not mailboxes**. Manual backups are limited to one every 24 hours.
- **Before any risky change, take your own copies.**
  1. **Enquiries → Download all as CSV.**
  2. **Tools → Export → All content.**
  3. **hPanel → Backups → Generate new backup** (Business plan and up), or **hPanel → Databases → phpMyAdmin → Export**.
- **Database restore.** Go to **hPanel → Backups → choose a date → Restore → Database only**.
  - ⚠️ This overwrites everything newer, including enquiries received since then. Download the CSV first and re-enter anything that was lost.
- **Files restore** (uploads lost) uses the same steps with **Files only**. Theme files come back from Git anyway.
- **Test a restore once a year.**
  1. Create a free Hostinger subdomain site (staging).
  2. Restore the latest backup into it (download the backup, then import the DB and files).
  3. Check that the six pages and the latest enquiry are there.
  4. Delete the staging site.
  5. Note the date here.

  *Last tested: never (no live site yet).*

## Data protection

- **Requests from people.** When someone asks to see or delete their details: **Tools → Export Personal Data** or **Erase Personal Data**, then enter their email address. This covers enquiries, including any in the bin.
- **Retention.** Set it in **Site settings → Delete enquiries after (months)**. A daily job deletes older enquiries. Copies in mailboxes and the Google Sheet are **not** deleted by it; clear them by hand on the same schedule.
- **Accounts.** Every staff account is personal. Use the **Editor** role for staff. The developer has a separate Administrator account. Remove leavers the same day.

## Security maintenance

- Keep WordPress core and LiteSpeed Cache updated (monthly). Keep PHP on a supported 8.x.
- Have no other plugins. Before adding one, read `ENGINEERING_RESEARCH.md` §evaluation criteria.
- Use passwords plus Hostinger account 2FA. Consider 2FA for WordPress admins too.
- If a secret leaks (SMTP password, Sheets secret code, salts):
  1. Change it at the source: the hPanel mailbox, or Site settings for the Sheets code.
  2. Update `wp-config.php` or the Apps Script.
  3. For salts, regenerate them in wp-config.php; this logs everyone out.

## Contacts

| Who | Role | How |
|---|---|---|
| Developer | Code, deploys, Hostinger technical | (fill in) |
| AAYKAY office | Content, enquiries, approvals | (fill in) |
| Hostinger support | Hosting | hPanel → Help (24/7 chat) |
