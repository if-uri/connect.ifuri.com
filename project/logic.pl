% ── Project Metadata ─────────────────────────────────────
project_metadata('connect.ifuri.com', '0.0.0', 'python').

% ── Project Files ────────────────────────────────────────
project_file('404.php', 62, 'php').
project_file('api/a2a.php', 5, 'php').
project_file('api/connector.php', 13, 'php').
project_file('api/connectors.php', 7, 'php').
project_file('api/mcp.php', 5, 'php').
project_file('api/registry.php', 7, 'php').
project_file('api/search.php', 7, 'php').
project_file('api/validate_connector.php', 70, 'php').
project_file('app.doql.less', 73, 'less').
project_file('assets/app.css', 751, 'css').
project_file('assets/app.js', 269, 'javascript').
project_file('assets/ifuri-ecobar.js', 109, 'javascript').
project_file('assets/ifuri-tokens.css', 44, 'css').
project_file('categories.php', 141, 'php').
project_file('connector.php', 236, 'php').
project_file('index.php', 212, 'php').
project_file('install.php', 10, 'php').
project_file('llms.php', 53, 'php').
project_file('project.sh', 66, 'shell').
project_file('robots.php', 10, 'php').
project_file('router.php', 75, 'php').
project_file('scripts/check_connectors.py', 118, 'python').
project_file('scripts/connector-template/pkg/__init__.py', 7, 'python').
project_file('scripts/connector-template/pkg/cli.py', 40, 'python').
project_file('scripts/connector-template/pkg/core.py', 42, 'python').
project_file('scripts/connector-template/tests/test_connector.py', 27, 'python').
project_file('scripts/deploy-plesk.sh', 16, 'shell').
project_file('scripts/new-connector.sh', 40, 'shell').
project_file('scripts/sign-manifest.php', 91, 'php').
project_file('scripts/validate_connectors.py', 60, 'python').
project_file('sitemap.php', 35, 'php').
project_file('submit.php', 226, 'php').
project_file('tests/policy_test.py', 117, 'python').
project_file('tests/sign_test.php', 72, 'php').
project_file('tests/smoke.sh', 304, 'shell').
project_file('tests/snapshot_test.php', 102, 'php').
project_file('tools/audit_connector_repos.py', 460, 'python').
project_file('tools/build_catalog.py', 172, 'python').
project_file('tree.sh', 5, 'shell').

% ── Python Functions ─────────────────────────────────────
python_function('scripts/check_connectors.py', 'reachable', 2, 2, 2).
python_function('scripts/check_connectors.py', 'main', 0, 20, 19).
python_function('scripts/connector-template/pkg/cli.py', 'emit', 1, 1, 2).
python_function('scripts/connector-template/pkg/cli.py', 'main', 1, 5, 10).
python_function('scripts/connector-template/pkg/core.py', '_json_resource', 1, 2, 6).
python_function('scripts/connector-template/pkg/core.py', 'connector_manifest', 0, 1, 1).
python_function('scripts/connector-template/pkg/core.py', 'run_command', 2, 1, 1).
python_function('scripts/connector-template/pkg/core.py', 'urirun_bindings', 0, 1, 1).
python_function('scripts/connector-template/pkg/core.py', 'run', 2, 1, 0).
python_function('scripts/connector-template/tests/test_connector.py', 'test_manifest_and_binding_share_route', 0, 5, 3).
python_function('scripts/connector-template/tests/test_connector.py', 'test_run_returns_json_result', 0, 5, 1).
python_function('scripts/validate_connectors.py', 'load', 1, 1, 3).
python_function('scripts/validate_connectors.py', 'main', 0, 9, 15).
python_function('tests/policy_test.py', 'manifest', 0, 1, 1).
python_function('tests/policy_test.py', 'expect_ok', 2, 2, 3).
python_function('tests/policy_test.py', 'expect_reject', 2, 2, 2).
python_function('tests/policy_test.py', 'main', 0, 4, 3).
python_function('tools/audit_connector_repos.py', 'run', 1, 2, 2).
python_function('tools/audit_connector_repos.py', 'load_json', 1, 2, 4).
python_function('tools/audit_connector_repos.py', 'connector_id', 1, 1, 1).
python_function('tools/audit_connector_repos.py', 'is_connector_repo', 1, 2, 1).
python_function('tools/audit_connector_repos.py', 'gh_env', 0, 3, 1).
python_function('tools/audit_connector_repos.py', 'run_gh', 1, 1, 2).
python_function('tools/audit_connector_repos.py', 'has_gh', 0, 1, 1).
python_function('tools/audit_connector_repos.py', 'discover_local_repos', 1, 5, 8).
python_function('tools/audit_connector_repos.py', 'discover_gh_repos', 0, 9, 8).
python_function('tools/audit_connector_repos.py', 'merge_repos', 2, 4, 5).
python_function('tools/audit_connector_repos.py', 'load_manifests', 0, 3, 6).
python_function('tools/audit_connector_repos.py', 'load_catalog_entries', 0, 4, 4).
python_function('tools/audit_connector_repos.py', 'read_local_file', 2, 4, 7).
python_function('tools/audit_connector_repos.py', 'read_remote_file', 2, 10, 8).
python_function('tools/audit_connector_repos.py', 'read_repo_file', 2, 2, 3).
python_function('tools/audit_connector_repos.py', 'pyproject_version', 1, 3, 4).
python_function('tools/audit_connector_repos.py', 'local_tags', 1, 5, 6).
python_function('tools/audit_connector_repos.py', 'remote_tag_present', 2, 9, 7).
python_function('tools/audit_connector_repos.py', 'tag_present', 2, 2, 3).
python_function('tools/audit_connector_repos.py', 'add_issue', 2, 1, 1).
python_function('tools/audit_connector_repos.py', 'detail_for', 4, 23, 14).
python_function('tools/audit_connector_repos.py', 'summarize', 2, 3, 0).
python_function('tools/audit_connector_repos.py', 'build_report', 1, 8, 13).
python_function('tools/audit_connector_repos.py', 'print_report', 1, 7, 3).
python_function('tools/audit_connector_repos.py', 'check_failed', 1, 4, 3).
python_function('tools/audit_connector_repos.py', 'main', 0, 5, 12).
python_function('tools/build_catalog.py', 'load_json', 1, 2, 4).
python_function('tools/build_catalog.py', 'validate_manifest', 2, 16, 7).
python_function('tools/build_catalog.py', 'public_connector', 1, 3, 0).
python_function('tools/build_catalog.py', 'build_catalog', 0, 8, 10).
python_function('tools/build_catalog.py', 'encode', 1, 1, 1).
python_function('tools/build_catalog.py', 'main', 0, 5, 10).

