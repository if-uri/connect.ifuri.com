#!/usr/bin/env php
<?php
// Author: Tom Sapletta · https://tom.sapletta.com
// Part of the ifURI solution.

declare(strict_types=1);

// Round-trip test for connector manifest signing/verification (Ed25519 / libsodium).
// Self-contained: generates a keypair, signs a real manifest in a temp dir,
// and checks verify / tamper-detection / unknown-publisher via lib/hub.php.

require_once dirname(__DIR__) . '/lib/hub.php';

$failures = 0;
function check(string $label, bool $cond): void
{
    global $failures;
    if ($cond) {
        echo "ok   - {$label}\n";
    } else {
        echo "FAIL - {$label}\n";
        $failures++;
    }
}

$root = dirname(__DIR__);
$tmp = sys_get_temp_dir() . '/ifuri-sign-test-' . bin2hex(random_bytes(4));
mkdir($tmp, 0700, true);

// A real manifest to sign.
$manifest = json_decode((string) file_get_contents($root . '/data/connectors/planfile/manifest.json'), true);

// 1) keygen + register publisher in an isolated trust store.
$pair = sodium_crypto_sign_keypair();
$secret = sodium_crypto_sign_secretkey($pair);
$public = sodium_crypto_sign_publickey($pair);
$publishersFile = $tmp . '/publishers.json';
file_put_contents($publishersFile, json_encode([
    'version' => 'ifuri.publishers.v1',
    'publishers' => [['id' => 'test-pub', 'name' => 'Test', 'publicKey' => base64_encode($public)]],
]));
putenv('IFURI_PUBLISHERS_FILE=' . $publishersFile);

// 2) sign: detached signature over the canonical manifest.
$signed = $manifest;
$signed['trust'] = [
    'alg' => 'ed25519',
    'publisherId' => 'test-pub',
    'signature' => base64_encode(sodium_crypto_sign_detached(hub_canonical_manifest($manifest), $secret)),
    'signedAt' => gmdate('c'),
];
check('signed manifest verifies against trusted publisher', hub_manifest_verified($signed) === true);

// 3) tamper after signing → must fail.
$tampered = $signed;
$tampered['summary'] = 'tampered';
check('tampered manifest fails verification', hub_manifest_verified($tampered) === false);

// 4) unknown publisher (empty store) → must fail.
putenv('IFURI_PUBLISHERS_FILE=' . $tmp . '/none.json');
check('unknown publisher fails verification', hub_manifest_verified($signed) === false);

// 5) missing/!ed25519 trust block → false.
check('manifest without trust block is not verified', hub_manifest_verified($manifest) === false);

// cleanup
@unlink($publishersFile);
@rmdir($tmp);

echo $failures === 0 ? "sign_test: PASS\n" : "sign_test: {$failures} FAILURE(S)\n";
exit($failures === 0 ? 0 : 1);
