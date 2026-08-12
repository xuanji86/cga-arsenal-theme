# CGA Arsenal — local-only for now (no prod host yet).
# `make verify` mirrors osa-arsenal-theme's workflow: rsync the CURRENT
# WORKING TREE (uncommitted changes included) into the cga-local Docker copy.

THEME_DEST := $(HOME)/Desktop/cga-local/public_html/wp-content/themes/cga-arsenal

PROD_DEST := cga-prod:~/cga/deploy/wp/public_html/wp-content/themes/cga-arsenal

.PHONY: verify
verify:
	@mkdir -p $(THEME_DEST)
	@rsync -a --delete \
		--exclude '.git' \
		--exclude 'tests' \
		--exclude 'Makefile' \
		--exclude '._*' \
		--exclude '.DS_Store' \
		./ $(THEME_DEST)/
	@echo "Synced working tree -> $(THEME_DEST)"
	@echo "Open http://localhost:8093"

# Deploys the CURRENT WORKING TREE (commit first!) to the CGA prod box over
# SSH. Prod theme dir is ubuntu-owned exactly so this rsync works. Main-
# session only, with user approval — prod rules apply.
.PHONY: deploy
deploy:
	@rsync -a --delete \
		--exclude '.git' \
		--exclude 'tests' \
		--exclude 'Makefile' \
		--exclude '._*' \
		--exclude '.DS_Store' \
		./ $(PROD_DEST)/
	@echo "Deployed working tree -> $(PROD_DEST)"
	@echo "Verify: https://caligunsandammo.com"
