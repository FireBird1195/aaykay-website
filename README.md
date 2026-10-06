# AAYKAY Electricals website (preview build)

A static, single-page site with no runtime build step and no third-party requests:
fonts, scripts and images are all in `assets/`. Any static host can serve the
repository root as-is. It is currently published with GitHub Pages from `main`.

This preview is the design and content reference for the production site, which is
planned as a lightweight WordPress theme so AAYKAY can add projects themselves.

## What is where

| Path | What it is |
|---|---|
| `index.html` | The page. Hand-written, except the contents of the nav lists, `.client-list`, `.firm-list`, `.sector-list`, `.filters` and `tbody#record-rows` (generated) |
| `data/content.json` | Navigation, sectors (with their icons), clients and firms (with their logos), contact values and the 38-project record (single source) |
| `assets/logos/` | Client and firm logo files (`clients/`, `firms/`), referenced from `data/content.json` |
| `tools/build.py` | Regenerates those regions (and the featured cards' titles/specs) from `data/content.json` and validates the data |
| `assets/css/site.css` | Design tokens (top of file), then one block per section |
| `assets/js/site.js` | Behaviour: scroll position and history, menu, current-section highlight, project-record filter, delivery-stage progress, document viewer, copy buttons, form, motion |
| `assets/vendor/` | GSAP, ScrollTrigger, Lenis (motion only; versions and checksums in its README) |
| `assets/icons/icons.svg` | Icon sprite: a subset of Lucide (ISC), referenced with `<use>`; see `assets/vendor/README.md` |
| `assets/fonts/`, `assets/licenses/` | Self-hosted fonts and third-party licences |
| `assets/img/` | Responsive WebP derivatives (not masters) |

## Changing content

**Projects, sectors, navigation:** edit `data/content.json`, then run

```sh
python3 tools/build.py          # rewrites the generated parts of index.html
python3 tools/build.py --check  # what CI runs: fails if index.html is stale or data is invalid
```

Python 3.8+ only, no packages. The script refuses unknown sector keys, duplicate or
malformed slugs, non-numeric areas, nav links to missing sections, and any phone number
or email address on the page that differs from `company` in the data file.

Project rules: keep a project's `slug` forever once published; leave a field `null` when
the source does not state it (never estimate); always fill `source`. The record is shown
grouped by how complete each row is and alphabetically within each group. It is never
sorted by order value.

**Icons:** one family (Lucide), one stroke weight, always next to words that say the same
thing, so every icon is `aria-hidden`. Markup: `<svg class="ic" aria-hidden="true"><use href="assets/icons/icons.svg#gauge"/></svg>`.
The same concept uses the same icon everywhere (each sector's icon is set once, in
`data/content.json`). `--check` fails if the page or the data names an icon that is not in
the sprite. To add one, copy its inner SVG from lucide.dev into a new `<symbol>` in the sprite.

**Logos (clients and architects/consultants):**

1. Save the logo in `assets/logos/clients/` or `assets/logos/firms/`. SVG is best. Otherwise use a PNG or WebP at least 400 px wide, on a transparent background. Any colour is fine: the page shows every logo in greyscale.
2. In `data/content.json`, set that entry's `"logo"` to the path, for example `"logo": "assets/logos/clients/amazon.svg"`.
3. Run `python3 tools/build.py`.

You don't need to enter a size. The build reads it from the file and gives each logo a balanced size, so a wide wordmark and a square mark carry the same weight. Until a logo is added:

- a client shows its name, set as a wordmark in the same cell;
- a firm shows its initials on the same plate a logo would sit on (`"mark"` overrides the letters).

`--check` fails if a logo path is wrong or its size can't be read. The current logo files and where they came from are listed in `assets/vendor/README.md`.

**Everything else** (copy, photographs, the contact block, the footer) is edited directly
in `index.html`. Do not hand-edit the generated parts; the next build overwrites them. The phone number and email also appear in `data/content.json`; change
both, and `--check` will tell you if any copy was missed.

## Behaviour worth knowing

- **Where the page starts.** The bare URL always opens at the hero, including on reload.
  `#section` URLs open at that section; Back/Forward return to where the reader was.
  In-page links add history entries. (`history.scrollRestoration` is set to `manual` in
  `<head>`; the logic is `initScrollPosition()` in `site.js`.)
- **Project filter in the URL.** `?sector=healthcare#record` opens the record filtered.
- **Motion** loads after the page, only for visitors who have not asked for reduced
  motion, and never hides content that is already on screen.
- **Project record on phones** (760 px and below) is shown as stacked records instead of a
  sideways-scrolling table: CSS only, with units added from hidden `.u` spans. `site.js`
  adds explicit table roles so screen readers keep the table structure.
- **Navigation follows the page.** Header links are in the same order as the sections, and
  the link for the section being read is underlined (`aria-current="location"`, set by
  `initNavSpy()`). Keep the order in `data/content.json` matching the page.
- **Delivery stages** fill along their track as the list scrolls into view
  (`initStages()`). With reduced motion or no JavaScript the track is shown complete.
- **No JavaScript.** All content, all 38 projects and the form (as a mail hand-off) still
  work; the header stays solid and a "Menu" link replaces the menu button.
- **The enquiry form has no backend.** It opens the visitor's email app with the
  enquiry filled in. Leads are not stored anywhere.

## Page weight

The HTML compresses to about 15.6 KB. That is just past the amount a server can send in its
first network round trip (about 14.6 KB including headers), so the first paint on slow
mobile connections waits for one more round trip: about +150 ms in Lighthouse's mobile
model (median of 3 runs: performance 96, FCP 1.7 s, LCP 2.6 s, CLS 0). This was accepted
deliberately for the information-design and logo passes; the earlier 14.3 KB build
measured FCP 2.0 s and LCP 2.7 s. Check `gzip -9c index.html | wc -c`
and Lighthouse when adding content.

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
