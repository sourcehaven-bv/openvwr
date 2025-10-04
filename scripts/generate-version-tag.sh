#!/usr/bin/env bash
set -euo pipefail

# Generate a date-based version tag
# Usage: generate-version-tag.sh [--alpha]
#
# Version format:
# - First release of day: vYYYYMMDD
# - Subsequent releases: vYYYYMMDD.1, vYYYYMMDD.2, etc.
# - Alpha releases: append -alpha suffix

ALPHA=false

# Parse arguments
while [[ $# -gt 0 ]]; do
  case $1 in
    --alpha)
      ALPHA=true
      shift
      ;;
    *)
      echo "Unknown option: $1" >&2
      echo "Usage: $0 [--alpha]" >&2
      exit 1
      ;;
  esac
done

# Get current UTC date
DATE=$(date -u +%Y%m%d)

# Fetch all tags (skip if remote fetch fails, use local tags)
git fetch --tags 2>/dev/null || true

# Find existing tags for today
EXISTING_TAGS=$(git tag -l "v${DATE}*" | grep -E "^v${DATE}(\.[0-9]+)?(-alpha)?$" || true)

if [ -z "$EXISTING_TAGS" ]; then
  # No tags for today, use base version
  VERSION="v${DATE}"
else
  # Find highest patch number
  HIGHEST_PATCH=$(echo "$EXISTING_TAGS" | sed -E "s/^v${DATE}\.?([0-9]+)?(-alpha)?$/\1/" | grep -E '^[0-9]+$' | sort -n | tail -1 || echo "0")

  if [ -z "$HIGHEST_PATCH" ] || [ "$HIGHEST_PATCH" = "0" ]; then
    # Only base version exists, next is .1
    PATCH=1
  else
    # Increment highest patch
    PATCH=$((HIGHEST_PATCH + 1))
  fi

  VERSION="v${DATE}.${PATCH}"
fi

# Add alpha suffix if requested
if [ "$ALPHA" = true ]; then
  VERSION="${VERSION}-alpha"
fi

echo "$VERSION"
