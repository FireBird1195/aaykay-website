# Handoff notes (for the next developer or AI session)

The full explanation is the PDF **AAYKAY developer handbook** (kept with the hand-off files and in
the claude.ai project "Aakay Electricals"). This page is the short version.

## State (13 Oct 2026, client review): branch `feat/review-oct13`
On top of `feat/final-brief-oct13` (not merged). Version 4 of the content (`AAYKAY_DATA_VERSION`;
`aaykay_upgrade_4()` updates a version 3 site, replacing only values still as launched).
- Home: slideshow with previous/next arrows (progress bars added in the third round), no buttons and no key
  figures; About Us without "Why AAYKAY"; timeline as a compact row of years (no animation, so
  it does not repeat the methodology track); leaders one row each, photo left and words right
  (Managing Director, then Shakeel Shuaib Ambur), then the project and office team; project
  methodology with the five-stage track first and the planning text under it (the works
  schedule and the tools sheet are removed).
- Contact details and the form are on Contact Us only. Every page's footer has the address,
  phones and email with copy buttons, and quick links.
- The menu follows the reader: on the home page About Us and its dropdown entry are marked
  for the section on screen (site.js `initNavSpy`); the same on Projects.
- Quality & Safety: all photos (quality and site safety) in one gallery first, then the ISO
  certificates and practice; testing equipment list removed; new "Certifications & Licences"
  (ISO 9001, ISO 45001, Telangana and Karnataka contractor licences, incorporation, MSME,
  scanned from the company profile p.9); no pre-qualification request button.
- Projects: architects, consultants & PMCs as names only.
- Image viewer: scans and photos in a set open with previous/next, "3 of 8", arrow keys.
- Our Capabilities: "Scope of Works" renamed "Works We Undertake"; first card "HT/LT Panels &
  Distribution".
- Browser tab icon made from the AAYKAY mark (assets/favicon.ico, favicon-32.png, icon-*.png).
- Section spacing reduced (`--section-y`); two sections on the same background share padding.
- Not published on purpose: PAN card, ESI and PF scans from the profile p.9 (identity
  numbers; GST was added later, see the third round). They can be added in Homepage content > Certifications & licences if AAYKAY wants.

## State (13 Oct 2026, third round): branch `feat/review-oct13`
Version 5 of the content (`AAYKAY_DATA_VERSION`, `aaykay_upgrade_5`, `seed/upgrade-5.json`).
- Opening headline: "Complete electrical installations, from design to commissioning." (three
  lines, red full stop). Headline size (`--fs-hero`) now follows the longest line, so it holds
  on three lines from tablet up and the longest word fits a 320px phone.
- Slideshow bars work like Instagram stories: the bar of the photo on screen fills over 7
  seconds (`--hero-dur` in site.css), earlier bars are full, later bars empty; the photo changes
  when the fill ends, pausing pauses the fill. Bars now show on phones too (own row above the
  arrows). With reduced motion the slideshow does not run and the current bar is shown full.
- Our Capabilities: the "to come" box on a card is replaced by one line, "For more
  information click here" ("here" is the link, opens in a new tab). A card with a link note
  but no address shows the line only while placeholders are on (Site settings > Placeholders).
