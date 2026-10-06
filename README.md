# AAYKAY Electricals website (preview build)

A static site with no build step and no third-party requests: fonts, scripts and
images are all in `assets/`. Any static host can serve this folder as-is.

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
1. Create a public repository and upload the contents of this folder (not the folder itself).
2. Settings → Pages → Deploy from branch → `main` / root.

Create the account in the client's name (or move it to them at handover), so AAYKAY owns
the hosting, the domain connection and the credentials.

## Before launch on akepl.in
- In `index.html`, delete `<meta name="robots" content="noindex, nofollow">` and add
  `<link rel="canonical" href="https://akepl.in/">`. The preview is deliberately hidden from search.
- Confirm the office address, phone and email (carried over from the older brochure).
- Connect the enquiry form to a real form handler; today it opens the visitor's email app.
- Swap the drawn "A" mark for the official logo files.
- Confirm AAYKAY is happy to publish order values and the building photos from its profile.