% ── Python Classes ───────────────────────────────────────

% ── Dependencies ─────────────────────────────────────────

% ── Makefile Targets ─────────────────────────────────────
makefile_target('help', '').
makefile_target('build-catalog', '').
makefile_target('check-catalog', '').
makefile_target('check-connectors', '').
makefile_target('new-connector', '').
makefile_target('test', '').
makefile_target('public-smoke', '').
makefile_target('serve', '').
makefile_target('deploy', '').
makefile_target('VERSION', '').
makefile_target('version', '').
makefile_target('push', '').

% ── Taskfile Tasks ───────────────────────────────────────

% ── Environment Variables ────────────────────────────────

% ── TestQL Scenarios ─────────────────────────────────────

% ── Semantic Facts from SUMD.md ──────────────────────────
sumd_declared_file('app.doql.less', 'doql').
sumd_declared_file('project/map.toon.yaml', 'analysis').
sumd_declared_file('project/logic.pl', 'analysis').
sumd_declared_file('project/calls.toon.yaml', 'analysis').
sumd_workflow('build-catalog', 'manual').
sumd_workflow_step('build-catalog', 1, 'python3 tools/build_catalog.py').
sumd_workflow('check-catalog', 'manual').
sumd_workflow_step('check-catalog', 1, 'python3 tools/build_catalog.py --check').
sumd_workflow('check-connectors', 'manual').
sumd_workflow_step('check-connectors', 1, 'python3 scripts/check_connectors.py').
sumd_workflow('new-connector', 'manual').
sumd_workflow_step('new-connector', 1, 'bash scripts/new-connector.sh $(ID) $(SCHEME)').
sumd_workflow('test', 'manual').
sumd_workflow_step('test', 1, 'bash tests/smoke.sh').
sumd_workflow('public-smoke', 'manual').
sumd_workflow_step('public-smoke', 1, 'CONNECT_HUB_BASE=https://connect.ifuri.com bash tests/smoke.sh').
sumd_workflow('serve', 'manual').
sumd_workflow_step('serve', 1, 'php -S 127.0.0.1:8099 router.php').
sumd_workflow('deploy', 'manual').
sumd_workflow_step('deploy', 1, 'bash scripts/deploy-plesk.sh').
sumd_workflow('version', 'manual').
sumd_workflow_step('version', 1, 'echo $(VERSION)').
sumd_workflow('push', 'manual').
sumd_workflow_step('push', 1, 'level="$(or $(LEVEL),patch)"').
sumd_workflow_step('push', 2, 'cur=$$(cat VERSION 2>/dev/null || echo 0.0.0)').
sumd_workflow_step('push', 3, 'new=$$(awk -F. -v l="$$level" \'{ if(l=="major") print $$1+1".0.0"').

