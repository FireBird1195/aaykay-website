#!/usr/bin/env bash
# Build a disposable WordPress with the AAYKAY theme active, for tests (local or CI).
# Never point this at a real site: it DROPS AND RECREATES the database named in DB_NAME.
#
#   WP_PATH=/var/www/aaykay WP_URL=http://localhost:8080 DB_NAME=wp DB_USER=wp DB_PASS=… \
#     tests/bin/fresh-site.sh
#
# Needs: php, wp-cli (wp), git, a MySQL/MariaDB server. WP_VERSION picks the WordPress tag
# (default 7.1.4, the version this release was tested with).
set -euo pipefail
here="$(cd "$(dirname "$0")/../.." && pwd)"
: "${WP_PATH:?set WP_PATH}" "${WP_URL:?set WP_URL}" "${DB_NAME:?set DB_NAME}" "${DB_USER:?set DB_USER}" "${DB_PASS:?set DB_PASS}"
DB_HOST="${DB_HOST:-localhost}"
WP_VERSION="${WP_VERSION:-7.1.4}"
WP="wp --path=$WP_PATH --allow-root"

if [ ! -f "$WP_PATH/wp-load.php" ]; then
  git clone -q --depth 1 --branch "$WP_VERSION" https://github.com/WordPress/WordPress.git "$WP_PATH"
  rm -rf "$WP_PATH/.git"
fi
if [ ! -f "$WP_PATH/wp-config.php" ]; then
  $WP config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --dbhost="$DB_HOST" --skip-check --quiet
  $WP config set WP_DEBUG true --raw --quiet
  $WP config set WP_DEBUG_LOG true --raw --quiet
  $WP config set WP_DEBUG_DISPLAY false --raw --quiet
  $WP config set WP_ENVIRONMENT_TYPE local --quiet
  $WP config set DISABLE_WP_CRON true --raw --quiet
fi
$WP db reset --yes --quiet
$WP core install --url="$WP_URL" --title="AAYKAY Electricals" --admin_user=admin \
  --admin_password="${WP_ADMIN_PASS:-LocalOnly-Admin-2026}" --admin_email=dev@example.test --skip-email --quiet
$WP rewrite structure '/%postname%/' --quiet
cat > "$WP_PATH/.htaccess" <<'HT'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
HT
ln -sfn "$here/theme" "$WP_PATH/wp-content/themes/aaykay"
$WP theme activate aaykay --quiet
rm -f "$WP_PATH/wp-content/debug.log"
echo "Fresh site ready at $WP_URL ($($WP core version), theme $($WP theme get aaykay --field=version))"
