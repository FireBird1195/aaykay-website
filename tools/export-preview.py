#!/usr/bin/env python3
"""
Exports a running copy of the website as plain HTML for a public client preview
(GitHub Pages). Pages cannot run WordPress, so this takes a snapshot of the pages
and the theme files they use; nothing here is part of the real website or its deploy.

    python3 tools/export-preview.py SOURCE_URL OUT_DIR PREVIEW_URL

    SOURCE_URL   the running site, e.g. http://localhost:8080 (no trailing slash)
    OUT_DIR      an empty folder; the snapshot is written into it
    PREVIEW_URL  where the snapshot will be served, e.g.
                 https://firebird1195.github.io/aaykay-website

What it does
  - fetches the six pages, writes each as <slug>/index.html (home as index.html);
  - rewrites every SOURCE_URL in them (links, images, canonical, structured data) to PREVIEW_URL;
  - copies every /wp-content/ file the pages, the stylesheet and the script name
    (fonts, images, icons, the brochure PDF), keeping the same paths;
  - marks each page noindex (it is a preview, not the site) and shows a thin banner;
  - stops the enquiry form from posting (a static host has no server to receive it) and says so;
  - adds .nojekyll (serve files as they are) and a 404 page.
Python 3.8+, no packages. Run it again after any change; the output is rebuilt from scratch.
"""
import os, re, sys, shutil, urllib.request, urllib.parse

PAGES = ['', 'capabilities/', 'projects/', 'quality-safety/', 'careers/', 'contact-us/']

BANNER_CSS = ('<style>.preview-bar{position:fixed;z-index:2147483647;left:0;right:0;bottom:0;'
              'padding:.45rem 1rem;background:#0b1224;color:#fff;font:500 12px/1.4 system-ui,sans-serif;'
              'text-align:center;border-top:1px solid #c8321f}</style>')
BANNER = ('<div class="preview-bar" role="note">Client preview of the AAYKAY website. '
          'Placeholders marked &ldquo;To come&rdquo; are content still to be supplied. '
          'The enquiry form is switched off in this preview.</div>')
FORM_JS = ('<script>document.addEventListener("submit",function(e){var f=e.target;'
           'if(!f||f.id!=="enquiry")return;e.preventDefault();e.stopImmediatePropagation();'
           'var n=f.querySelector(".preview-note");if(!n){n=document.createElement("p");'
           'n.className="preview-note";n.setAttribute("role","status");n.style.marginTop="1rem";'
           'f.appendChild(n);}n.textContent="Preview only: the enquiry form works on the live website.";},true);</script>')


def get(url):
    with urllib.request.urlopen(url, timeout=60) as r:
        return r.read()


def main(src, out, dest):
    src, dest = src.rstrip('/'), dest.rstrip('/')
    if os.path.exists(out) and os.listdir(out):
        sys.exit('OUT_DIR must be empty: ' + out)
    os.makedirs(out, exist_ok=True)
    esc_src = src.replace('/', r'\/')
    esc_dest = dest.replace('/', r'\/')
    files = set()

    def rewrite(text):
        return text.replace(src, dest).replace(esc_src, esc_dest)

    for slug in PAGES:
        html = get(src + '/' + slug).decode('utf-8')
        files.update(re.findall(re.escape(src) + r'(/wp-content/[^"\'\s)<>?#]+)', html))
        html = rewrite(html)
        html = html.replace('<head>', '<head>\n<meta name="robots" content="noindex, nofollow">', 1)
        html = html.replace('</head>', BANNER_CSS + '</head>', 1)
        html = html.replace('</body>', BANNER + FORM_JS + '</body>', 1)
        path = os.path.join(out, slug, 'index.html')
        os.makedirs(os.path.dirname(path), exist_ok=True)
        open(path, 'w', encoding='utf-8').write(html)

    # Files named inside the stylesheet and script (fonts, images, icons).
    queue = sorted(files)
    seen, missing = set(), []
    while queue:
        p = queue.pop()
        if p in seen:
            continue
        seen.add(p)
        try:
            data = get(src + urllib.parse.quote(p))
        except Exception as e:  # a file named in code but absent: report it, keep going
            missing.append(p + ' (' + str(e) + ')')
            continue
        dst = os.path.join(out, p.lstrip('/'))
        os.makedirs(os.path.dirname(dst), exist_ok=True)
        if p.endswith(('.css', '.js')):
            text = data.decode('utf-8')
            base = os.path.dirname(p)
            for ref in re.findall(r'url\(\s*["\']?(?!#|%23)([^)"\'#?]+)', text):
                if ref.startswith(('data:', 'http', '//')):
                    continue
                queue.append(os.path.normpath(os.path.join(base, ref)).replace(os.sep, '/'))
            for ref in re.findall(r'["\'](assets/[A-Za-z0-9_./-]+\.(?:webp|jpg|png|svg|woff2|pdf))["\']', text):
                queue.append('/wp-content/themes/aaykay/' + ref)
            text = rewrite(text)
            data = text.encode('utf-8')
        open(dst, 'wb').write(data)

    # The icon sprite is referenced as icons.svg#name from every page; it is among the files above.
    open(os.path.join(out, '.nojekyll'), 'w').close()
    open(os.path.join(out, '404.html'), 'w', encoding='utf-8').write(
        '<!doctype html><meta charset="utf-8"><meta name="robots" content="noindex">'
        '<title>Page not found</title><p style="font:16px system-ui;margin:3rem">'
        'Page not found. <a href="' + dest + '/">Back to the AAYKAY preview</a></p>')
    for m in missing:
        print('MISSING:', m)
    print('wrote %d pages and %d files to %s' % (len(PAGES), len(seen), out))


if __name__ == '__main__':
    if len(sys.argv) != 4:
        sys.exit(__doc__)
    main(*sys.argv[1:])
