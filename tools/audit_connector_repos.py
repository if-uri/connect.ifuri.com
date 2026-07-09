#!/usr/bin/env python3
# Author: Tom Sapletta - https://tom.sapletta.com
# Part of the ifURI solution.

"""Audit urirun connector repositories against the public connector catalog.

The audit compares connector repositories named ``urirun-connector-*`` with:

- source manifests in ``data/connectors/<id>/manifest.json``
- the generated ``data/connectors.json`` catalog
- repository version sources: ``VERSION``, optional ``pyproject.toml`` and Git tag

It works locally by scanning the organization checkout parent directory (``..``)
and can also use GitHub CLI/API when ``gh`` is available.
"""

from __future__ import annotations

import argparse
import base64
import json
import os
import re
import shutil
import subprocess
import sys
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


ROOT = Path(__file__).resolve().parents[1]
DEFAULT_ORG_DIR = ROOT.parent
CONNECTORS_DIR = ROOT / "data" / "connectors"
CATALOG_PATH = ROOT / "data" / "connectors.json"
DEFAULT_REPORT_PATH = ROOT / "data" / "connector-repo-audit.json"

ORG = "if-uri"
REPO_PREFIX = "urirun-connector-"
TOOLKIT_REPO = "urirun-connectors-toolkit"
SEMVER_RE = re.compile(r"^[0-9]+\.[0-9]+\.[0-9]+(?:[-+][0-9A-Za-z.-]+)?$")
PYPROJECT_VERSION_RE = re.compile(r"(?m)^version\s*=\s*[\"']([^\"']+)[\"']")

CHECK_ISSUES = {
    "missingManifest",
    "missingCatalogEntry",
    "missingVersionField",
    "missingVersionFile",
    "emptyVersionFile",
    "versionMismatch",
    "missingGitTag",
}


def run(cmd: list[str], *, cwd: Path | None = None, timeout: int = 30) -> subprocess.CompletedProcess[str]:
    return subprocess.run(
        cmd,
        cwd=str(cwd) if cwd else None,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        timeout=timeout,
        check=False,
    )


def load_json(path: Path) -> dict[str, Any]:
    with path.open("r", encoding="utf-8") as handle:
        data = json.load(handle)
    if not isinstance(data, dict):
        raise ValueError(f"{path}: expected JSON object")
    return data


def connector_id(repo_name: str) -> str:
    return repo_name.removeprefix(REPO_PREFIX)


def is_connector_repo(name: str) -> bool:
    return name.startswith(REPO_PREFIX) and name != TOOLKIT_REPO


def gh_env() -> dict[str, str]:
    env = os.environ.copy()
    if "GH_TOKEN" not in env and "GITHUB_TOKEN" in env:
        env["GH_TOKEN"] = env["GITHUB_TOKEN"]
    return env


def run_gh(cmd: list[str], *, timeout: int = 30) -> subprocess.CompletedProcess[str]:
    return subprocess.run(
        ["gh", *cmd],
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        timeout=timeout,
        check=False,
        env=gh_env(),
    )


def has_gh() -> bool:
    return shutil.which("gh") is not None


def discover_local_repos(org_dir: Path) -> dict[str, dict[str, Any]]:
    repos: dict[str, dict[str, Any]] = {}
    if not org_dir.exists():
        return repos
    for path in sorted(org_dir.iterdir()):
        if path.is_dir() and is_connector_repo(path.name):
            cid = connector_id(path.name)
            repos[cid] = {
                "id": cid,
                "repoName": path.name,
                "nameWithOwner": f"{ORG}/{path.name}",
                "repoPath": str(path.resolve()),
                "local": True,
                "gh": False,
            }
    return repos


def discover_gh_repos() -> dict[str, dict[str, Any]]:
    repos: dict[str, dict[str, Any]] = {}
    if not has_gh():
        return repos
    proc = run_gh([
        "repo",
        "list",
        ORG,
        "--limit",
        "500",
        "--visibility",
        "public",
        "--json",
        "name,nameWithOwner,isArchived",
    ], timeout=60)
    if proc.returncode != 0:
        return repos
    for item in json.loads(proc.stdout or "[]"):
        name = str(item.get("name") or "")
        if bool(item.get("isArchived")) or not is_connector_repo(name):
            continue
        cid = connector_id(name)
        repos[cid] = {
            "id": cid,
            "repoName": name,
            "nameWithOwner": item.get("nameWithOwner") or f"{ORG}/{name}",
            "repoPath": None,
            "local": False,
            "gh": True,
        }
    return repos


def merge_repos(local: dict[str, dict[str, Any]], gh: dict[str, dict[str, Any]]) -> dict[str, dict[str, Any]]:
    repos = {cid: dict(info) for cid, info in gh.items()}
    for cid, info in local.items():
        if cid in repos:
            repos[cid].update({
                "repoPath": info["repoPath"],
                "local": True,
                "gh": repos[cid].get("gh", False),
            })
        else:
            repos[cid] = dict(info)
    return dict(sorted(repos.items()))


