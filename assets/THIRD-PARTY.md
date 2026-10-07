# Third-party files

Everything the site loads is served from the theme. There are no CDN or third-party
requests.

## JavaScript

None. `js/site.js` is the theme's own code. The GSAP, ScrollTrigger and Lenis motion
libraries used by the static preview were removed in the WordPress build (audit dossier §30:
the reveals are now CSS transitions started by an IntersectionObserver, and the browser
scrolls natively).

## Fonts (`fonts/`)

From Fontsource 5.3.0 (`@fontsource/big-shoulders-display`, `@fontsource/ibm-plex-sans`,
`@fontsource/ibm-plex-mono`), latin and latin-ext subsets, `files/*.woff2`. SIL Open Font
License 1.1: [`licenses/`](licenses/).

## Icons (`icons/icons.svg`)

A hand-picked subset of [Lucide](https://lucide.dev) 1.52.0 (`lucide-static`), copied
into one SVG sprite of `<symbol>`s. ISC licence: [`licenses/lucide-ISC.txt`](licenses/lucide-ISC.txt).
Only the geometry is copied (stroke attributes are removed and set by `.ic` in `site.css`),
so these are not byte-identical to the package files. `earth-ground` is not from Lucide:
it is the standard earth symbol drawn on the same 24 × 24 grid.

No npm dependency and no JavaScript: pages reference a symbol with
`<svg class="ic" aria-hidden="true"><use href="assets/icons/icons.svg#gauge"/></svg>`.
The sprite is about 2 KB gzipped and cached after the first visit.

## Logos (`logos/`)

Client and firm logos are trademarks of their owners and are shown with AAYKAY's
permission. The page greyscales them with CSS, so the files keep their original colours.

| File | Source |
|---|---|
| `clients/ibm.svg`, `clients/microsoft.svg`, `clients/qualcomm.svg` | svg-logos by Gil Barbara (CC0), via `@iconify-json/logos` 1.2.15 |
| `clients/shell.svg` | Simple Icons 16.34.0 (CC0) |
| `clients/amazon.svg` | Simple Icons (CC0) brand mark, supplied by Tejas, 7 Oct 2026 |
| `clients/hdfc-bank.svg`, `clients/servicenow.webp`, `clients/medtronic.webp`, `clients/cognizant.webp`, `clients/walmart.webp` | Wikimedia Commons, downloaded by Tejas, 7 Oct 2026. The WebP files are Commons renders, trimmed and resized to 400 px (Walmart's stacked 2025 version is 250 px as supplied). |
| `clients/gmr-hyderabad-airport.svg` | hyderabad.aero (official site) |
| `clients/my-home-group.svg` | myhomeconstructions.com (official site). The site's file has white letters for dark backgrounds, so they were set to dark navy for the white strip; the red mark is unchanged. |
| `clients/yashoda-hospitals.svg` | Auto-traced (VTracer) from a small PNG of the official logo; replace with an original vector when AAYKAY or Yashoda supplies one. |

Record any new logo file here with where it came from (ideally the company's own brand or
press page).
