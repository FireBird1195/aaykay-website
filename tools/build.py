"""Regenerate the repeated, data-driven parts of index.html from data/content.json.

    python3 tools/build.py          rewrite index.html in place (only the marked regions)
    python3 tools/build.py --check  exit 1 if index.html is out of date or the data is invalid

Why this exists: the navigation (three lists), the sector rows, the filter chips, the
project record and the featured-card specs used to be typed by hand in several places,
so counts drifted and a typo in a sector key silently hid a row. The client strip and the
sector icons come from the same file. Now they are generated
from one file and validated.

How it works: index.html stays the hand-written page. Each generated region is the
content of one container element (see REGIONS below: the nav lists, the sector list, the
filter chips and the record's <tbody>); only what is inside those elements is replaced. Featured cards are matched by data-project="<slug>" on their
<article>; their <h3> and <ul class="specs"> are filled from the project with that slug.
Table rows deliberately carry no slug: it would add page weight for no runtime use.

Standard library only (Python 3.8+). No other build step exists: the repository root is
the deployable site.
"""
import html
import json
import os
import re
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA = os.path.join(ROOT, "data", "content.json")
PAGE = os.path.join(ROOT, "index.html")
SPRITE = os.path.join(ROOT, "assets", "icons", "icons.svg")
SPRITE_URL = "assets/icons/icons.svg"

SLUG_RE = re.compile(r"^[a-z0-9]+(?:-[a-z0-9]+)*$")
NAV_PLACES = ("header", "mobile", "footer")
BROCHURE_PREFIX = "brochure:"  # rows known only from the legacy AayKay Electrical Enterprises brochure
PROJECT_FIELDS = ("slug", "name", "sector", "location", "area_sqft", "floors", "order_value",
                  "design_team", "status", "note", "source")

ARROW_DOWN = ('<svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M8 1.5v12M3.5 9 8 13.5 12.5 9" '
              'fill="none" stroke="currentColor" stroke-width="1.5"/></svg>')
# Empty cells: the text "Not stated" is visually hidden and CSS draws a dash in its place
# (.record .na), so screen readers read real words. (Previously aria-label on a plain
# <span>, which ARIA does not allow.)
NOT_STATED = '<span class="na">Not stated</span>'
# Units shown only in the phone layout of the record, where there are no column headers
# on screen ("200,000 sq ft · 4 floors · ₹3.5 Cr order"); hidden at table widths.
UNIT = '<span class="u">%s</span>'


def icon(name, extra=""):
    """An icon from the sprite. Icons always sit next to words that say the same thing,
    so they are hidden from assistive technology."""
    cls = "ic" + (" " + extra if extra else "")
    return '<svg class="%s" aria-hidden="true"><use href="%s#%s"/></svg>' % (cls, SPRITE_URL, name)


def sprite_ids():
    with open(SPRITE, encoding="utf-8") as f:
        return set(re.findall(r'<symbol id="([^"]+)"', f.read()))


def e(text):
    return html.escape(text, quote=True)