def load_manifests() -> dict[str, dict[str, Any]]:
    manifests: dict[str, dict[str, Any]] = {}
    for path in sorted(CONNECTORS_DIR.glob("*/manifest.json")):
        manifest = load_json(path)
        cid = str(manifest.get("id") or path.parent.name)
        manifest["_path"] = str(path.resolve())
        manifests[cid] = manifest
    return manifests


def load_catalog_entries() -> dict[str, dict[str, Any]]:
    catalog = load_json(CATALOG_PATH)
    entries: dict[str, dict[str, Any]] = {}
    for entry in catalog.get("connectors", []):
        if isinstance(entry, dict) and entry.get("id"):
            entries[str(entry["id"])] = entry
    return entries


def read_local_file(repo: dict[str, Any], name: str) -> str | None:
    repo_path = repo.get("repoPath")
    if not repo_path:
        return None
    path = Path(str(repo_path)) / name
    if not path.exists() or not path.is_file():
        return None
    return path.read_text(encoding="utf-8").strip()


def read_remote_file(repo: dict[str, Any], name: str) -> str | None:
    if not repo.get("gh") or not has_gh():
        return None
    owner_repo = str(repo.get("nameWithOwner") or f"{ORG}/{repo['repoName']}")
    proc = run_gh(["api", f"repos/{owner_repo}/contents/{name}"], timeout=30)
    if proc.returncode != 0:
        return None
    try:
        payload = json.loads(proc.stdout)
        content = str(payload.get("content") or "")
        encoding = str(payload.get("encoding") or "")
        if encoding == "base64" and content:
            return base64.b64decode(content).decode("utf-8").strip()
    except Exception:
        return None
    return None


def read_repo_file(repo: dict[str, Any], name: str) -> str | None:
    return read_local_file(repo, name) if repo.get("local") else read_remote_file(repo, name)


def pyproject_version(repo: dict[str, Any]) -> str | None:
    raw = read_repo_file(repo, "pyproject.toml")
    if raw is None:
        return None
    match = PYPROJECT_VERSION_RE.search(raw)
    return match.group(1).strip() if match else None


def local_tags(repo: dict[str, Any]) -> set[str]:
    repo_path = repo.get("repoPath")
    if not repo_path:
        return set()
    proc = run(["git", "-C", str(repo_path), "tag", "--list"], timeout=30)
    if proc.returncode != 0:
        return set()
    return {line.strip() for line in proc.stdout.splitlines() if line.strip()}


def remote_tag_present(repo: dict[str, Any], expected_tag: str) -> bool | None:
    if repo.get("local"):
        repo_path = str(repo.get("repoPath") or "")
        proc = run(["git", "-C", repo_path, "ls-remote", "--tags", "origin", f"refs/tags/{expected_tag}"], timeout=45)
        if proc.returncode == 0:
            return bool(proc.stdout.strip())
    if repo.get("gh") and has_gh():
        owner_repo = str(repo.get("nameWithOwner") or f"{ORG}/{repo['repoName']}")
        proc = run_gh(["api", f"repos/{owner_repo}/git/ref/tags/{expected_tag}"], timeout=30)
        if proc.returncode == 0:
            return True
        if proc.returncode == 1:
            return False
    return None


def tag_present(repo: dict[str, Any], expected_tag: str) -> bool:
    tags = local_tags(repo)
    if expected_tag in tags:
        return True
    remote = remote_tag_present(repo, expected_tag)
    return bool(remote)


def add_issue(detail: dict[str, Any], issue: str) -> None:
    detail["issues"].append(issue)


def detail_for(
    cid: str,
    repo: dict[str, Any] | None,
    manifest: dict[str, Any] | None,
    catalog_entry: dict[str, Any] | None,
) -> dict[str, Any]:
    version_manifest = manifest.get("version") if manifest else None
    version_catalog = catalog_entry.get("version") if catalog_entry else None
    version_file = None
    version_pyproject = None
    expected_tag = None
    present_tag = None

    detail = {
        "id": cid,
        "repoName": repo.get("repoName") if repo else f"{REPO_PREFIX}{cid}",
        "repoPath": repo.get("repoPath") if repo else None,
        "manifestPath": manifest.get("_path") if manifest else None,
        "catalogPresent": catalog_entry is not None,
        "manifestPresent": manifest is not None,
        "versionManifest": version_manifest,
        "versionCatalog": version_catalog,
        "versionFile": None,
        "versionPyproject": None,
        "expectedTag": None,
        "tagPresent": None,
        "issues": [],
    }

    if repo is None:
        add_issue(detail, "manifestWithoutRepo")
    else:
        raw_version = read_repo_file(repo, "VERSION")
        if raw_version is None:
            add_issue(detail, "missingVersionFile")
        elif raw_version.strip() == "":
            add_issue(detail, "emptyVersionFile")
            version_file = ""
        else:
            version_file = raw_version.splitlines()[0].strip()
            if not SEMVER_RE.match(version_file):
                add_issue(detail, "versionMismatch")

        version_pyproject = pyproject_version(repo)
        if version_file:
            expected_tag = f"v{version_file}"
            present_tag = tag_present(repo, expected_tag)
            if not present_tag:
                add_issue(detail, "missingGitTag")

    if repo is not None and manifest is None:
        add_issue(detail, "missingManifest")
    if repo is not None and catalog_entry is None:
        add_issue(detail, "missingCatalogEntry")
    if manifest is not None and not version_manifest:
        add_issue(detail, "missingVersionField")
    if catalog_entry is not None and not version_catalog:
        add_issue(detail, "missingVersionField")

    comparable = {
        "VERSION": version_file,
        "pyproject": version_pyproject,
        "manifest": version_manifest,
        "catalog": version_catalog,
    }
    values = {name: value for name, value in comparable.items() if value not in (None, "")}
    if len(set(values.values())) > 1:
        add_issue(detail, "versionMismatch")

    detail.update({
        "versionManifest": version_manifest,
        "versionCatalog": version_catalog,
        "versionFile": version_file,
        "versionPyproject": version_pyproject,
        "expectedTag": expected_tag,
        "tagPresent": present_tag,
    })
    detail["issues"] = sorted(set(detail["issues"]))
    return detail


