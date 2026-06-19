# Plesk setup

1. Create the domain or subdomain `connect.ifuri.com` in Plesk.
2. Set PHP version to PHP 8.2 or newer.
3. Set the document root to the repository root, or deploy repository files into
   the domain `httpdocs` directory.
4. Make sure Apache rewrite rules are enabled so `.htaccess` can route:
   - `/install`
   - `/connectors.json`
   - `/registry.json`
   - `/connectors/{id}`
   - `/sitemap.xml`
   - `/robots.txt`
   - `/llms.txt`
5. Test:

```bash
curl -fsSL https://connect.ifuri.com/connectors.json
curl -fsSL https://connect.ifuri.com/connectors/namecheap-dns
curl -fsSL https://connect.ifuri.com/sitemap.xml
curl -fsSL https://connect.ifuri.com/llms.txt
curl -fsSL 'https://connect.ifuri.com/install?connectors=planfile' | bash
```

No Composer install step is required.
