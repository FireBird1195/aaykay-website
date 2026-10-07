# AAYKAY Electricals WordPress theme

A custom classic theme for one page. Everything a visitor reads or sees (texts, photos, icons,
logos, projects, figures) is edited in the WordPress dashboard; this theme holds the design
and the structure. No plugins are required and none are bundled.

## Who edits what

| Content | Where it is edited | By |
|---|---|---|
| Projects (record rows and the "Selected work" cards, with photos) | Dashboard → Projects | AAYKAY |
| Sectors (name, one-line description, example clients, icon, order) | Dashboard → Projects → Sectors | AAYKAY |
| Client logos | Dashboard → Client logos | AAYKAY |
| Architects, consultants and PMCs | Dashboard → Architects & consultants | AAYKAY |
| Every heading, paragraph, list, photo, icon and button label, the logo, menu labels, and which sections are shown | Dashboard → Homepage content | AAYKAY |
| Phone, email, address, branch states, clients served, turnover, head counts, CIN/GSTIN, order-value switch, Google Sheets link, analytics, search title and description | Dashboard → Site settings | AAYKAY |
| Enquiries from the form | Dashboard → Enquiries (email, CSV download, optional Google Sheet) | AAYKAY reads |
| Site icon (favicon) | Appearance → Customize → Site Identity | AAYKAY |
| Layout, styles, behaviour, form field names, structural labels (e.g. table column headings) | `template-parts/`, `assets/`, `inc/` | Developer, through Git |

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
| `inc/content.php` | Homepage content: the schema of every section and field, defaults, getters (`aaykay_c()`, `aaykay_img()`), placeholders |
| `inc/admin-content.php` | The Homepage content editor screen; `assets/admin/` holds its script and styles |
| `inc/leads.php` | Enquiries to Google Sheets (optional) and the CSV download |
| `inc/content-types.php` | Projects, Sectors, Client logos, Architects & consultants; field lists |
| `inc/admin-fields.php` | Edit screens, list columns, warnings, dashboard box (dashboard only) |
| `inc/settings.php` | Site settings page and the derived values (`aaykay_contact()`, `aaykay_turnover()`…) |
| `inc/template-data.php` | Queries and the markup for repeated parts (nav, sectors, chips, record, cards, logos) |
| `inc/enquiry.php` | Enquiry form handler: validation, spam checks, saving, email |
| `inc/setup.php` | Supports, scripts and styles, a lean `<head>`, hardening |
| `inc/seo.php` | Title, description, canonical, social tags, schema.org data |
| `inc/starter-content.php` | Imports `seed/content.json` once on activation; Tools → AAYKAY starter content |
| `inc/helpers.php` | Small shared functions |
| `seed/` | Launch content (settings, homepage content, projects, logos, firms) and the five card photos. The homepage content in it is also the fallback until the editor is saved |
| `assets/` | CSS, JS, fonts, icon sprite, images, logos, third-party notes in `assets/THIRD-PARTY.md` |

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
  It has a honeypot, a 3-second minimum (required for script-sent forms) and per-connection
  limits (over 20 an hour: saved, not emailed; over 60: refused). Field formats are checked
  on both sides (`aaykay_enquiry_check_formats()` and the matching rules in `site.js`; keep
  them in step). Every valid enquiry is saved before it is emailed or sent to Google Sheets.
- **Adding a homepage field**: add one line to `aaykay_content_schema()`, a default under
  `content` in `seed/content.json`, and print it in the template with `aaykay_e()` /
  `aaykay_img()`. The editor, saving and escaping follow automatically.
- **Scrolling**: a fresh visit starts at the top, a reload returns to the same place
  (sessionStorage), Back returns to the saved place; in-page links keep the address clean.
  See `initScrollPosition()` in `site.js`. No smooth-scroll or animation libraries.
- **Photos uploaded as JPEG or PNG** get WebP resized copies when the server supports it
  (`image_editor_output_format` in `inc/setup.php`).

## Local development

Any WordPress 6.5+ with PHP 7.4+ works. Symlink or copy this folder to
`wp-content/themes/aaykay`, activate it, and the starter content imports itself.

## Before changing something

Check the page at 390 px and 1440 px, with and without JavaScript, and with reduced motion;
submit the form; and open the edit screens you touched. `php -l` every PHP file and
`node --check assets/js/site.js` (CI does both on every push).
