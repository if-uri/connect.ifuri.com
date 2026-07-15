# connect.ifuri.com

SUMD - Structured Unified Markdown Descriptor for AI-aware project refactorization

## Contents

- [Metadata](#metadata)
- [Architecture](#architecture)
- [Workflows](#workflows)
- [Call Graph](#call-graph)
- [Refactoring Analysis](#refactoring-analysis)
- [Intent](#intent)

## Metadata

- **name**: `connect.ifuri.com`
- **version**: `0.0.0`
- **ecosystem**: SUMD + DOQL + testql + taskfile
- **generated_from**: Makefile, app.doql.less, project/(5 analysis files)

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

## Refactoring Analysis

*Pre-refactoring snapshot — use this section to identify targets. Generated from `project/` toon files.*

### Call Graph & Complexity (`project/calls.toon.yaml`)

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

### Code Analysis (`project/analysis.toon.yaml`)

```toon markpact:analysis path=project/analysis.toon.yaml
# code2llm | 103f 12056L | json:70,php:18,python:7,shell:4,javascript:2 | 2026-07-14
# generated in 0.01s
# CC̅=4.4 | critical:5/113 | dups:0 | cycles:0

HEALTH[5]:
  🟡 CC    builder CC=51 (limit:15)
  🟡 CC    readManifest CC=19 (limit:15)
  🟡 CC    main CC=20 (limit:15)
  🟡 CC    validate_manifest CC=16 (limit:15)
  🟡 CC    detail_for CC=23 (limit:15)

REFACTOR[1]:
  1. split 5 high-CC methods  (CC>15)

PIPELINES[44]:
  [1] Src [die_err]: die_err
      PURITY: 100% pure
  [2] Src [arg_value]: arg_value
      PURITY: 100% pure
  [3] Src [host]: host
      PURITY: 100% pure
  [4] Src [curView]: curView
      PURITY: 100% pure
  [5] Src [navHTML]: navHTML → esc
      PURITY: 100% pure
  [6] Src [hostEl]: hostEl
      PURITY: 100% pure
  [7] Src [sr]: sr
      PURITY: 100% pure
  [8] Src [checks]: checks
      PURITY: 100% pure
  [9] Src [command]: command
      PURITY: 100% pure
  [10] Src [copyInstall]: copyInstall
      PURITY: 100% pure
  [11] Src [selectAvailable]: selectAvailable
      PURITY: 100% pure
  [12] Src [hubBase]: hubBase
      PURITY: 100% pure
  [13] Src [suffix]: suffix
      PURITY: 100% pure
  [14] Src [refreshCommand]: refreshCommand → installCommand → selectedIds
      PURITY: 100% pure
  [15] Src [input]: input
      PURITY: 100% pure
  [16] Src [previous]: previous
      PURITY: 100% pure
  [17] Src [button]: button → copyText
      PURITY: 100% pure
  [18] Src [target]: target
      PURITY: 100% pure
  [19] Src [search]: search
      PURITY: 100% pure
  [20] Src [cards]: cards
      PURITY: 100% pure
  [21] Src [count]: count
      PURITY: 100% pure
  [22] Src [noResults]: noResults
      PURITY: 100% pure
  [23] Src [filterConnectors]: filterConnectors
      PURITY: 100% pure
  [24] Src [term]: term
      PURITY: 100% pure
  [25] Src [visible]: visible
      PURITY: 100% pure
  [26] Src [buttons]: buttons
      PURITY: 100% pure
  [27] Src [panels]: panels
      PURITY: 100% pure
  [28] Src [builder]: builder → lineList
      PURITY: 100% pure
  [29] Src [manifest]: manifest → renderValidation → escapeHtml
      PURITY: 100% pure
  [30] Src [validText]: validText → escapeHtml
      PURITY: 100% pure
  [31] Src [fixTemplate]: fixTemplate
      PURITY: 100% pure
  [32] Src [fixText]: fixText
      PURITY: 100% pure
  [33] Src [response]: response
      PURITY: 100% pure
  [34] Src [root]: root → isDark
      PURITY: 100% pure
  [35] Src [saved]: saved → isDark
      PURITY: 100% pure
  [36] Src [nav]: nav → isDark
      PURITY: 100% pure
  [37] Src [btn]: btn → isDark
      PURITY: 100% pure
  [38] Src [render]: render → isDark
      PURITY: 100% pure
  [39] Src [main]: main → load
      PURITY: 100% pure
  [40] Src [main]: main → run
      PURITY: 100% pure
  [41] Src [run_command]: run_command
      PURITY: 100% pure
  [42] Src [main]: main → reachable
      PURITY: 100% pure
  [43] Src [main]: main → encode
      PURITY: 100% pure
  [44] Src [main]: main → print_report
      PURITY: 100% pure

LAYERS:
  tools/                          CC̄=4.9    ←in:0  →out:0
  │ !! audit_connector_repos      459L  0C   26m  CC=23     ←0
  │ !! build_catalog              171L  0C    6m  CC=16     ←0
  │
  assets/                         CC̄=4.3    ←in:0  →out:0
  │ !! app.js                     268L  0C   56m  CC=51     ←0
  │ ifuri-ecobar.js            108L  0C   12m  CC=9      ←0
  │
  scripts/                        CC̄=3.7    ←in:0  →out:0
  │ !! check_connectors           117L  0C    2m  CC=20     ←0
  │ sign-manifest.php           90L  0C    2m  CC=3      ←0
  │ validate_connectors         59L  0C    2m  CC=9      ←0
  │ core                        41L  0C    5m  CC=2      ←1
  │ cli                         39L  0C    2m  CC=5      ←0
  │ new-connector.sh            39L  0C    0m  CC=0.0    ←0
  │ connector.manifest.json     27L  0C    0m  CC=0.0    ←0
  │ Makefile                    18L  0C    0m  CC=0.0    ←0
  │ deploy-plesk.sh             15L  0C    0m  CC=0.0    ←0
  │ __init__                     6L  0C    0m  CC=0.0    ←0
  │
  ./                              CC̄=0.0    ←in:0  →out:0
  │ connector.php              235L  0C    0m  CC=0.0    ←0
  │ submit.php                 225L  0C    0m  CC=0.0    ←0
  │ index.php                  211L  0C    0m  CC=0.0    ←0
  │ categories.php             140L  0C    0m  CC=0.0    ←0
  │ router.php                  74L  0C    0m  CC=0.0    ←0
  │ project.sh                  66L  0C    0m  CC=0.0    ←0
  │ 404.php                     61L  0C    0m  CC=0.0    ←0
  │ Makefile                    53L  0C    0m  CC=0.0    ←0
  │ llms.php                    52L  0C    0m  CC=0.0    ←0
  │ sitemap.php                 34L  0C    0m  CC=0.0    ←0
  │ install.php                  9L  0C    0m  CC=0.0    ←0
  │ robots.php                   9L  0C    0m  CC=0.0    ←0
  │ tree.sh                      4L  0C    0m  CC=0.0    ←0
  │
  schema/                         CC̄=0.0    ←in:0  →out:0
  │ connector.schema.json      106L  0C    0m  CC=0.0    ←0
  │ connectors.schema.json      90L  0C    0m  CC=0.0    ←0
  │
  api/                            CC̄=0.0    ←in:0  →out:0
  │ validate_connector.php      69L  0C    0m  CC=0.0    ←0
  │ connector.php               12L  0C    0m  CC=0.0    ←0
  │ connectors.php               6L  0C    0m  CC=0.0    ←0
  │ registry.php                 6L  0C    0m  CC=0.0    ←0
  │ search.php                   6L  0C    0m  CC=0.0    ←0
  │ mcp.php                      4L  0C    0m  CC=0.0    ←0
  │ a2a.php                      4L  0C    0m  CC=0.0    ←0
  │
  data/                           CC̄=0.0    ←in:0  →out:0
  │ !! connectors.json           3995L  0C    0m  CC=0.0    ←0
  │ !! connector-repo-audit.json  1179L  0C    0m  CC=0.0    ←0
  │ manifest.json              101L  0C    0m  CC=0.0    ←0
  │ manifest.json              100L  0C    0m  CC=0.0    ←0
  │ manifest.json               91L  0C    0m  CC=0.0    ←0
  │ manifest.json               90L  0C    0m  CC=0.0    ←0
  │ manifest.json               89L  0C    0m  CC=0.0    ←0
  │ manifest.json               89L  0C    0m  CC=0.0    ←0
  │ manifest.json               76L  0C    0m  CC=0.0    ←0
  │ manifest.json               74L  0C    0m  CC=0.0    ←0
  │ manifest.json               73L  0C    0m  CC=0.0    ←0
  │ manifest.json               72L  0C    0m  CC=0.0    ←0
  │ manifest.json               71L  0C    0m  CC=0.0    ←0
  │ manifest.json               69L  0C    0m  CC=0.0    ←0
  │ manifest.json               66L  0C    0m  CC=0.0    ←0
  │ manifest.json               66L  0C    0m  CC=0.0    ←0
  │ manifest.json               65L  0C    0m  CC=0.0    ←0
  │ manifest.json               65L  0C    0m  CC=0.0    ←0
  │ manifest.json               63L  0C    0m  CC=0.0    ←0
  │ manifest.json               62L  0C    0m  CC=0.0    ←0
  │ manifest.json               62L  0C    0m  CC=0.0    ←0
  │ manifest.json               62L  0C    0m  CC=0.0    ←0
  │ manifest.json               61L  0C    0m  CC=0.0    ←0
  │ manifest.json               60L  0C    0m  CC=0.0    ←0
  │ manifest.json               59L  0C    0m  CC=0.0    ←0
  │ manifest.json               59L  0C    0m  CC=0.0    ←0
  │ manifest.json               59L  0C    0m  CC=0.0    ←0
  │ manifest.json               59L  0C    0m  CC=0.0    ←0
  │ manifest.json               58L  0C    0m  CC=0.0    ←0
  │ manifest.json               57L  0C    0m  CC=0.0    ←0
  │ manifest.json               57L  0C    0m  CC=0.0    ←0
  │ manifest.json               57L  0C    0m  CC=0.0    ←0
  │ manifest.json               57L  0C    0m  CC=0.0    ←0
  │ manifest.json               57L  0C    0m  CC=0.0    ←0
  │ manifest.json               57L  0C    0m  CC=0.0    ←0
  │ manifest.json               57L  0C    0m  CC=0.0    ←0
  │ manifest.json               57L  0C    0m  CC=0.0    ←0
  │ manifest.json               56L  0C    0m  CC=0.0    ←0
  │ manifest.json               56L  0C    0m  CC=0.0    ←0
  │ manifest.json               56L  0C    0m  CC=0.0    ←0
  │ manifest.json               56L  0C    0m  CC=0.0    ←0
  │ manifest.json               56L  0C    0m  CC=0.0    ←0
  │ manifest.json               56L  0C    0m  CC=0.0    ←0
  │ manifest.json               55L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               54L  0C    0m  CC=0.0    ←0
  │ manifest.json               52L  0C    0m  CC=0.0    ←0
  │ catalog.meta.json           44L  0C    0m  CC=0.0    ←0
  │ publishers.json              5L  0C    0m  CC=0.0    ←0
  │

COUPLING: no cross-package imports detected

EXTERNAL:
  validation: run `vallm batch .` → validation.toon
  duplication: run `redup scan .` → duplication.toon
```

### Duplication (`project/duplication.toon.yaml`)

```toon markpact:analysis path=project/duplication.toon.yaml
# redup/duplication | 1 groups | 26f 2717L | 2026-07-14

SUMMARY:
  files_scanned: 26
  total_lines:   2717
  dup_groups:    1
  dup_fragments: 2
  saved_lines:   13
  scan_ms:       112

HOTSPOTS[1] (files with most duplication):
  assets/app.js  dup=28L  groups=1  frags=2  (1.0%)

DUPLICATES[1] (ranked by impact):
  [F0001]   FUZZ  arrow_function  L=13 N=2 saved=13 sim=0.95
      assets/app.js:96-108  (arrow_function)
      assets/app.js:95-109  (arrow_function)

REFACTOR[1] (ranked by priority):
  [1] ○ extract_function   → assets/utils/arrow_function.py
      WHY: 2 occurrences of 13-line block across 1 files — saves 13 lines
      FILES: assets/app.js

QUICK_WINS[1] (low risk, high savings — do first):
  [1] extract_function   saved=13L  → assets/utils/arrow_function.py
      FILES: app.js

EFFORT_ESTIMATE (total ≈ 0.4h):
  easy   arrow_function                      saved=13L  ~26min

METRICS-TARGET:
  dup_groups:  1 → 0
  saved_lines: 13 lines recoverable
```

### Evolution / Churn (`project/evolution.toon.yaml`)

```toon markpact:analysis path=project/evolution.toon.yaml
# code2llm/evolution | 100 func | 4f | 2026-07-14
# generated in 0.00s

NEXT[6] (ranked by impact):
  [1] !! SPLIT-FUNC      builder  CC=51  fan=29
      WHY: CC=51 exceeds 15
      EFFORT: ~1h  IMPACT: 1479

  [2] !  SPLIT-FUNC      detail_for  CC=23  fan=17
      WHY: CC=23 exceeds 15
      EFFORT: ~1h  IMPACT: 391

  [3] !  SPLIT-FUNC      readManifest  CC=19  fan=6
      WHY: CC=19 exceeds 15
      EFFORT: ~1h  IMPACT: 114

  [4] !  SPLIT-FUNC      validate_manifest  CC=16  fan=7
      WHY: CC=16 exceeds 15
      EFFORT: ~1h  IMPACT: 112

  [5] !! SPLIT           data/connectors.json
      WHY: 3995L, 0 classes, max CC=0
      EFFORT: ~4h  IMPACT: 0

  [6] !! SPLIT           data/connector-repo-audit.json
      WHY: 1179L, 0 classes, max CC=0
      EFFORT: ~4h  IMPACT: 0


RISKS[2]:
  ⚠ Splitting data/connectors.json may break 0 import paths
  ⚠ Splitting data/connector-repo-audit.json may break 0 import paths

METRICS-TARGET:
  CC̄:          4.5 → ≤3.1
  max-CC:      51 → ≤20
  god-modules: 2 → 0
  high-CC(≥15): 4 → ≤2
  hub-types:   0 → ≤0

PATTERNS (language parser shared logic):
  _extract_declarations() in base.py — unified extraction for:
    - TypeScript: interfaces, types, classes, functions, arrow funcs
    - PHP: namespaces, traits, classes, functions, includes
    - Ruby: modules, classes, methods, requires
    - C++: classes, structs, functions, #includes
    - C#: classes, interfaces, methods, usings
    - Java: classes, interfaces, methods, imports
    - Go: packages, functions, structs
    - Rust: modules, functions, traits, use statements

  Shared regex patterns per language:
    - import: language-specific import/require/using patterns
    - class: class/struct/trait declarations with inheritance
    - function: function/method signatures with visibility
    - brace_tracking: for C-family languages ({ })
    - end_keyword_tracking: for Ruby (module/class/def...end)

  Benefits:
    - Consistent extraction logic across all languages
    - Reduced code duplication (~70% reduction in parser LOC)
    - Easier maintenance: fix once, apply everywhere
    - Standardized FunctionInfo/ClassInfo models

HISTORY:
  (first run — no previous data)
```

## Intent

connect.ifuri.com
