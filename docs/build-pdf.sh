#!/usr/bin/env bash
#
# Generates the data-model documentation from the form definitions and renders
# it as a PDF in the OpenVWR house style, once per locale.
#
#   ./build-pdf.sh              generate and build every locale
#   ./build-pdf.sh nl           only Dutch
#   ./build-pdf.sh --no-generate  use the markdown that is already there
#
# Requires: pandoc, xelatex (MacTeX/TeX Live) and rsvg-convert for the logo.
# Generating additionally needs a working PHP environment (src/cms).

set -euo pipefail

cd "$(dirname "$0")"

LOCALES=(nl en)
GENERATE=true
REQUESTED=()

for arg in "$@"; do
    case "$arg" in
        --no-generate) GENERATE=false ;;
        nl | en) REQUESTED+=("$arg") ;;
        *)
            echo "Unknown argument: $arg" >&2
            exit 1
            ;;
    esac
done

if [[ ${#REQUESTED[@]} -gt 0 ]]; then
    LOCALES=("${REQUESTED[@]}")
fi

LOGO_SVG="assets/openvwr_logo.svg"
LOGO_PDF="assets/openvwr_logo.pdf"

# LaTeX cannot place an SVG; convert when the PDF is missing or out of date.
if [[ ! -f "$LOGO_PDF" || "$LOGO_SVG" -nt "$LOGO_PDF" ]]; then
    echo "Converting the logo to PDF..."
    rsvg-convert -f pdf -o "$LOGO_PDF" "$LOGO_SVG"
fi

# The default fonts are the macOS ones, where this document is usually built. A
# build server does not have them; there DOC_MAINFONT and DOC_MONOFONT point at
# a font that does exist (see build-release.yml).
MAINFONT="${DOC_MAINFONT:-Helvetica Neue}"
MONOFONT="${DOC_MONOFONT:-Menlo}"

# The date is assembled by hand. Relying on `date` with a locale is not an
# option: those locales are absent on a bare build server, and it then falls
# back to English without saying so.
MONTHS_NL=(januari februari maart april mei juni juli
           augustus september oktober november december)
MONTHS_EN=(January February March April May June July
           August September October November December)
MONTH_INDEX=$(( 10#$(date '+%m') - 1 ))

for LOCALE in "${LOCALES[@]}"; do
    SRC="datamodel-${LOCALE}.md"
    OUT="datamodel-${LOCALE}.pdf"

    # The field tables come from the Filament forms so the document cannot fall
    # behind the application. The handwritten chapters live in prose/<locale>/
    # and are left alone.
    if [[ "$GENERATE" == true ]]; then
        echo "Generating markdown (${LOCALE}) from the form definitions..."
        (cd ../src/cms && php artisan docs:datamodel --locale="$LOCALE")
    fi

    if [[ ! -f "$SRC" ]]; then
        echo "Skipping ${LOCALE}: ${SRC} does not exist." >&2
        continue
    fi

    case "$LOCALE" in
        nl)
            TITLE="Wat legt OpenVWR vast?"
            SUBTITLE="Overzicht van de vast te leggen gegevens per register"
            DATE="$(date '+%-d') ${MONTHS_NL[$MONTH_INDEX]} $(date '+%Y')"
            ;;
        en)
            TITLE="What does OpenVWR record?"
            SUBTITLE="An overview of the data each register can hold"
            DATE="${MONTHS_EN[$MONTH_INDEX]} $(date '+%-d, %Y')"
            ;;
    esac

    echo "Building PDF: $OUT"
    pandoc "$SRC" \
        --output="$OUT" \
        --template=./openvwr.latex \
        --pdf-engine=xelatex \
        --toc \
        --toc-depth=2 \
        --variable=logo:"$LOGO_PDF" \
        --variable=mainfont:"$MAINFONT" \
        --variable=monofont:"$MONOFONT" \
        --variable=title:"$TITLE" \
        --variable=subtitle:"$SUBTITLE" \
        --variable=date:"$DATE"

    echo "Done: $OUT"
done
