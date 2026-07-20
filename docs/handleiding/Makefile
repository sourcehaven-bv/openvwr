PDF := openvwr_handleiding.pdf
DEST := ../../src/cms/public/pdf/$(PDF)
DATE := $(shell date "+%d-%m-%Y")
PANDOC_ARGS := -N *.md -M date="$(DATE)" --from markdown --template eisvogel --listings -o $(PDF)

# Native build (fast). Needs pandoc, a LaTeX distribution (MacTeX/TeX Live) and the
# eisvogel template in ~/.local/share/pandoc/templates/ — run `make install-template`.
# Falls back to Docker when pandoc is not installed. Note that the pandoc/extra image
# is amd64-only, so on Apple Silicon the Docker path runs under slow emulation.
handleiding:
	@if command -v pandoc >/dev/null 2>&1; then \
		echo "Building with local pandoc..."; \
		pandoc $(PANDOC_ARGS) --pdf-engine=xelatex; \
	else \
		echo "pandoc not found, falling back to Docker..."; \
		docker run --rm --platform linux/amd64 --volume "`pwd`:/data" --user `id -u`:`id -g` pandoc/extra $(PANDOC_ARGS); \
	fi
	mv $(PDF) $(DEST)

install-template:
	mkdir -p $(HOME)/.local/share/pandoc/templates
	curl -fsSL https://github.com/Wandmalfarbe/pandoc-latex-template/releases/download/v3.5.1/Eisvogel-3.5.1.tar.gz \
		| tar xz -O Eisvogel-3.5.1/eisvogel.latex > $(HOME)/.local/share/pandoc/templates/eisvogel.latex

open: handleiding
	open $(DEST)

.PHONY: handleiding install-template open
