# connect.ifuri.com

connect.ifuri.com

## Contents

- [Metadata](#metadata)
- [Architecture](#architecture)
- [Workflows](#workflows)
- [Configuration](#configuration)
- [Deployment](#deployment)
- [Makefile Targets](#makefile-targets)
- [Code Analysis](#code-analysis)
- [Call Graph](#call-graph)
- [Intent](#intent)

## Metadata

- **name**: `connect.ifuri.com`
- **version**: `0.0.0`
- **ecosystem**: SUMD + DOQL + testql + taskfile
- **generated_from**: Makefile, app.doql.less, project/(3 analysis files)

## Architecture

```
SUMD (description) → DOQL/source (code) → taskfile (automation) → testql (verification)
```

### DOQL Application Declaration (`app.doql.less`)

```less markpact:doql path=app.doql.less
// LESS format — define @variables here as needed

app {
  name: connect.ifuri.com;
  version: 0.1.0;
}

workflow[name="build-catalog"] {
  trigger: manual;
  step-1: run cmd=python3 tools/build_catalog.py;
}

workflow[name="check-catalog"] {
  trigger: manual;
  step-1: run cmd=python3 tools/build_catalog.py --check;
}

workflow[name="check-connectors"] {
  trigger: manual;
  step-1: run cmd=python3 scripts/check_connectors.py;
}

workflow[name="new-connector"] {
  trigger: manual;
  step-1: run cmd=bash scripts/new-connector.sh $(ID) $(SCHEME);
}

workflow[name="test"] {
  trigger: manual;
  step-1: run cmd=bash tests/smoke.sh;
}

workflow[name="public-smoke"] {
  trigger: manual;
  step-1: run cmd=CONNECT_HUB_BASE=https://connect.ifuri.com bash tests/smoke.sh;
}

workflow[name="serve"] {
  trigger: manual;
  step-1: run cmd=php -S 127.0.0.1:8099 router.php;
}

workflow[name="deploy"] {
  trigger: manual;
  step-1: run cmd=bash scripts/deploy-plesk.sh;
}

workflow[name="version"] {
  trigger: manual;
  step-1: run cmd=echo $(VERSION);
}

workflow[name="push"] {
  trigger: manual;
  step-1: run cmd=level="$(or $(LEVEL),patch)"; \;
  step-2: run cmd=cur=$$(cat VERSION 2>/dev/null || echo 0.0.0); \;
  step-3: run cmd=new=$$(awk -F. -v l="$$level" '{ if(l=="major") print $$1+1".0.0"; else if(l=="minor") print $$1"."$$2+1".0"; else print $$1"."$$2"."$$3+1 }' VERSION); \;
  step-4: run cmd=echo "$$new" > VERSION; \;
  step-5: run cmd=git add VERSION; \;
  step-6: run cmd=git commit -m "release: v$$new"; \;
  step-7: run cmd=git tag -a "v$$new" -m "v$$new"; \;
  step-8: run cmd=git push && git push --tags; \;
  step-9: run cmd=echo "released v$$new (was v$$cur); deploy carries VERSION -> footer shows it";
}

deploy {
  target: makefile;
}

environment[name="local"] {
  runtime: python;
}
```

## Workflows

## Configuration

```yaml
project:
  name: connect.ifuri.com
  version: 0.0.0
  env: local
```

## Deployment

```bash markpact:run
pip install connect.ifuri.com

# development install
pip install -e .[dev]
```

## Makefile Targets

- `help`
- `build-catalog`
- `check-catalog`
- `check-connectors`
- `new-connector`
- `test`
- `public-smoke`
- `serve`
- `deploy`
- `VERSION`
- `version`
- `push`

## Code Analysis

### `project/map.toon.yaml`

```toon markpact:analysis path=project/map.toon.yaml
# connect.ifuri.com | 103f 12056L | shell:4,php:18,json:70,javascript:2,python:7 | 2026-07-14
# generated in 0.01s
# stats: 113 func | 0 cls | 103 mod | CC̄=4.4 | critical:5 | cycles:0
# alerts[5]: CC builder=51; fan-out builder=29; fan-out main=21; CC detail_for=23; CC main=20
# hotspots[5]: builder fan=29; main fan=21; main fan=19; detail_for fan=17; build_report fan=15
# evolution: baseline
# Keys: M=modules, D=details, i=imports, e=exports, c=classes, f=functions, m=methods
M[103]:
  404.php,61
  Makefile,53
  api/a2a.php,4
  api/connector.php,12
  api/connectors.php,6
  api/mcp.php,4
  api/registry.php,6
  api/search.php,6
  api/validate_connector.php,69
  assets/app.js,268
  assets/ifuri-ecobar.js,108
  categories.php,140
  connector.php,235
  data/catalog.meta.json,44
  data/connector-repo-audit.json,1179
  data/connectors.json,3995
  data/connectors/adb/manifest.json,54
  data/connectors/adopt/manifest.json,54
  data/connectors/agents/manifest.json,54
  data/connectors/base64/manifest.json,66
  data/connectors/browser-control/manifest.json,66
  data/connectors/calendar/manifest.json,54
  data/connectors/camera-web/manifest.json,56
  data/connectors/camera/manifest.json,90
  data/connectors/chat/manifest.json,56
  data/connectors/connectorgen/manifest.json,54
  data/connectors/doc/manifest.json,72
  data/connectors/docid/manifest.json,54
  data/connectors/document/manifest.json,54
  data/connectors/domain-monitor/manifest.json,89
  data/connectors/email/manifest.json,65
  data/connectors/flow-repair/manifest.json,52
  data/connectors/fs/manifest.json,69
  data/connectors/get-node/manifest.json,59
  data/connectors/github/manifest.json,63
  data/connectors/grpc-transport/manifest.json,56
  data/connectors/hash/manifest.json,57
  data/connectors/him/manifest.json,62
  data/connectors/http-check/manifest.json,57
  data/connectors/img2nl/manifest.json,57
  data/connectors/invoice/manifest.json,71
  data/connectors/koru/manifest.json,54
  data/connectors/ksef/manifest.json,101
  data/connectors/kvm/manifest.json,60
  data/connectors/linkedin/manifest.json,54
  data/connectors/llm/manifest.json,62
  data/connectors/mcp-filesystem/manifest.json,62
  data/connectors/message/manifest.json,54
  data/connectors/mqtt/manifest.json,56
  data/connectors/namecheap-dns/manifest.json,89
  data/connectors/netscan/manifest.json,54
  data/connectors/nodeadmin/manifest.json,54
  data/connectors/ocr/manifest.json,56
  data/connectors/pdf/manifest.json,73
  data/connectors/planfile/manifest.json,91
  data/connectors/rdp/manifest.json,59
  data/connectors/router/manifest.json,54
  data/connectors/scanner/manifest.json,54
  data/connectors/screen/manifest.json,57
  data/connectors/sheet/manifest.json,74
  data/connectors/smart-crop/manifest.json,57
  data/connectors/sqlite-context/manifest.json,100
  data/connectors/stepper/manifest.json,61
  data/connectors/stt/manifest.json,57
  data/connectors/time-tools/manifest.json,65
  data/connectors/twin/manifest.json,54
  data/connectors/urifix/manifest.json,54
  data/connectors/urimail/manifest.json,59
  data/connectors/urioffice/manifest.json,59
  data/connectors/urivision-runtime/manifest.json,57
  data/connectors/urivision/manifest.json,55
  data/connectors/usb/manifest.json,76
  data/connectors/uuid/manifest.json,57
  data/connectors/vdisplay/manifest.json,54
  data/connectors/view/manifest.json,54
  data/connectors/vql/manifest.json,56
  data/connectors/webnode/manifest.json,54
  data/connectors/webrtc/manifest.json,58
  data/connectors/youtube/manifest.json,54
  data/publishers.json,5
  index.php,211
  install.php,9
  llms.php,52
  project.sh,66
  robots.php,9
  router.php,74
  schema/connector.schema.json,106
  schema/connectors.schema.json,90
  scripts/check_connectors.py,117
  scripts/connector-template/Makefile,18
  scripts/connector-template/pkg/__init__.py,6
  scripts/connector-template/pkg/cli.py,39
  scripts/connector-template/pkg/connector.manifest.json,27
  scripts/connector-template/pkg/core.py,41
  scripts/deploy-plesk.sh,15
  scripts/new-connector.sh,39
  scripts/sign-manifest.php,90
  scripts/validate_connectors.py,59
  sitemap.php,34
  submit.php,225
  tools/audit_connector_repos.py,459
  tools/build_catalog.py,171
  tree.sh,4
D:
  assets/app.js:
    e: checks,command,copyInstall,selectAvailable,hubBase,selectedIds,installCommand,suffix,refreshCommand,copyText,input,previous,button,button,target,search,cards,count,noResults,filterConnectors,term,visible,ofWord,buttons,panels,target,active,active,builder,form,output,folder,result,template,lineList,escapeHtml,setField,field,readManifest,values,pipValues,renderManifest,manifest,renderValidation,i18n,validText,errors,fixTemplate,fixText,loadTemplate,manifest,response,payload,root,saved,nav,isDark,btn,render,next
    checks()
    command()
    copyInstall()
    selectAvailable()
    hubBase()
    selectedIds()
    installCommand()
    suffix()
    refreshCommand()
    copyText()
    input()
    previous()
    button()
    button()
    target()
    search()
    cards()
    count()
    noResults()
    filterConnectors()
    term()
    visible()
    ofWord()
    buttons()
    panels()
    target()
    active()
    active()
    builder()
    form()
    output()
    folder()
    result()
    template()
    lineList()
    escapeHtml()
    setField()
    field()
    readManifest()
    values()
    pipValues()
    renderManifest()
    manifest()
    renderValidation()
    i18n()
    validText()
    errors()
    fixTemplate()
    fixText()
    loadTemplate()
    manifest()
    response()
    payload()
    root()
    saved()
    nav()
    isDark()
    btn()
    render()
    next()
  tools/audit_connector_repos.py:
    e: run,load_json,connector_id,is_connector_repo,gh_env,run_gh,has_gh,discover_local_repos,discover_gh_repos,merge_repos,load_manifests,load_catalog_entries,read_local_file,read_remote_file,read_repo_file,pyproject_version,local_tags,remote_tag_present,tag_present,add_issue,detail_for,summarize,build_report,print_report,check_failed,main
    run(cmd)
    load_json(path)
    connector_id(repo_name)
    is_connector_repo(name)
    gh_env()
    run_gh(cmd)
    has_gh()
    discover_local_repos(org_dir)
    discover_gh_repos()
    merge_repos(local;gh)
    load_manifests()
    load_catalog_entries()
    read_local_file(repo;name)
    read_remote_file(repo;name)
    read_repo_file(repo;name)
    pyproject_version(repo)
    local_tags(repo)
    remote_tag_present(repo;expected_tag)
    tag_present(repo;expected_tag)
    add_issue(detail;issue)
    detail_for(cid;repo;manifest;catalog_entry)
    summarize(details;issue)
    build_report(args)
    print_report(report)
    check_failed(report)
    main()
  scripts/check_connectors.py:
    e: reachable,main
    reachable(url;timeout)
    main()
  tools/build_catalog.py:
    e: load_json,validate_manifest,public_connector,build_catalog,encode,main
    load_json(path)
    validate_manifest(path;manifest)
    public_connector(manifest)
    build_catalog()
    encode(data)
    main()
  assets/ifuri-ecobar.js:
    e: params,lang,view,host,curView,isActive,esc,navHTML,label,hostEl,sr,p
    params()
    lang()
    view()
    host()
    curView()
    isActive()
    esc()
    navHTML()
    label()
    hostEl()
    sr()
    p()
  scripts/validate_connectors.py:
    e: load,main
    load(rel)
    main()
  scripts/connector-template/pkg/cli.py:
    e: emit,main
    emit(payload)
    main(argv)
  scripts/sign-manifest.php:
    e: die_err,arg_value
    die_err()
    arg_value()
  scripts/connector-template/pkg/core.py:
    e: _json_resource,connector_manifest,run_command,urirun_bindings,run
    _json_resource(name)
    connector_manifest()
    run_command(target;timeout)
    urirun_bindings()
    run(target;timeout)
  Makefile:
  tree.sh:
  install.php:
  project.sh:
  categories.php:
  schema/connector.schema.json:
  router.php:
  robots.php:
  llms.php:
  404.php:
  submit.php:
  schema/connectors.schema.json:
  scripts/deploy-plesk.sh:
  scripts/connector-template/Makefile:
  api/connectors.php:
  index.php:
  sitemap.php:
  connector.php:
  api/registry.php:
  api/mcp.php:
  scripts/new-connector.sh:
  api/search.php:
  api/connector.php:
  api/a2a.php:
  data/catalog.meta.json:
  data/connectors/grpc-transport/manifest.json:
  data/connectors/base64/manifest.json:
  scripts/connector-template/pkg/connector.manifest.json:
  data/connectors/ksef/manifest.json:
  data/connector-repo-audit.json:
  data/connectors/flow-repair/manifest.json:
  data/connectors.json:
  data/connectors/img2nl/manifest.json:
  data/connectors/ocr/manifest.json:
  data/publishers.json:
  data/connectors/http-check/manifest.json:
  data/connectors/vql/manifest.json:
  data/connectors/webrtc/manifest.json:
  data/connectors/linkedin/manifest.json:
  data/connectors/pdf/manifest.json:
  data/connectors/doc/manifest.json:
  data/connectors/sheet/manifest.json:
  data/connectors/namecheap-dns/manifest.json:
  data/connectors/docid/manifest.json:
  data/connectors/browser-control/manifest.json:
  data/connectors/connectorgen/manifest.json:
  data/connectors/scanner/manifest.json:
  data/connectors/planfile/manifest.json:
  api/validate_connector.php:
  data/connectors/get-node/manifest.json:
  data/connectors/llm/manifest.json:
  data/connectors/usb/manifest.json:
  data/connectors/adb/manifest.json:
  data/connectors/urivision/manifest.json:
  data/connectors/calendar/manifest.json:
  data/connectors/twin/manifest.json:
  data/connectors/view/manifest.json:
  data/connectors/urivision-runtime/manifest.json:
  data/connectors/stt/manifest.json:
  data/connectors/smart-crop/manifest.json:
  data/connectors/hash/manifest.json:
  data/connectors/camera-web/manifest.json:
  data/connectors/fs/manifest.json:
  data/connectors/urifix/manifest.json:
  data/connectors/github/manifest.json:
  data/connectors/screen/manifest.json:
  data/connectors/message/manifest.json:
  data/connectors/mcp-filesystem/manifest.json:
  data/connectors/chat/manifest.json:
  data/connectors/uuid/manifest.json:
  data/connectors/nodeadmin/manifest.json:
  data/connectors/urioffice/manifest.json:
  data/connectors/adopt/manifest.json:
  data/connectors/domain-monitor/manifest.json:
  data/connectors/camera/manifest.json:
  data/connectors/invoice/manifest.json:
  data/connectors/rdp/manifest.json:
  data/connectors/vdisplay/manifest.json:
  data/connectors/email/manifest.json:
  data/connectors/document/manifest.json:
  data/connectors/agents/manifest.json:
  data/connectors/time-tools/manifest.json:
  data/connectors/mqtt/manifest.json:
  data/connectors/sqlite-context/manifest.json:
  data/connectors/webnode/manifest.json:
  data/connectors/router/manifest.json:
  data/connectors/him/manifest.json:
  data/connectors/stepper/manifest.json:
  data/connectors/koru/manifest.json:
  data/connectors/urimail/manifest.json:
  data/connectors/netscan/manifest.json:
  data/connectors/youtube/manifest.json:
  data/connectors/kvm/manifest.json:
  scripts/connector-template/pkg/__init__.py:
```

### `project/logic.pl`

```prolog markpact:analysis path=project/logic.pl
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
```

## Call Graph

*60 nodes · 56 edges · 7 modules · CC̄=4.4*

### Hubs (by degree)

| Function | CC | in | out | total |
|----------|----|----|-----|-------|
| `main` *(in scripts.validate_connectors)* | 9 | 0 | 37 | **37** |
| `detail_for` *(in tools.audit_connector_repos)* | 23 ⚠ | 1 | 29 | **30** |
| `build_report` *(in tools.audit_connector_repos)* | 8 | 1 | 26 | **27** |
| `validate_manifest` *(in tools.build_catalog)* | 16 ⚠ | 1 | 20 | **21** |
| `main` *(in tools.audit_connector_repos)* | 5 | 0 | 18 | **18** |
| `print_report` *(in tools.audit_connector_repos)* | 7 | 1 | 15 | **16** |
| `main` *(in scripts.connector-template.pkg.cli)* | 5 | 0 | 15 | **15** |
| `read_remote_file` *(in tools.audit_connector_repos)* | 10 ⚠ | 1 | 13 | **14** |

```toon markpact:analysis path=project/calls.toon.yaml
# code2llm call graph | /home/tom/github/if-uri/connect.ifuri.com
# generated in 0.02s
# nodes: 60 | edges: 56 | modules: 7
# CC̄=4.4

HUBS[20]:
  scripts.validate_connectors.main
    CC=9  in:0  out:37  total:37
  tools.audit_connector_repos.detail_for
    CC=23  in:1  out:29  total:30
  tools.audit_connector_repos.build_report
    CC=8  in:1  out:26  total:27
  tools.build_catalog.validate_manifest
    CC=16  in:1  out:20  total:21
  tools.audit_connector_repos.main
    CC=5  in:0  out:18  total:18
  tools.audit_connector_repos.print_report
    CC=7  in:1  out:15  total:16
  scripts.connector-template.pkg.cli.main
    CC=5  in:0  out:15  total:15
  tools.audit_connector_repos.read_remote_file
    CC=10  in:1  out:13  total:14
  tools.build_catalog.build_catalog
    CC=8  in:1  out:13  total:14
  tools.audit_connector_repos.remote_tag_present
    CC=9  in:1  out:11  total:12
  tools.build_catalog.main
    CC=5  in:0  out:12  total:12
  tools.audit_connector_repos.discover_gh_repos
    CC=9  in:1  out:10  total:11
  tools.audit_connector_repos.add_issue
    CC=1  in:10  out:1  total:11
  assets.app.renderValidation
    CC=7  in:2  out:8  total:10
  tools.audit_connector_repos.merge_repos
    CC=4  in:1  out:9  total:10
  tools.audit_connector_repos.discover_local_repos
    CC=5  in:1  out:8  total:9
  assets.app.copyText
    CC=5  in:1  out:8  total:9
  tools.audit_connector_repos.local_tags
    CC=5  in:1  out:8  total:9
  tools.audit_connector_repos.load_manifests
    CC=3  in:1  out:7  total:8
  assets.app.readManifest
    CC=19  in:2  out:6  total:8

MODULES:
  assets.app  [20 funcs]
    btn  CC=2  out:1
    button  CC=4  out:1
    copyText  CC=5  out:8
    escapeHtml  CC=5  out:3
    installCommand  CC=2  out:3
    isDark  CC=2  out:0
    lineList  CC=5  out:3
    loadTemplate  CC=13  out:3
    manifest  CC=2  out:5
    nav  CC=2  out:1
  assets.ifuri-ecobar  [3 funcs]
    esc  CC=1  out:2
    isActive  CC=9  out:1
    navHTML  CC=3  out:4
  scripts.connector-template.pkg.cli  [2 funcs]
    emit  CC=1  out:2
    main  CC=5  out:15
  scripts.connector-template.pkg.core  [3 funcs]
    _json_resource  CC=2  out:6
    connector_manifest  CC=1  out:1
    run  CC=1  out:0
  scripts.validate_connectors  [2 funcs]
    load  CC=1  out:3
    main  CC=9  out:37
  tools.audit_connector_repos  [24 funcs]
    add_issue  CC=1  out:1
    build_report  CC=8  out:26
    connector_id  CC=1  out:1
    detail_for  CC=23  out:29
    discover_gh_repos  CC=9  out:10
    discover_local_repos  CC=5  out:8
    gh_env  CC=3  out:1
    has_gh  CC=1  out:1
    is_connector_repo  CC=2  out:1
    load_catalog_entries  CC=4  out:5
  tools.build_catalog  [6 funcs]
    build_catalog  CC=8  out:13
    encode  CC=1  out:1
    load_json  CC=2  out:4
    main  CC=5  out:12
    public_connector  CC=3  out:0
    validate_manifest  CC=16  out:20

EDGES:
  assets.ifuri-ecobar.navHTML → assets.ifuri-ecobar.esc
  assets.ifuri-ecobar.navHTML → assets.ifuri-ecobar.isActive
  assets.app.installCommand → assets.app.selectedIds
  assets.app.refreshCommand → assets.app.installCommand
  assets.app.button → assets.app.copyText
  assets.app.readManifest → assets.app.lineList
  assets.app.renderManifest → assets.app.readManifest
  assets.app.manifest → assets.app.renderValidation
  assets.app.renderValidation → assets.app.escapeHtml
  assets.app.validText → assets.app.escapeHtml
  assets.app.loadTemplate → assets.app.setField
  assets.app.loadTemplate → assets.app.renderManifest
  assets.app.root → assets.app.isDark
  assets.app.saved → assets.app.isDark
  assets.app.nav → assets.app.isDark
  assets.app.btn → assets.app.isDark
  assets.app.render → assets.app.isDark
  scripts.validate_connectors.main → scripts.validate_connectors.load
  scripts.connector-template.pkg.cli.main → scripts.connector-template.pkg.core.run
  scripts.connector-template.pkg.cli.main → scripts.connector-template.pkg.cli.emit
  scripts.connector-template.pkg.core.connector_manifest → scripts.connector-template.pkg.core._json_resource
  tools.build_catalog.build_catalog → tools.build_catalog.load_json
  tools.build_catalog.build_catalog → tools.build_catalog.validate_manifest
  tools.build_catalog.build_catalog → tools.build_catalog.public_connector
  tools.build_catalog.main → tools.build_catalog.encode
  tools.build_catalog.main → tools.build_catalog.build_catalog
  tools.audit_connector_repos.run_gh → tools.audit_connector_repos.gh_env
  tools.audit_connector_repos.discover_local_repos → tools.audit_connector_repos.is_connector_repo
  tools.audit_connector_repos.discover_local_repos → tools.audit_connector_repos.connector_id
  tools.audit_connector_repos.discover_gh_repos → tools.audit_connector_repos.run_gh
  tools.audit_connector_repos.discover_gh_repos → tools.audit_connector_repos.has_gh
  tools.audit_connector_repos.discover_gh_repos → tools.audit_connector_repos.connector_id
  tools.audit_connector_repos.discover_gh_repos → tools.audit_connector_repos.is_connector_repo
  tools.audit_connector_repos.load_manifests → tools.audit_connector_repos.load_json
  tools.audit_connector_repos.load_catalog_entries → tools.audit_connector_repos.load_json
  tools.audit_connector_repos.read_remote_file → tools.audit_connector_repos.run_gh
  tools.audit_connector_repos.read_remote_file → tools.audit_connector_repos.has_gh
  tools.audit_connector_repos.read_repo_file → tools.audit_connector_repos.read_local_file
  tools.audit_connector_repos.read_repo_file → tools.audit_connector_repos.read_remote_file
  tools.audit_connector_repos.pyproject_version → tools.audit_connector_repos.read_repo_file
  tools.audit_connector_repos.local_tags → tools.audit_connector_repos.run
  tools.audit_connector_repos.remote_tag_present → tools.audit_connector_repos.run
  tools.audit_connector_repos.remote_tag_present → tools.audit_connector_repos.has_gh
  tools.audit_connector_repos.remote_tag_present → tools.audit_connector_repos.run_gh
  tools.audit_connector_repos.tag_present → tools.audit_connector_repos.local_tags
  tools.audit_connector_repos.tag_present → tools.audit_connector_repos.remote_tag_present
  tools.audit_connector_repos.detail_for → tools.audit_connector_repos.add_issue
  tools.audit_connector_repos.detail_for → tools.audit_connector_repos.read_repo_file
  tools.audit_connector_repos.detail_for → tools.audit_connector_repos.pyproject_version
  tools.audit_connector_repos.build_report → tools.audit_connector_repos.discover_local_repos
```

## Intent

connect.ifuri.com
