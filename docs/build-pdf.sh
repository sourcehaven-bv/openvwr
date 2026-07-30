#!/usr/bin/env bash
#
# Genereert gegevensmodel-verwerkingen.md uit de formulierdefinities en zet dat
# om naar PDF met de OpenVWR-huisstijl.
#
#   ./build-pdf.sh                 genereer de markdown opnieuw en bouw de PDF
#   ./build-pdf.sh --geen-generatie  gebruik de bestaande markdown
#   ./build-pdf.sh <bestand.md>    bouw een ander markdownbestand
#
# Vereist: pandoc, xelatex (MacTeX/TeX Live) en rsvg-convert voor het logo.
# Voor het genereren is daarnaast een werkende PHP-omgeving nodig (src/cms).

set -euo pipefail

cd "$(dirname "$0")"

GENERATE=true
SRC="gegevensmodel-verwerkingen.md"

for arg in "$@"; do
    case "$arg" in
        --geen-generatie) GENERATE=false ;;
        *.md) SRC="$arg"; GENERATE=false ;;
    esac
done

OUT="${SRC%.md}.pdf"

# De veldtabellen komen uit de Filament-formulieren, zodat het document niet
# achterloopt op de applicatie. De handgeschreven hoofdstukken staan in
# handgeschreven/ en blijven ongemoeid.
if [[ "$GENERATE" == true ]]; then
    echo "Markdown genereren uit de formulierdefinities..."
    (cd ../src/cms && php artisan docs:datamodel)
fi

LOGO_SVG="assets/openvwr_logo.svg"
LOGO_PDF="assets/openvwr_logo.pdf"

# LaTeX kan geen SVG plaatsen; converteer als de PDF ontbreekt of verouderd is.
if [[ ! -f "$LOGO_PDF" || "$LOGO_SVG" -nt "$LOGO_PDF" ]]; then
    echo "Logo omzetten naar PDF..."
    rsvg-convert -f pdf -o "$LOGO_PDF" "$LOGO_SVG"
fi

# De standaardlettertypes zijn die van macOS, waar dit document doorgaans wordt
# gemaakt. Op een buildserver bestaan die niet; daar zet DOC_MAINFONT en
# DOC_MONOFONT een lettertype dat er wel is (zie build-release.yml).
MAINFONT="${DOC_MAINFONT:-Helvetica Neue}"
MONOFONT="${DOC_MONOFONT:-Menlo}"

# De datum met de hand in het Nederlands zetten. Op `date` met een nl_NL-locale
# kunnen we niet bouwen: die locale ontbreekt op een kale buildserver, en dan
# valt hij stilzwijgend terug op Engels.
MAANDEN=(januari februari maart april mei juni juli
         augustus september oktober november december)
DATUM="$(date '+%-d') ${MAANDEN[$(( 10#$(date '+%m') - 1 ))]} $(date '+%Y')"

echo "PDF genereren: $OUT"
pandoc "$SRC" \
    --output="$OUT" \
    --template=./openvwr.latex \
    --pdf-engine=xelatex \
    --toc \
    --toc-depth=2 \
    --variable=logo:"$LOGO_PDF" \
    --variable=mainfont:"$MAINFONT" \
    --variable=monofont:"$MONOFONT" \
    --variable=title:"Wat legt OpenVWR vast?" \
    --variable=subtitle:"Overzicht van de vast te leggen gegevens per register" \
    --variable=date:"$DATUM"

echo "Klaar: $OUT"