# ----------------------------------------------------------------- validation
def validate(data, page):
    errors = []
    sector_keys = [s["key"] for s in data["sectors"]]
    if len(set(sector_keys)) != len(sector_keys):
        errors.append("duplicate sector key")
    slugs = set()
    for i, p in enumerate(data["projects"]):
        where = "project #%d (%s)" % (i + 1, p.get("name", "?"))
        missing = [f for f in PROJECT_FIELDS if f not in p]
        unknown = [f for f in p if f not in PROJECT_FIELDS]
        if missing:
            errors.append("%s: missing field(s) %s" % (where, ", ".join(missing)))
        if unknown:
            errors.append("%s: unknown field(s) %s" % (where, ", ".join(unknown)))
        if not p.get("name"):
            errors.append("%s: name is empty" % where)
        slug = p.get("slug") or ""
        if not SLUG_RE.match(slug):
            errors.append("%s: slug %r must be lowercase words joined by hyphens" % (where, slug))
        if slug in slugs:
            errors.append("%s: slug %r is used twice" % (where, slug))
        slugs.add(slug)
        if p.get("sector") not in sector_keys:
            errors.append("%s: sector %r is not one of %s" % (where, p.get("sector"), ", ".join(sector_keys)))
        area = p.get("area_sqft")
        if area is not None and (not isinstance(area, int) or isinstance(area, bool) or area <= 0):
            errors.append("%s: area_sqft must be a whole number or null" % where)
        if not p.get("source"):
            errors.append("%s: source is required (where does this come from?)" % where)
    for item in data["nav"]:
        if not set(item["in"]) <= set(NAV_PLACES):
            errors.append("nav %r: 'in' must only use %s" % (item["label"], ", ".join(NAV_PLACES)))
        target = item["href"].lstrip("#")
        if not re.search(r'\bid="%s"' % re.escape(target), page):
            errors.append("nav %r points to #%s, which does not exist in index.html" % (item["label"], target))
    icons = sprite_ids()
    for s in data["sectors"]:
        if s.get("icon") not in icons:
            errors.append("sector %r: icon %r is not a symbol in %s" % (s["key"], s.get("icon"), SPRITE_URL))
    for ref in sorted(set(re.findall(re.escape(SPRITE_URL) + r'#([\w-]+)', page))):
        if ref not in icons:
            errors.append("index.html uses icon %r, which is not a symbol in %s" % (ref, SPRITE_URL))
    for c in data["clients"]:
        if not c.get("name") or not c.get("source"):
            errors.append("client %r: name and source are required" % c.get("name"))
        logo = c.get("logo")
        if logo is not None:
            if not all(isinstance(logo.get(k), int) for k in ("width", "height")) or not logo.get("src"):
                errors.append("client %r: logo needs src, width and height" % c["name"])
            elif not os.path.isfile(os.path.join(ROOT, logo["src"])):
                errors.append("client %r: logo file %s does not exist" % (c["name"], logo["src"]))
    for slug in re.findall(r'<article[^>]*\bdata-project="([^"]+)"', page):
        if slug not in slugs:
            errors.append("featured card data-project=%r has no matching project" % slug)
    return errors


def check_contact_consistency(company, page):
    """Every phone number and email address on the page must be the canonical one."""
    errors = []
    emails = set(re.findall(r"[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}", page))
    for found in emails - {company["email"]}:
        errors.append("index.html contains email %r; data/content.json says %r" % (found, company["email"]))
    phones = set(re.findall(r"\+91[\d \-]{8,14}\d", page))
    allowed = {company["phone_display"], company["phone_tel"], company["phone_display"].replace(" ", "-")}
    for found in phones - allowed:
        errors.append("index.html contains phone %r; data/content.json says %r" % (found, company["phone_display"]))
    return errors


# ----------------------------------------------------------------- renderers
def sort_key(p):
    """Deterministic display order. Never sorted by order value.

    Rows are grouped by how much the source documents state (full profile rows first, then
    rows with an area, then name-only rows) and are alphabetical inside each group, so the
    default view shows complete rows without ranking clients by contract size.
    """
    tier = 0 if p["order_value"] else 1 if p["area_sqft"] else 2
    return (tier, p["name"].casefold(), p["slug"])


def counts(data):
    return {s["key"]: sum(1 for p in data["projects"] if p["sector"] == s["key"]) for s in data["sectors"]}


def render_nav(data, place, indent):
    items = [n for n in data["nav"] if place in n["in"]]
    if place == "footer":
        lines = ['<a href="%s">%s</a>' % (e(n["href"]), e(n["label"])) for n in items]
    else:
        lines = ['<li><a href="%s">%s</a></li>' % (e(n["href"]), e(n["label"])) for n in items]
    return ("\n" + indent).join(lines)


