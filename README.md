# AAYKAY Electricals website

The website for AAYKAY Electricals Private Limited (akepl.in): a custom WordPress theme.
AAYKAY edits every word, photo, icon, project, logo and company fact in the WordPress
dashboard (Homepage content, Site settings, Projects, Clients, Consultants); design and
code changes go through this repository and deploy automatically. No plugins are required.

| Path | What it is |
|---|---|
| `theme/` | The WordPress theme. Everything that goes to the server. See `theme/README.md`. |
| `.github/workflows/check.yml` | On every push: PHP syntax check, JavaScript check, starter-data check |
| `.github/workflows/deploy.yml` | On every push to `main`: copies `theme/` to the `deploy` branch |
| `docs/HANDOFF.md` | Short state-and-rules note for the next developer or AI session |
| `docs/google-sheets-apps-script.gs` | Script pasted into AAYKAY's Google Sheet so enquiries also land in a sheet (not deployed) |

The original static preview is kept at the Git tag `static-preview-final`.

## How a change reaches the live site

```
edit theme/ on a branch → pull request → merge to main
      → GitHub Action "deploy" publishes theme/ to the deploy branch
      → Hostinger (auto-deployment on) pulls the deploy branch into
        public_html/wp-content/themes/aaykay
```

Content (projects, photos, settings, enquiries) lives in the WordPress database and the
uploads folder on Hostinger, never in Git, so a code deploy never touches it.

The `deploy` branch holds only the theme files at its root, so Hostinger can place it
straight into the theme folder. Never commit to `deploy` by hand; it is rewritten from
`main` by the workflow (`git subtree split`, so its history only moves forward).

## First-time setup on Hostinger

The step-by-step version, with screenshots, is in the client guide (PDF). In short:

1. Buy the domain and a Web Hosting plan in AAYKAY's own Hostinger account.
2. Install WordPress from hPanel. Settings → Reading → tick "Discourage search engines"
   until launch.
3. Push this repository to GitHub so the deploy workflow creates the `deploy` branch.
4. hPanel → Websites → Dashboard → Advanced → Git → Connect with GitHub → this repository,
   branch `deploy`, folder `public_html/wp-content/themes/aaykay` → Deploy. Turn on
   auto-deployment.
5. WordPress → Appearance → Themes → activate "AAYKAY Electricals". The launch content
   (38 projects, 14 clients, 31 firms, settings) imports itself.
6. Create AAYKAY's own user accounts; keep a separate administrator account for the developer.

If Git deployment is unavailable, upload `aaykay-theme.zip` (the `theme/` folder zipped
as `aaykay/`) under Appearance → Themes → Add New → Upload Theme.

## Launch checklist

- Confirm the address, phone and email (carried over from the older brochure) in Site settings.
- Confirm AAYKAY is happy to publish order values, turnover, head counts, client names and photos.
- Connect the Google Sheet (Site settings → Leads, steps in the guide) and press
  "Send a test row". Every enquiry is also kept under Dashboard → Enquiries (CSV download).
- Send a test enquiry and confirm it arrives by email. If it doesn't, the enquiry is still
  under Dashboard → Enquiries; set up an SMTP mailbox (see the guide) so email is reliable.
- Swap the drawn "A" mark for the official logo when AAYKAY supplies it.
- Untick "Discourage search engines", then submit `https://akepl.in/wp-sitemap.xml` in
  Google Search Console.
- Never change DNS, MX or other records for `akelectricals.in` (company email) as part of
  website work.

## Behaviour worth knowing

- **Scroll position.** Opening the site starts at the top; a refresh or Back returns to the
  exact place. Menu links change the address bar without `#hash` (so a shared link opens
  at the top). Code: `initScrollPosition()` and `initAnchors()` in `theme/assets/js/site.js`.
- **Enquiry form.** Checked in the browser and again on the server (`inc/enquiry.php`):
  names letters only, phone digits only, valid email, honeypot, minimum fill time, per-IP
  limits. Saved first, then sent to Google Sheets, then emailed.
- **No animation libraries.** GSAP, ScrollTrigger and Lenis were removed; reveals use
  IntersectionObserver and respect "reduce motion".
