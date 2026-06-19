<?php

declare(strict_types=1);

/**
 * Golden snapshot test for the machine endpoints.
 *
 * Locks the public contract of /connectors.json and /registry.json byte-for-byte
 * (modulo the volatile registry timestamp). This is the safety net for the future
 * "source = per-connector folders, served = generated aggregate" migration: as long
 * as this test passes, that refactor provably does not change what the ifuri app sees.
 *
 *   php tests/snapshot_test.php           # verify (exit 1 on drift)
 *   php tests/snapshot_test.php --update  # regenerate goldens after an intentional change
 *
 * Run it after every change to data/connectors.json or lib/hub.php; if the diff is
 * intended, re-run with --update and commit the refreshed golden file.
 */

require_once dirname(__DIR__) . '/lib/hub.php';

const SNAPSHOT_TS = 'SNAPSHOT';

/** Encode exactly like hub_send_json so the snapshot equals the served bytes. */
function snapshot_encode(array $payload): string
{
    return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
}

/** Replace volatile fields with a constant so the snapshot is stable. */
function snapshot_normalize(array $payload): array
{
    if (isset($payload['generatedAt'])) {
        $payload['generatedAt'] = SNAPSHOT_TS;
    }
    return $payload;
}

$targets = [
    'connectors.json' => snapshot_normalize(hub_catalog()),
    'registry.json' => snapshot_normalize(hub_registry()),
];

$goldenDir = __DIR__ . '/golden';
$update = in_array('--update', $argv, true);

if ($update && !is_dir($goldenDir)) {
    mkdir($goldenDir, 0775, true);
}

$failures = 0;
foreach ($targets as $name => $payload) {
    $actual = snapshot_encode($payload);
    $goldenFile = $goldenDir . '/' . $name;

    if ($update) {
        file_put_contents($goldenFile, $actual);
        fwrite(STDOUT, "updated  {$name}\n");
        continue;
    }

    if (!is_file($goldenFile)) {
        fwrite(STDERR, "MISSING  {$name} — run with --update to create it\n");
        $failures++;
        continue;
    }

    $expected = (string) file_get_contents($goldenFile);
    if ($expected === $actual) {
        fwrite(STDOUT, "ok       {$name}\n");
        continue;
    }

    $failures++;
    fwrite(STDERR, "DRIFT    {$name} — served output differs from golden\n");
    $expectedLines = explode("\n", $expected);
    $actualLines = explode("\n", $actual);
    $max = max(count($expectedLines), count($actualLines));
    $shown = 0;
    for ($i = 0; $i < $max && $shown < 12; $i++) {
        $e = $expectedLines[$i] ?? '';
        $a = $actualLines[$i] ?? '';
        if ($e !== $a) {
            fwrite(STDERR, sprintf("  line %d\n    - %s\n    + %s\n", $i + 1, $e, $a));
            $shown++;
        }
    }
}

if ($update) {
    fwrite(STDOUT, "goldens updated.\n");
    exit(0);
}

if ($failures > 0) {
    fwrite(STDERR, "\n{$failures} snapshot(s) drifted. If intended: php tests/snapshot_test.php --update\n");
    exit(1);
}

fwrite(STDOUT, "all snapshots match.\n");
exit(0);