def render_sector_rows(data, indent):
    n = counts(data)
    out = []
    for s in data["sectors"]:
        c = n[s["key"]]
        out.append(
            '<li><a class="sector-row" href="?sector=%s#record" data-filter="%s">'
            '<span class="sector-name">%s%s</span>'
            '<span class="sector-info"><span class="sector-desc">%s</span>'
            '<span class="sector-clients">%s</span></span>'
            '<span class="sector-count">%d project%s</span>'
            '<span class="sector-arrow" aria-hidden="true">%s</span></a></li>'
            % (e(s["key"]), e(s["key"]), icon(s["icon"], "sector-ic"), e(s["label"]), e(s["description"]), e(s["clients"]),
               c, "" if c == 1 else "s", ARROW_DOWN))
    return ("\n" + indent).join(out)


def render_chips(data, indent):
    n = counts(data)
    items = [("all", "All sectors", len(data["projects"]), "")]
    items += [(s["key"], s["label"], n[s["key"]], icon(s["icon"])) for s in data["sectors"]]
    return ("\n" + indent).join(
        '<button class="chip" type="button" data-filter="%s" aria-pressed="%s">%s<span class="chip-label">%s</span> '
        '<span class="chip-count">%d</span></button>'
        % (e(k), "true" if k == "all" else "false", ic, e(label), count) for k, label, count, ic in items)


def render_record_rows(data, indent):
    sectors = {s["key"]: s for s in data["sectors"]}
    rows = []
    for p in sorted(data["projects"], key=sort_key):
        s = sectors[p["sector"]]
        # Under the name: the sector (icon + words), then optional markers. Status gets
        # its own marker because it changes what the row means (finished vs in progress).
        meta = '<span class="p-meta">%s%s</span>' % (icon(s["icon"]), e(s["label"]))
        if p["status"]:
            done = p["status"].startswith("Completed")
            meta += '<span class="p-status%s">%s%s</span>' % (
                " p-status--done" if done else "", icon("circle-check" if done else "clock"), e(p["status"]))
        if p["note"]:
            meta += '<span class="p-note">%s</span>' % e(p["note"])
        if p["source"].startswith(BROCHURE_PREFIX):
            meta += '<span class="p-note">Earlier brochure</span>'
        cell = lambda v, unit="": (e(v) + (UNIT % unit if unit else "")) if v else NOT_STATED
        area = "{:,}".format(p["area_sqft"]) if p["area_sqft"] else None
        floors_unit = " floors" if (p["floors"] or "").isdigit() else ""
        if not any((area, p["floors"], p["order_value"], p["design_team"])):
            # Nothing but a name and place in the sources: one "not stated" cell across the
            # four columns instead of four dashes, so the gaps don't dominate the table.
            detail = '<td colspan="4" class="na-all">%s</td>' % NOT_STATED
        else:
            # A developer (residential rows) is named with its own "Developer:" prefix; the
            # class stops the phone layout adding its "Design team" label in front of it.
            team_cls = ' class="dev"' if (p["design_team"] or "").startswith("Developer:") else ""
            detail = '<td class="num">%s</td><td>%s</td><td class="num">%s</td><td%s>%s</td>' % (
                cell(area, " sq ft"), cell(p["floors"], floors_unit), cell(p["order_value"], " order"),
                team_cls, cell(p["design_team"]))
        rows.append(
            '<tr data-sector="%s"><th scope="row"><span class="p-name">%s</span>%s</th><td>%s</td>%s</tr>'
            % (e(p["sector"]), e(p["name"]), meta, cell(p["location"]), detail))
    return ("\n" + indent).join(rows)


def render_clients(data, indent):
    out = []
    for c in data["clients"]:
        logo = c.get("logo")
        if logo:
            out.append('<li class="client"><img src="%s" width="%d" height="%d" alt="%s" loading="lazy" decoding="async"></li>'
                       % (e(logo["src"]), logo["width"], logo["height"], e(c["name"])))
        else:
            out.append('<li class="client">%s</li>' % e(c["name"]))
    return ("\n" + indent).join(out)


def card_specs(p):
    specs = []
    if p["area_sqft"]:
        specs.append("{:,} sq ft".format(p["area_sqft"]))
    if p["floors"]:
        specs.append(p["floors"] + " floors" if p["floors"].isdigit() else p["floors"])
    if p["order_value"]:
        specs.append(p["order_value"] + " order")
    elif p["status"]:
        specs.append(p["status"])
    return "".join("<li>%s</li>" % e(s) for s in specs)


