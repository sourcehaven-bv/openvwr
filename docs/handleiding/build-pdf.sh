#!/usr/bin/env bash
#
# Builds the Dutch OpenVWR manual and places it in the CMS public directory.
#
#   ./build-pdf.sh
#   OUTPUT=/tmp/openvwr_handleiding.pdf ./build-pdf.sh
#
# Uses a local pandoc and XeLaTeX installation when available. If pandoc is not
# installed, it falls back to the pandoc/extra Docker image.
#
# The local build requires the Eisvogel template. Install the pinned version with:
#
#   mkdir -p "${HOME}/.local/share/pandoc/templates"
#   curl -fsSL https://github.com/Wandmalfarbe/pandoc-latex-template/releases/download/v3.5.1/Eisvogel-3.5.1.tar.gz \
#       | tar xz -O Eisvogel-3.5.1/eisvogel.latex \
#       > "${HOME}/.local/share/pandoc/templates/eisvogel.latex"

set -euo pipefail

cd "$(dirname "$0")"

OUTPUT="${OUTPUT:-../../src/cms/public/pdf/openvwr_handleiding.pdf}"
BUILD_OUTPUT="openvwr_handleiding.pdf"
DATE="$(date '+%d-%m-%Y')"
PANDOC_ARGS=(
    --number-sections
    ./*.md
    --metadata "date=${DATE}"
    --from markdown
    --template eisvogel
    --listings
    --output "$BUILD_OUTPUT"
)

if command -v pandoc >/dev/null 2>&1; then
    echo "Building the manual with local pandoc..."
    pandoc "${PANDOC_ARGS[@]}" --pdf-engine=xelatex
else
    echo "pandoc not found; building the manual with Docker..."
    docker run --rm \
        --platform linux/amd64 \
        --volume "$(pwd):/data" \
        --user "$(id -u):$(id -g)" \
        pandoc/extra:latest-ubuntu \
        "${PANDOC_ARGS[@]}"
fi

mkdir -p "$(dirname "$OUTPUT")"
mv "$BUILD_OUTPUT" "$OUTPUT"
echo "Done: $OUTPUT"
