#!/bin/bash
set -xeuo pipefail

VERSION="${1:-dev}"

# Same reasoning as the SHA check below: the version ends up in a single-quoted
# PHP string, a tar filename and a JSON document, so restrict it to the shapes
# actually used (dev, dev-<sha>, v20260721).
if ! [[ "$VERSION" =~ ^[0-9A-Za-z._-]+$ ]]; then
    echo "error: '$VERSION' is not a valid release version (expected letters, digits, '.', '_' or '-')." >&2
    exit 1
fi

RELEASE_NAME="openvwr-cms-$VERSION"

# The commit being packaged. Released archives have been shipping
# 'sha' => 'unknown', because the lookup below runs in the build container and
# silently falls back when git cannot read the checkout. Rather than rely on
# the packaging step rediscovering the commit, prefer a value passed in by the
# caller; that also works when packaging an exported tree with no .git at all.
#   ./scripts/create-release.sh v20260820 <sha>
# RELEASE_REQUIRE_SHA=1 turns a missing SHA into a hard failure so an official
# build cannot silently publish "unknown".
GIT_COMMIT="${2:-${RELEASE_SHA:-}}"
if [ -z "$GIT_COMMIT" ]; then
    GIT_COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
fi

if [ "$GIT_COMMIT" = "unknown" ] && [ "${RELEASE_REQUIRE_SHA:-0}" = "1" ]; then
    echo "error: could not determine the commit SHA to stamp into this release." >&2
    echo "Pass it explicitly: ./scripts/create-release.sh <version> <sha>" >&2
    exit 1
fi

# The SHA is interpolated into a single-quoted PHP string in config/version.php
# below, so anything but hex would let a caller close that quote and have the
# rest evaluated as code. Every caller today passes `git rev-parse` output;
# checking the shape here keeps that true for callers added later.
if [ "$GIT_COMMIT" != "unknown" ] && ! [[ "$GIT_COMMIT" =~ ^[0-9a-fA-F]{7,40}$ ]]; then
    echo "error: '$GIT_COMMIT' is not a valid commit SHA (expected 7-40 hex characters)." >&2
    exit 1
fi

# Create release info file
BUILD_DATE=$(date -u)
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
if [ "$GIT_COMMIT" = "unknown" ]; then
    GIT_COMMIT_SHORT="unknown"
else
    # Shorten locally; the packaged tree may not have git metadata to ask.
    GIT_COMMIT_SHORT="${GIT_COMMIT:0:7}"
fi
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
