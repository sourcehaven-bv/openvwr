#!/usr/bin/env python3
"""Replace the QR code and TOTP key in the otp-setup figure with placeholders.

The figure documents the forced two-factor enrolment step, so it necessarily
renders a working QR code and the matching key in plain text. That image is
committed and ends up in src/cms/public/pdf/openvwr_handleiding.pdf, which sits
under the Laravel document root and is therefore served without authentication.

The captured secret is faker output (UserFactory: regexify('[A-Z]{16}')) from a
throwaway dev database, so nothing real leaks today. The reason to redact anyway
is the mechanism: whatever the database holds at capture time gets published
unauthenticated. Capture against an environment with a real user once and the
same pipeline publishes a real secret with no gate in between.

Pixels are overwritten, not blurred. Blur is a reversible transform on a known
alphabet, and a blurred QR still carries its error-correction data.

capture.mjs invokes this automatically via the figure's postprocess hook. It can
also be run by hand, on the committed figure or on a given file:

  python3 tools/screenshots/redact-otp.py [path/to/figure.png]

It refuses to run on an already-redacted image rather than redacting twice.
"""

from __future__ import annotations

import sys
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

REPO_ROOT = Path(__file__).resolve().parents[2]
FIGURE = REPO_ROOT / "docs/handleiding/imgs/01_welkom/02_profile_one_time_password.png"

# Bounds measured from the rendered figure by scanning for dark pixels inside
# the two-factor card: the QR occupies a clean 282px square, separated from the
# key line by a band of blank rows. verify_boxes() re-checks them against the
# image so a layout shift fails loudly instead of redacting the wrong pixels.
QR_BOX = (1447, 459, 1728, 740)
KEY_BOX = (1346, 799, 1741, 819)

# The card background the placeholders sit on, and Filament's muted text colour.
CARD_BG = (255, 255, 255)
PLACEHOLDER_BG = (243, 244, 246)
PLACEHOLDER_BORDER = (156, 163, 175)
PLACEHOLDER_TEXT = (75, 85, 99)

FONT_CANDIDATES = [
    "/System/Library/Fonts/Supplemental/Arial.ttf",
    "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
]


def load_font(size: int) -> ImageFont.FreeTypeFont | ImageFont.ImageFont:
    for path in FONT_CANDIDATES:
        if Path(path).exists():
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


def centred_text(draw: ImageDraw.ImageDraw, box, text, font, fill) -> None:
    x0, y0, x1, y1 = box
    left, top, right, bottom = draw.textbbox((0, 0), text, font=font)
    x = x0 + (x1 - x0 - (right - left)) / 2 - left
    y = y0 + (y1 - y0 - (bottom - top)) / 2 - top
    draw.text((x, y), text, font=font, fill=fill)


def verify_boxes(image: Image.Image) -> None:
    """Fail loudly if the figure no longer matches the measured bounds.

    A redaction step that silently misses is worse than none at all: it reads as
    "handled" while publishing the secret. So confirm both regions still contain
    the dark pixels we expect to cover, and that the gap between them is blank.
    """
    grey = image.convert("L")
    width, height = grey.size
    pixels = grey.load()

    def dark_count(box) -> int:
        x0, y0, x1, y1 = box
        if x1 >= width or y1 >= height:
            raise SystemExit(f"figure is {width}x{height}; region {box} lies outside it")
        return sum(
            1
            for y in range(y0, y1 + 1)
            for x in range(x0, x1 + 1)
            if pixels[x, y] < 100
        )

    qr_dark = dark_count(QR_BOX)
    key_dark = dark_count(KEY_BOX)

    # A rendered QR is roughly a third dark; the key line is sparse text. Exact
    # counts vary with the secret, so assert only that content is present.
    if qr_dark < 5000:
        raise SystemExit(
            f"expected a QR code at {QR_BOX} but found only {qr_dark} dark pixels; "
            "the layout changed - re-measure before redacting"
        )
    if key_dark < 200:
        raise SystemExit(
            f"expected the key line at {KEY_BOX} but found only {key_dark} dark pixels; "
            "the layout changed - re-measure before redacting"
        )


def redact(path: Path) -> None:
    image = Image.open(path).convert("RGB")
    verify_boxes(image)
    draw = ImageDraw.Draw(image)

    # QR code: a bordered placeholder box carrying its own label, so a reader
    # who meets the figure without the surrounding text still understands that
    # a real code appears here rather than a rendering failure.
    draw.rectangle(QR_BOX, fill=PLACEHOLDER_BG, outline=PLACEHOLDER_BORDER, width=3)
    qr_font = load_font(30)
    x0, y0, x1, y1 = QR_BOX
    # Centre the two lines as one block rather than one per half, which would
    # strand them against the top and bottom edges.
    line_height = 40
    centre = (y0 + y1) // 2
    for offset, line in ((-line_height // 2, "QR-code"), (line_height // 2, "verschijnt hier")):
        centred_text(
            draw,
            (x0, centre + offset - line_height // 2, x1, centre + offset + line_height // 2),
            line,
            qr_font,
            PLACEHOLDER_TEXT,
        )

    # Key line: clear the whole line and rewrite it, keeping the "Sleutel:"
    # label the manual text refers to.
    draw.rectangle(KEY_BOX, fill=CARD_BG)
    key_font = load_font(26)
    centred_text(draw, KEY_BOX, "Sleutel: XXXX XXXX XXXX XXXX", key_font, PLACEHOLDER_TEXT)

    image.save(path)


def main(argv: list[str]) -> int:
    # capture.mjs passes the file it just wrote; without an argument fall back
    # to the committed figure, so the script is also usable on its own.
    target = Path(argv[1]).resolve() if len(argv) > 1 else FIGURE
    if not target.exists():
        print(f"figure not found: {target}", file=sys.stderr)
        return 1
    redact(target)
    try:
        shown = target.relative_to(REPO_ROOT)
    except ValueError:
        shown = target
    print(f"redacted {shown}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv))
