# AAYKAY Electricals WordPress theme

A custom classic theme for one page. WordPress stores the content that changes; this theme
holds the design and the copy that doesn't. No plugins are required and none are bundled.

## Who edits what

| Content | Where it is edited | By |
|---|---|---|
| Projects (record rows and the "Selected work" cards, with photos) | Dashboard → Projects | AAYKAY |
| Sectors (name, one-line description, example clients, icon, order) | Dashboard → Projects → Sectors | AAYKAY |
| Client logos | Dashboard → Client logos | AAYKAY |
| Architects, consultants and PMCs | Dashboard → Architects & consultants | AAYKAY |
| Phone, email, address, branch states, clients served, turnover, head counts, search title and description | Dashboard → Site settings | AAYKAY |
| Enquiries from the form | Dashboard → Enquiries (and email) | AAYKAY reads |
| Everything else: layout, section copy, method statements, testing kit, photos of sites, founder, timeline, documents | `template-parts/home/*.php` and `assets/` | Developer, through Git |

Facts entered once in Site settings are used everywhere they appear (for example the phone
number is in the phone menu, the contact section, the footer and the structured data).

## Files

| Path | What it is |
|---|---|
| `functions.php` | Loads `inc/`; lists what each file does |
| `front-page.php` | The page: header, the sections in order, footer |
| `template-parts/home/` | One file per section, in page order |
| `header.php`, `footer.php` | Head, header, phone menu; footer and the document viewer |
| `index.php`, `404.php` | Any other page (e.g. a privacy policy) and "not found" |
| `inc/content-types.php` | Projects, Sectors, Client logos, Architects & consultants; field lists |
| `inc/admin-fields.php` | Edit screens, list columns, warnings, dashboard box (dashboard only) |
| `inc/settings.php` | Site settings page and the derived values (`aaykay_contact()`, `aaykay_turnover()`…) |
| `inc/template-data.php` | Queries and the markup for repeated parts (nav, sectors, chips, record, cards, logos) |
| `inc/enquiry.php` | Enquiry form handler: validation, spam checks, saving, email |
| `inc/setup.php` | Supports, scripts and styles, a lean `<head>`, hardening |
| `inc/seo.php` | Title, description, canonical, social tags, schema.org data |
| `inc/starter-content.php` | Imports `seed/content.json` once on activation; Tools → AAYKAY starter content |
| `inc/helpers.php` | Small shared functions |
| `seed/` | Launch content and the five card photos, read only by the importer |
| `assets/` | CSS, JS, fonts, icon sprite, images, logos, vendor scripts (see `assets/vendor/README.md`) |

## Rules worth keeping

- **Never estimate a fact.** Leave a project field empty when the source doesn't state it.
  The record is grouped by how complete each row is and is never sorted by order value.
- **Escape on output.** Every renderer escapes what it prints (`esc_html`, `esc_attr`,
  `esc_url`); keep it that way. Raw titles (`$post->post_title`) are used, then escaped.
- **Project slugs are permanent** once published (the importer matches on them).
- **Icons** come from one sprite (`assets/icons/icons.svg`, Lucide), always next to words,
  always `aria-hidden`. Use `aaykay_icon( 'gauge' )`. To add one, copy its inner SVG from
  lucide.dev into a new `<symbol>`; it then appears in the sector icon picker.
- **Logos** uploaded in WordPress must be PNG or WebP (WordPress blocks SVG uploads for
  security). The SVG logos imported at launch are files in `assets/logos/` referenced by
  the meta field `_aaykay_bundled_logo`; to add another SVG, put it there and set that field.
- **The enquiry form has no nonce on purpose** (cached pages would serve expired nonces).
  It has a honeypot, a 3-second minimum and a limit of five an hour per address. Every
  valid enquiry is saved before it is emailed.
- **Photos uploaded as JPEG or PNG** get WebP resized copies when the server supports it
  (`image_editor_output_format` in `inc/setup.php`).

## Local development

Any WordPress 6.5+ with PHP 7.4+ works. Symlink or copy this folder to
`wp-content/themes/aaykay`, activate it, and the starter content imports itself.

## Before changing something

Check the page at 390 px and 1440 px, with and without JavaScript, and with reduced motion;
submit the form; and open the edit screens you touched. `php -l` every PHP file and
`node --check assets/js/site.js` (CI does both on every push).
