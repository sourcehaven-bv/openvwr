#!/bin/bash
set -xeuo pipefail

VERSION="${1:-dev}"
RELEASE_NAME="openvwr-cms-$VERSION"
# Create release info file
BUILD_DATE=$(date -u)
GIT_COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
GIT_BRANCH=$(git branch --show-current 2>/dev/null || echo "unknown")

echo "Creating OpenVWR CMS release version $VERSION..."

pushd "$(dirname "$0")/../src/cms" >/dev/null

# Check for .env and create from .env.example if missing
if [ ! -f .env ]; then
    echo "No .env found, creating from .env.example..."
    cp .env.example .env
fi

# Prepare CMS
npm ci --ignore-scripts
composer install --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist --no-dev
npm run build
php artisan vendor:publish --tag=livewire:assets

cat >"RELEASE_INFO.txt" <<EOF
OpenVWR CMS Release $VERSION
Built on: $BUILD_DATE
Git commit: $GIT_COMMIT
Git branch: $GIT_BRANCH

Installation Instructions:
- Extract this archive to your web server
- Configure your environment via .env
- Run: php artisan key:generate
- Run: php artisan migrate
- Run: php artisan storage:link
- Set proper permissions on storage/ and bootstrap/cache/
- Configure your web server to serve public/ as document root
EOF
cp ../../.db_requirements ./

# Stamp the deployment version into the app (overwrites the committed dev defaults).
GIT_COMMIT_SHORT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
cat >"config/version.php" <<EOF
<?php

declare(strict_types=1);

return [
    'label' => '$RELEASE_NAME',
    'sha' => '$GIT_COMMIT_SHORT',
];
EOF

# Prepare static-website
cp -R ../static-website/ ./
echo "{ \"version\": \"$RELEASE_NAME\", \"git_ref\": \"$GIT_COMMIT\"}" >./static-website/static/version.json

# Create release archive
tar -czf ../../${RELEASE_NAME}.tar.gz ./app ./bootstrap/app.php ./config ./database ./public ./resources ./routes ./vendor ./artisan ./composer.json ./static-website ./.db_requirements

popd >/dev/null
