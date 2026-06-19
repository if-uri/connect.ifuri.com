# connect.ifuri.com

PHP connector hub for ifuri and urirun.

The site is designed for Plesk hosting and exposes:

- `GET /` - human-friendly connector picker,
- `GET /connectors.json` - connector catalog,
- `GET /registry.json` - machine-readable URI package catalog,
- `GET /install?connectors=planfile,namecheap-dns` - shell installer script.

## Local run

```bash
php -S 127.0.0.1:8099 -t .
```

Open `http://127.0.0.1:8099/`.

## Test

```bash
bash tests/smoke.sh
CONNECT_HUB_BASE=https://connect.ifuri.com bash tests/smoke.sh
CONNECT_HUB_BASE=https://connect.ifuri.com CONNECT_HUB_INSTALL_TEST=1 bash tests/smoke.sh
```

## One-liner

```bash
curl -fsSL 'https://connect.ifuri.com/install?connectors=planfile,namecheap-dns' | bash
```

By default the installer uses the current GitHub source for the Python urirun
package. Override it when the final `if-uri/urirun` package is published:

```bash
URIRUN_PIP_SPEC='urirun @ git+https://github.com/if-uri/urirun.git@main#subdirectory=runtimes/python' \
curl -fsSL 'https://connect.ifuri.com/install?connectors=planfile' | bash
```

## Plesk deploy

Point the domain document root to this repository root or upload the repository
contents to the domain `httpdocs` directory. No Composer step is required.