- Certifications & Licences: GST registration certificate added (page 1 of the certificate
  only; the annexures list directors' personal details and are not published). Placed last,
  after MSME. This reverses the earlier "GST not published" note, at the client's request.
- Visual audit (320 to 1920px, all pages): project list tags (sector, "executed via") now sit
  on their own lines in every row; the sideways-scroll hint of the project table now shows
  below 1200px (it scrolls from there); the rule above Our Branches lines up with the content.
- Not changed, for the client to decide: Certifications now has 7 documents, so the last row
  of the three-column grid holds one card.

## Client preview on GitHub Pages (13 Oct 2026)
GitHub Pages cannot run WordPress, so the public preview is a snapshot: `tools/export-preview.py`
fetches the six pages from a running copy of the site and writes plain HTML, with the theme files
they use, to a folder. That folder is the whole of the orphan branch `gh-pages` (nothing else is on
it; never merge it). Pages are noindex, a thin bar says it is a preview, and the enquiry form is
switched off. Rebuild after any change: start the site, run
`python3 tools/export-preview.py http://localhost:8080 OUT_DIR https://firebird1195.github.io/aaykay-website`,
replace the contents of `gh-pages` with OUT_DIR, commit and push. In the repository's Settings >
Pages, publish from branch `gh-pages`, folder `/ (root)`.
- Map on Contact Us: hovering a pin shows the state's name above it (CSS only, `.map-tip`).
- The empty "site team member in full PPE" photo slot on Quality & Safety is removed (also from
  sites with saved content, by `aaykay_upgrade_5`).

## State (13 Oct 2026, second round): branch `feat/final-brief-oct13`
On top of `feat/client-brief-oct10` (not merged). Version 3 of the content (`AAYKAY_DATA_VERSION`).
- Pages: Home (slideshow of ten site photos, then About Us: company and "Our work", timeline,
  team, project methodology with the electrical works schedule), Our Capabilities (nine cards,
  Fire Safety ninth, site gallery), Projects (sectors, project list with a status column,
  clients, architects/consultants/PMCs, appreciation certificates, client letter), Quality &
  Safety (unchanged section, site safety photos, compliance & pre-qualification), Careers (open
  positions with domain filter and job descriptions, employee welfare, how to apply), Contact Us
  (both phones and email with copy buttons, form, branches map). Every page except Contact Us
  ends with the enquiry form.
- Menu: Home, About Us (dropdown to the home sections), Our Capabilities, Projects (dropdown),
  Quality & Safety, Careers, Contact Us. No dropdowns on phones. Old addresses (/about-us/,
  /projects/clients/…) redirect (301) to the new place.
- Brochure tab on the left edge (Site settings > Brochure); launch file: the Aug 2026 company
  profile, images recompressed (6.9 MB), which still shows turnover.
- Written for client approval (see the pull request): Fire Safety card text, the four sample
  open positions, the Managing Director's message (copyedited from profile p.6), "Why AAYKAY"
  wording, formal headings.

- Branch `feat/client-brief-oct10` (not merged): the client brief of 10 Oct 2026 on top of
  version 1. Ten pages instead of one, the client's project list (95 projects, alphabetical),
  no turnover or order values, full company name with the AEK mark, second director, Careers,
  Safety Solutions, Appreciations; "to come" placeholders for content still awaited.
  Open questions are listed in the pull request / report.

## State (7 Oct 2026)
- Code complete: WordPress classic theme in `theme/`, no plugins. Every homepage text, photo and icon
  is edited in Dashboard → Homepage content; facts in Site settings; projects, sectors, clients and
  firms as their own types; enquiries saved, optionally copied to Google Sheets, and emailed.
- Not launched. Waiting on the client: Hostinger account + akepl.in in the company's name,
  Collaborator access, written approval for order values / turnover / head counts / client names,
  photo rights, official logo, confirmed contact details, MEP wording, ISO evidence.
- Make this repository private (or move it to a company organisation) before launch.

## How things move
- Code: branch → PR → `main` → Action `check` → Action `deploy` (subtree split of `theme/` to the
  `deploy` branch) → Hostinger Git auto-deploy into `wp-content/themes/aaykay`.
- Content lives only in WordPress on Hostinger, never in Git.

## Rules
- Never invent facts. Empty is better than wrong; templates hide empty values.
- No plugins, libraries or build steps without a written reason.
- Prefix `aaykay_`; sanitise input, escape output; nonces and capability checks on admin actions.
- New visible content goes through the schema in `theme/inc/content.php` with a default in
  `theme/seed/content.json`.
- Never change DNS/MX for `akelectricals.in` (company email). Never link `akee.in`.

## Next steps
1. Push `main` (from the hand-off bundle) and check the Actions are green.
2. Repo private. 3. Client decisions + Hostinger purchase. 4. Install (client guide Part B),
   connect the Google Sheet, SMTP, test enquiry. 5. Content session with Shueb. 6. Launch day
   (guide B8), Search Console, PageSpeed on the real host.
