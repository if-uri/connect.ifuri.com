#!/usr/bin/env php
<?php
// Author: Tom Sapletta · https://tom.sapletta.com
// Part of the ifURI solution.

declare(strict_types=1);

/**
 * Sign / verify connector manifests with Ed25519 (libsodium).
 *
 *   keygen [--out secret.key]         generate a keypair; prints the base64
 *                                     publicKey to register in data/publishers.json
 *   sign  <manifest.json> --key <secret.key> --publisher <id>
 *                                     embed a detached signature under `trust`
 *   verify <manifest.json>            verify against the trusted publisher keyring
 *
 * Signature covers the canonical manifest (keys sorted, compact JSON) minus the
 * `trust` block — identical bytes to lib/hub.php hub_canonical_manifest().
 */

require_once dirname(__DIR__) . '/lib/hub.php';

function die_err(string $msg): never
{
    fwrite(STDERR, "error: {$msg}\n");
    exit(1);
}

function arg_value(array $argv, string $flag): ?string
{
    $i = array_search($flag, $argv, true);
    return ($i !== false && isset($argv[$i + 1])) ? (string) $argv[$i + 1] : null;
}

$argv = $_SERVER['argv'];
$cmd = $argv[1] ?? '';

if ($cmd === 'keygen') {
    $pair = sodium_crypto_sign_keypair();
    $secret = sodium_crypto_sign_secretkey($pair);
    $public = sodium_crypto_sign_publickey($pair);
    $out = arg_value($argv, '--out') ?? 'connector-signing.key';
    file_put_contents($out, base64_encode($secret));
    @chmod($out, 0600);
    fwrite(STDERR, "secret key written to {$out} (keep private)\n");
    echo "publicKey: " . base64_encode($public) . "\n";
    exit(0);
}

if ($cmd === 'sign') {
    $path = $argv[2] ?? die_err('usage: sign <manifest.json> --key <secret.key> --publisher <id>');
    $keyFile = arg_value($argv, '--key') ?? die_err('--key <secret.key> required');
    $publisher = arg_value($argv, '--publisher') ?? die_err('--publisher <id> required');
    is_file($path) || die_err("manifest not found: {$path}");
    is_file($keyFile) || die_err("key not found: {$keyFile}");

    $manifest = json_decode((string) file_get_contents($path), true);
    is_array($manifest) || die_err('manifest is not a JSON object');
    $secret = base64_decode(trim((string) file_get_contents($keyFile)), true);
    ($secret !== false && strlen($secret) === SODIUM_CRYPTO_SIGN_SECRETKEYBYTES)
        || die_err('invalid secret key');

    $signature = sodium_crypto_sign_detached(hub_canonical_manifest($manifest), $secret);
    $manifest['trust'] = [
        'alg' => 'ed25519',
        'publisherId' => $publisher,
        'signature' => base64_encode($signature),
        'signedAt' => gmdate('c'),
    ];
    file_put_contents($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
    fwrite(STDERR, "signed {$path} as publisher '{$publisher}'\n");
    exit(0);
}

if ($cmd === 'verify') {
    $path = $argv[2] ?? die_err('usage: verify <manifest.json>');
    is_file($path) || die_err("manifest not found: {$path}");
    $manifest = json_decode((string) file_get_contents($path), true);
    is_array($manifest) || die_err('manifest is not a JSON object');
    $ok = hub_manifest_verified($manifest);
    echo json_encode([
        'verified' => $ok,
        'publisherId' => $manifest['trust']['publisherId'] ?? null,
        'publishers' => hub_publishers_path(),
    ], JSON_UNESCAPED_SLASHES) . "\n";
    exit($ok ? 0 : 2);
}

fwrite(STDERR, "usage: sign-manifest.php {keygen|sign|verify} ...\n");
exit(1);