# ----------------------------------------------------------------- assembly
# Each generated region is the content of one container element in index.html, found by
# its opening tag. Everything inside that element is replaced; everything else is untouched.
REGIONS = {
    "nav-header": ('<ul class="nav-list">', "</ul>"),
    "nav-mobile": ('<ul class="m-links">', "</ul>"),
    "nav-footer": ('<nav class="footer-nav" id="footer-nav" aria-label="Footer">', "</nav>"),
    "sector-rows": ('<ul class="sector-list">', "</ul>"),
    "clients": ('<ul class="client-list">', "</ul>"),
    "chips": ('<div class="filters" role="group" aria-label="Filter projects by sector" hidden>', "</div>"),
    "record-rows": ('<tbody id="record-rows">', "</tbody>"),
}


def replace_region(page, name, render):
    open_tag, close_tag = REGIONS[name]
    if page.count(open_tag) != 1:
        raise SystemExit("index.html: expected exactly one %s for region %r" % (open_tag, name))
    a = page.index(open_tag) + len(open_tag)
    b = page.index(close_tag, a)
    line_start = page.rfind("\n", 0, page.index(open_tag)) + 1
    outer = page[line_start:page.index(open_tag)]          # indentation of the container
    indent = outer + "  "
    return page[:a] + "\n" + indent + render(indent) + "\n" + outer + page[b:]


def fill_cards(page, data):
    by_slug = {p["slug"]: p for p in data["projects"]}

    def one(m):
        p = by_slug[m.group(2)]
        body = re.sub(r"<h3>.*?</h3>", lambda _: "<h3>%s</h3>" % e(p["name"]), m.group(3), count=1, flags=re.S)
        body = re.sub(r'<ul class="specs">.*?</ul>', lambda _: '<ul class="specs">%s</ul>' % card_specs(p), body, count=1, flags=re.S)
        return m.group(1) + body + m.group(4)

    return re.sub(r'(<article[^>]*\bdata-project="([^"]+)"[^>]*>)(.*?)(</article>)', one, page, flags=re.S)


def build(data, page):
    page = replace_region(page, "nav-header", lambda i: render_nav(data, "header", i))
    page = replace_region(page, "nav-mobile", lambda i: render_nav(data, "mobile", i))
    page = replace_region(page, "nav-footer", lambda i: render_nav(data, "footer", i))
    page = replace_region(page, "sector-rows", lambda i: render_sector_rows(data, i))
    page = replace_region(page, "clients", lambda i: render_clients(data, i))
    page = replace_region(page, "chips", lambda i: render_chips(data, i))
    page = replace_region(page, "record-rows", lambda i: render_record_rows(data, i))
    return fill_cards(page, data)


def main(argv):
    check = "--check" in argv
    with open(DATA, encoding="utf-8") as f:
        data = json.load(f)
    with open(PAGE, encoding="utf-8") as f:
        page = f.read()
    errors = validate(data, page)
    if errors:
        print("data/content.json is invalid:\n  - " + "\n  - ".join(errors), file=sys.stderr)
        return 1
    built = build(data, page)
    errors = check_contact_consistency(data["company"], built)
    if errors:
        print("Contact details disagree:\n  - " + "\n  - ".join(errors), file=sys.stderr)
        return 1
    n = counts(data)
    summary = "%d projects (%s)" % (len(data["projects"]), ", ".join("%s %d" % (k, v) for k, v in n.items()))
    if check:
        if built != page:
            print("index.html is out of date. Run: python3 tools/build.py", file=sys.stderr)
            return 1
        print("OK: index.html matches data/content.json; " + summary)
        return 0
    if built != page:
        with open(PAGE, "w", encoding="utf-8", newline="\n") as f:
            f.write(built)
        print("Updated index.html; " + summary)
    else:
        print("index.html already up to date; " + summary)
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
