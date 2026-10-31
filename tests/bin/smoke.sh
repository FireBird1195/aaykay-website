#!/usr/bin/env bash
# Read-only smoke test for any copy of the site, including the live one: it only GETs pages
# (no form is sent, nothing is changed). Run after every deploy:
#
#   tests/bin/smoke.sh https://akepl.in            # live
#   tests/bin/smoke.sh http://localhost:8080      # local
#
# LAUNCHED=1 also checks that search engines are allowed (use it after launch day).
set -uo pipefail
BASE="${1:?usage: smoke.sh BASE_URL}"; BASE="${BASE%/}"
fail=0
ok()   { printf '  ok    %s\n' "$1"; }
bad()  { printf '  FAIL  %s\n' "$1"; fail=1; }
code() { curl -s -o /dev/null -w '%{http_code}' --max-time 20 "$@"; }

echo "Smoke test: $BASE"
for p in / /capabilities/ /projects/ /quality-safety/ /careers/ /contact-us/; do
  c=$(code "$BASE$p"); [ "$c" = 200 ] && ok "$p 200" || bad "$p answered $c"
done
c=$(code "$BASE/no-such-page-$(date +%s)/"); [ "$c" = 404 ] && ok "unknown page 404" || bad "unknown page answered $c"

h=$(curl -s --max-time 20 "$BASE/wp-json/aaykay/v1/health"); [ "$h" = '{"status":"ok"}' ] && ok "health ok" || bad "health said: $h"

hdr=$(curl -sI --max-time 20 "$BASE/")
for want in 'x-content-type-options: nosniff' 'x-frame-options: SAMEORIGIN' 'referrer-policy:' 'content-security-policy:'; do
  echo "$hdr" | grep -qi "^$want" && ok "header $want" || bad "missing header $want"
done
echo "$hdr" | grep -qi '^x-powered-by: php' && bad "PHP version is disclosed (X-Powered-By)" || ok "no X-Powered-By"

for f in seed/content.json README.md; do
  c=$(code "$BASE/wp-content/themes/aaykay/$f"); [ "$c" = 403 ] || [ "$c" = 404 ] && ok "theme $f not downloadable ($c)" || bad "theme $f answered $c"
done
c=$(code "$BASE/wp-json/wp/v2/users"); [ "$c" != 200 ] && ok "user list hidden ($c)" || bad "user list is public"
c=$(code "$BASE/wp-content/debug.log"); [ "$c" != 200 ] && ok "debug.log not public ($c)" || bad "wp-content/debug.log is downloadable"
c=$(code "$BASE/wp-content/uploads/"); [ "$c" != 200 ] && ok "uploads folder not listed ($c)" || bad "uploads folder lists its files (Options -Indexes)"
[ "$(code "$BASE/robots.txt")" = 200 ] && ok "robots.txt" || bad "robots.txt"
[ "$(code "$BASE/wp-sitemap.xml")" = 200 ] && ok "sitemap" || bad "wp-sitemap.xml"

page=$(curl -s --max-time 20 "$BASE/")
echo "$page" | grep -q 'localhost' && bad "home page mentions localhost" || ok "no localhost links"
echo "$page" | grep -q 'class="pending' && bad "\"To come\" placeholders are visible" || ok "no placeholders"
if [ "${LAUNCHED:-0}" = 1 ]; then
  echo "$page" | grep -qi 'name=.robots.*noindex' && bad "home page still says noindex" || ok "indexable"
fi

case "$BASE" in https://*)
  host="${BASE#https://}"
  c=$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' --max-time 20 "http://$host/")
  case "$c" in 301*https://*) ok "http redirects to https";; *) bad "http://$host/ answered $c";; esac
  case "$host" in www.*) other="${host#www.}";; *) other="www.$host";; esac
  c=$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' --max-time 20 "https://$other/")
  case "$c" in 301*"$BASE"*) ok "$other redirects to $host";; *) bad "https://$other/ answered $c (want 301 to $BASE/)";; esac
  ;;
esac

[ $fail = 0 ] && echo "All smoke checks passed." || echo "Some smoke checks FAILED."
exit $fail
