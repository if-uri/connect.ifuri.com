.PHONY: help
help:
	@grep -E "^[a-zA-Z_-]+:.*?## .*$$" $(MAKEFILE_LIST) | awk "BEGIN{FS=\":.*?## \"}{printf \"  %-12s %s\\n\",\$$1,\$$2}"

.PHONY: build-catalog
build-catalog: ## Rebuild data/connectors.json from connector manifests
	python3 tools/build_catalog.py

.PHONY: check-catalog
check-catalog: ## Verify generated catalog is up to date
	python3 tools/build_catalog.py --check

.PHONY: test
test: ## Run local smoke tests
	bash tests/smoke.sh

.PHONY: public-smoke
public-smoke: ## Test the deployed connect.ifuri.com site
	CONNECT_HUB_BASE=https://connect.ifuri.com bash tests/smoke.sh

.PHONY: serve
serve: ## Start local PHP server on http://127.0.0.1:8099
	php -S 127.0.0.1:8099 router.php

.PHONY: deploy
deploy: ## Publish to connect.ifuri.com (Plesk)
	bash scripts/deploy-plesk.sh
