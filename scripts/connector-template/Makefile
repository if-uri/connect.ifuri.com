.PHONY: help manifest bindings smoke test
help: ## List targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN{FS=":.*?## "}{printf "  %-10s %s\n",$$1,$$2}'
manifest: ## Print the connector manifest
	__CLI__ manifest
bindings: ## Print urirun bindings
	__CLI__ bindings
smoke: ## bindings -> urirun validate/compile/run -> MCP tools
	__CLI__ bindings > /tmp/__ID__.bindings.json
	urirun validate /tmp/__ID__.bindings.json
	urirun compile /tmp/__ID__.bindings.json --out /tmp/__ID__.registry.json
	urirun run '__SCHEME__://host/__SCHEME__/query/run' /tmp/__ID__.registry.json \
	  --payload '{"target":"example","timeout":5}' --execute --allow '__SCHEME__://host/*'
	python3 -m urirun.v2_mcp tools /tmp/__ID__.registry.json
test: ## Install editable + smoke
	pip install -e . && $(MAKE) smoke
