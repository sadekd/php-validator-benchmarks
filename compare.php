#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . "/vendor/autoload.php";

use Bench\ComparisonFormatter;

$dir = $argv[1] ?? (getenv('RESULTS_DIR') ?: __DIR__);
$files = glob(rtrim($dir, '/') . '/results-*.json') ?: [];

if (count($files) < 2) {
    fwrite(STDERR, sprintf(
        "Need at least two results-<version>.json files in %s to compare, found %d.\n",
        $dir,
        count($files),
    ));
    exit(1);
}

$runs = [];
foreach ($files as $file) {
    $decoded = json_decode((string) file_get_contents($file), true);
    if (!is_array($decoded) || !isset($decoded['php_series'], $decoded['results'])) {
        fwrite(STDERR, "Skipping unreadable run file: {$file}\n");
        continue;
    }
    $runs[] = $decoded;
}

$out = rtrim($dir, '/') . '/COMPARISON.md';
ComparisonFormatter::build($runs, $out);

printf("Compared %d PHP versions -> %s\n", count($runs), $out);
