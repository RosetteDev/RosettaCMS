#!/bin/bash
#
# Deploy the release tarball to the live server.
#
# Usage: bash scripts/deploy.sh
#
# Requirements: clean git tree (server always reflects committed code),
# public/build freshly built (done automatically), SSH alias `rosettamaps`
# (see .env / deployment docs), and the server's ea-php84 binary.
#
# Never overwrites: .env, .htaccess, storage/, uploads, vendor (refreshed
# via composer install instead), or the database.

set -euo pipefail

SSH_ALIAS="rosettamaps"
APP_ROOT="/home2/naftzdte/public_html/website_bfb9d71f"
PHP="/opt/cpanel/ea-php84/root/usr/bin/php"
TARBALL="/tmp/rosetta-cms-release.tar.gz"

cd "$(dirname "$0")/.."

# Only tracked changes block the deploy — untracked files (e.g. the
# intentionally-untracked package-lock.json) never make it into the tarball.
if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
    echo "ERROR: tracked changes are not committed — commit or stash them before deploying."
    echo "The server only receives committed code."
    git status --short --untracked-files=no
    exit 1
fi

echo "==> Building frontend assets"
npm run build

echo "==> Building release tarball"
python3 scripts/build_release.py "$TARBALL"

echo "==> Uploading to $SSH_ALIAS:$APP_ROOT"
scp "$TARBALL" "$SSH_ALIAS:$APP_ROOT/rosetta-cms-release.tar.gz"

echo "==> Extracting and refreshing vendor/caches on the server"
ssh "$SSH_ALIAS" "cd $APP_ROOT && \
    tar -xzf rosetta-cms-release.tar.gz && \
    rm rosetta-cms-release.tar.gz && \
    /opt/cpanel/ea-php84/root/usr/bin/php -r 'echo \"php: \" . PHP_VERSION . PHP_EOL;' && \
    /home2/naftzdte/bin/composer install --no-dev --optimize-autoloader --quiet && \
    $PHP artisan migrate --force && \
    $PHP artisan config:clear && \
    $PHP artisan route:clear && \
    $PHP artisan view:clear && \
    $PHP artisan cache:clear && \
    echo 'Deploy steps completed'"

echo "==> Verifying the site responds"
curl -sS -o /dev/null -w "Homepage HTTP status: %{http_code}\n" -L "https://cms.rosettedevelopment.com/" || true

echo "==> Done. Live site: https://cms.rosettedevelopment.com"
