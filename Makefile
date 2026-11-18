# CGA Arsenal — local-only for now (no prod host yet).
# `make verify` mirrors osa-arsenal-theme's workflow: rsync the CURRENT
# WORKING TREE (uncommitted changes included) into the cga-local Docker copy.

THEME_DEST ?= $(HOME)/Desktop/OSA/cga-local/public_html/wp-content/themes/cga-arsenal

PROD_HOST := cga-prod
PROD_DIR  := ~/cga/deploy/wp/public_html/wp-content/themes/cga-arsenal
PROD_DEST := $(PROD_HOST):$(PROD_DIR)

BACKUP_DIR  := ~/deploy-backups/cga-arsenal
BACKUP_KEEP := 10
STAMP := $(shell date +%Y%m%d-%H%M%S)

.PHONY: verify
verify:
	@mkdir -p $(THEME_DEST)
	@rsync -a --delete \
		--exclude '.git' \
		--exclude 'tests' \
		--exclude 'Makefile' \
		--exclude 'CLAUDE.md' \
		--exclude '._*' \
		--exclude '.DS_Store' \
		./ $(THEME_DEST)/
	@echo "Synced working tree -> $(THEME_DEST)"
	@echo "Open http://localhost:8093"

# Deploys the working tree to the CGA prod box over SSH, in three steps:
# refuse a dirty tree, tar the live theme dir on the server (keeping the newest
# BACKUP_KEEP), then rsync. --delete-excluded also removes excluded files
# (CLAUDE.md, tests, ...) that are already on the server. Prod theme dir is
# ubuntu-owned exactly so this rsync works. Main-session only, with user
# approval — prod rules apply.
.PHONY: deploy
deploy:
	@test -z "$$(git status --porcelain)" || { echo "Refusing to deploy: working tree is not clean. Commit or stash changes, or remove untracked files, first." >&2; exit 1; }
	@ssh $(PROD_HOST) 'mkdir -p $(BACKUP_DIR) && tar czf $(BACKUP_DIR)/predeploy-$(STAMP).tar.gz -C $(PROD_DIR) . && ls -1t $(BACKUP_DIR)/predeploy-*.tar.gz | tail -n +$$(($(BACKUP_KEEP) + 1)) | xargs rm -f'
	@rsync -a --delete --delete-excluded \
		--exclude '.git' \
		--exclude 'tests' \
		--exclude 'Makefile' \
		--exclude 'CLAUDE.md' \
		--exclude '._*' \
		--exclude '.DS_Store' \
		./ $(PROD_DEST)/
	@echo "Deployed $$(git rev-parse --short HEAD) -> $(PROD_DEST)"
	@echo "Rollback: ssh $(PROD_HOST) 'tar xzf $(BACKUP_DIR)/predeploy-$(STAMP).tar.gz -C $(PROD_DIR)'"
	@echo "Verify: https://caligunsandammo.com"
