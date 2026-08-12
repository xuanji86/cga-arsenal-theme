# CGA Arsenal — local-only for now (no prod host yet).
# `make verify` mirrors osa-arsenal-theme's workflow: rsync the CURRENT
# WORKING TREE (uncommitted changes included) into the cga-local Docker copy.

THEME_DEST := $(HOME)/Desktop/cga-local/public_html/wp-content/themes/cga-arsenal

.PHONY: verify
verify:
	@mkdir -p $(THEME_DEST)
	@rsync -a --delete \
		--exclude '.git' \
		--exclude 'tests' \
		--exclude 'Makefile' \
		./ $(THEME_DEST)/
	@echo "Synced working tree -> $(THEME_DEST)"
	@echo "Open http://localhost:8093"
