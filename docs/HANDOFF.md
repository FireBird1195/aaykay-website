# Handoff notes (for the next developer or AI session)

The full explanation is the PDF **AAYKAY developer handbook** (kept with the hand-off files and in
the claude.ai project "Aakay Electricals"). This page is the short version.

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
