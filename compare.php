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
$unreadable = [];
foreach ($files as $file) {
    $decoded = json_decode((string) file_get_contents($file), true);
    if (!is_array($decoded) || !isset($decoded['php_series'], $decoded['results'])) {
        fwrite(STDERR, "Skipping unreadable run file: {$file}\n");
        $unreadable[] = $file;
        continue;
    }
    $runs[] = $decoded;
}

if (count($runs) < 2) {
    fwrite(STDERR, sprintf(
        "Need at least two readable runs to compare, found %d; COMPARISON.md not written.\n",
        count($runs),
    ));
    foreach ($unreadable as $file) {
        fwrite(STDERR, "  - unreadable: {$file}\n");
    }
    exit(1);
}

$problems = ComparisonFormatter::validate($runs);
if ($problems !== []) {
    fwrite(STDERR, "Refusing to compare these runs; COMPARISON.md not written:\n");
    foreach ($problems as $problem) {
        fwrite(STDERR, "  - {$problem}\n");
    }
    fwrite(STDERR, "Remove stale results-*.json files or re-run the matrix with the same BENCH_COUNT, BENCH_SEED and repeats.\n");
    exit(1);
}

$out = rtrim($dir, '/') . '/COMPARISON.md';
ComparisonFormatter::build($runs, $out);

printf("Compared %d PHP versions -> %s\n", count($runs), $out);

$splits = ComparisonFormatter::verdictSplits($runs);
if (count($splits) > 1) {
    fwrite(STDERR, "Validators disagreed on the verdict split: " . implode(', ', $splits) . " (see Correctness Cross-Check in {$out}).\n");
    exit(1);
}
