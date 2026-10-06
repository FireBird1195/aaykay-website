# AAYKAY Electricals website (preview build)

A static, single-page site with no runtime build step and no third-party requests:
fonts, scripts and images are all in `assets/`. Any static host can serve the
repository root as-is. It is currently published with GitHub Pages from `main`.

This preview is the design and content reference for the production site, which is
planned as a lightweight WordPress theme so AAYKAY can add projects themselves.

## What is where

| Path | What it is |
|---|---|
| `index.html` | The page. Hand-written, except the regions between `<!-- gen:… -->` markers |
| `data/content.json` | Navigation, sectors, contact values and the 38-project record (single source) |
| `tools/build.py` | Regenerates the marked regions of `index.html` from `data/content.json` and validates the data |
| `assets/css/site.css` | Design tokens (top of file), then one block per section |
| `assets/js/site.js` | Behaviour: scroll position and history, menu, project-record filter, document viewer, copy buttons, form, motion |
| `assets/vendor/` | GSAP, ScrollTrigger, Lenis (motion only; versions and checksums in its README) |
| `assets/fonts/`, `assets/licenses/` | Self-hosted fonts and third-party licences |
| `assets/img/` | Responsive WebP derivatives (not masters) |

## Changing content

**Projects, sectors, navigation:** edit `data/content.json`, then run

```sh
python3 tools/build.py          # rewrites the generated regions of index.html
python3 tools/build.py --check  # what CI runs: fails if index.html is stale or data is invalid
```

Python 3.8+ only, no packages. The script refuses unknown sector keys, duplicate or
malformed slugs, non-numeric areas, nav links to missing sections, and any phone number
or email address on the page that differs from `company` in the data file.

Project rules: keep a project's `slug` forever once published; leave a field `null` when
the source does not state it (never estimate); always fill `source`. The record is shown
grouped by how complete each row is and alphabetically within each group. It is never
sorted by order value.

**Everything else** (copy, photographs, the contact block, the footer) is edited directly
in `index.html`. The phone number and email also appear in `data/content.json`; change
both, and `--check` will tell you if any copy was missed.

## Behaviour worth knowing

- **Where the page starts.** The bare URL always opens at the hero, including on reload.
  `#section` URLs open at that section; Back/Forward return to where the reader was.
  In-page links add history entries. (`history.scrollRestoration` is set to `manual` in
  `<head>`; the logic is `initScrollPosition()` in `site.js`.)
- **Project filter in the URL.** `?sector=healthcare#record` opens the record filtered.
- **Motion** loads after the page, only for visitors who have not asked for reduced
  motion, and never hides content that is already on screen.
- **No JavaScript.** All content, all 38 projects and the form (as a mail hand-off) still
  work; the header stays solid and a "Menu" link replaces the menu button.
- **The enquiry form has no backend.** It opens the visitor's email app with the
  enquiry filled in. Leads are not stored anywhere.

## Testing

Before merging, at minimum: `python3 tools/build.py --check`, then check in a browser at
phone and desktop widths that the page opens at the hero, the menu works, a sector filter
works and the form validates. The PR description for the quality pass lists the full
regression set that was run (Playwright, axe-core, Lighthouse).

## Put it online for free (pick one)

**Netlify Drop (fastest, about a minute)**
1. Open https://app.netlify.com/drop
2. Drag this whole folder onto the page. You get a live `*.netlify.app` link straight away.
3. Create a free account when prompted so the site isn't deleted, then rename the site under
   Site configuration → Site details.

**Cloudflare Pages**
1. dash.cloudflare.com → Workers & Pages → Create → Pages → Upload assets.
2. Name the project, upload this folder, Deploy. You get a `*.pages.dev` link.

**GitHub Pages**
1. Create a repository and upload the contents of this folder (not the folder itself).
2. Settings → Pages → Deploy from branch → `main` / root.

Create the account in the client's name (or move it to them at handover), so AAYKAY owns
the hosting, the domain connection and the credentials.

## Before launch on akepl.in
- Register `akepl.in` in the company's name (it does not resolve today).
- In `index.html`, delete `<meta name="robots" content="noindex, nofollow">` and add
  `<link rel="canonical" href="https://akepl.in/">`. The preview is deliberately hidden from search.
- Confirm the office address, phone and email (carried over from the older brochure).
- Connect the enquiry form to a real form handler; today it opens the visitor's email app.
- Swap the drawn "A" mark for the official logo files (and add a logo to the JSON-LD).
- Confirm AAYKAY is happy to publish order values, turnover, headcounts, client names and
  the building photos from its profile.
- Never touch DNS, MX or other records for `akelectricals.in` (company email) as part of
  website work.