def summarize(details: list[dict[str, Any]], issue: str) -> list[str]:
    return [item["id"] for item in details if issue in item["issues"]]


def build_report(args: argparse.Namespace) -> dict[str, Any]:
    local_repos = discover_local_repos(args.org_dir)
    gh_repos = discover_gh_repos() if not args.no_gh else {}
    repos = merge_repos(local_repos, gh_repos)
    manifests = load_manifests()
    catalog_entries = load_catalog_entries()

    if local_repos and gh_repos:
        source = "mixed"
    elif gh_repos:
        source = "gh"
    else:
        source = "local"

    ids = sorted(set(repos) | set(manifests) | set(catalog_entries))
    details = [
        detail_for(cid, repos.get(cid), manifests.get(cid), catalog_entries.get(cid))
        for cid in ids
    ]

    report = {
        "generatedAt": datetime.now(timezone.utc).isoformat(),
        "source": source,
        "repoCount": len(repos),
        "manifestCount": len(manifests),
        "catalogCount": len(catalog_entries),
        "missingManifest": summarize(details, "missingManifest"),
        "missingCatalogEntry": summarize(details, "missingCatalogEntry"),
        "manifestWithoutRepo": summarize(details, "manifestWithoutRepo"),
        "missingVersionField": summarize(details, "missingVersionField"),
        "missingVersionFile": summarize(details, "missingVersionFile"),
        "emptyVersionFile": summarize(details, "emptyVersionFile"),
        "versionMismatch": summarize(details, "versionMismatch"),
        "missingGitTag": summarize(details, "missingGitTag"),
        "ok": [item["id"] for item in details if not item["issues"]],
        "perConnector": details,
    }
    return report


def print_report(report: dict[str, Any]) -> None:
    print("Connector repository audit")
    print(f"  generatedAt: {report['generatedAt']}")
    print(f"  source: {report['source']}")
    print(f"  repos: {report['repoCount']}")
    print(f"  manifests: {report['manifestCount']}")
    print(f"  catalog entries: {report['catalogCount']}")
    for key in [
        "missingManifest",
        "missingCatalogEntry",
        "manifestWithoutRepo",
        "missingVersionField",
        "missingVersionFile",
        "emptyVersionFile",
        "versionMismatch",
        "missingGitTag",
    ]:
        values = report[key]
        print(f"  {key}: {len(values)}" + (f" ({', '.join(values)})" if values else ""))
    print(f"  ok: {len(report['ok'])}")

    problem_details = [item for item in report["perConnector"] if item["issues"]]
    if problem_details:
        print()
        print("Issues:")
        for item in problem_details:
            print(f"  - {item['id']}: {', '.join(item['issues'])}")


def check_failed(report: dict[str, Any], *, warn_missing_tags: bool) -> bool:
    failing = set(CHECK_ISSUES)
    if warn_missing_tags:
        failing.discard("missingGitTag")
    for item in report["perConnector"]:
        if failing.intersection(item["issues"]):
            return True
    return False


def main() -> int:
    parser = argparse.ArgumentParser(description="Audit connector repositories against manifests and catalog versions.")
    parser.add_argument("--org-dir", type=Path, default=DEFAULT_ORG_DIR, help="local organization checkout directory (default: ..)")
    parser.add_argument("--output", type=Path, default=DEFAULT_REPORT_PATH, help="JSON report path")
    parser.add_argument("--check", action="store_true", help="exit 1 when blocking audit issues are found")
    parser.add_argument("--warn-missing-tags", action="store_true", help="treat missing Git tags as warnings in --check mode")
    parser.add_argument("--no-gh", action="store_true", help="disable optional GitHub CLI discovery/API checks")
    args = parser.parse_args()

    try:
        report = build_report(args)
    except Exception as exc:
        print(f"audit_connector_repos: {exc}", file=sys.stderr)
        return 1

    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print_report(report)
    print()
    print(f"wrote {args.output.relative_to(ROOT) if args.output.is_relative_to(ROOT) else args.output}")

    if args.check and check_failed(report, warn_missing_tags=args.warn_missing_tags):
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
