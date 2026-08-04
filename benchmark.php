#!/usr/bin/env php
<?php
declare(strict_types=1);

ini_set("memory_limit", "1G");

require __DIR__ . "/vendor/autoload.php";

use Bench\BenchmarkRunner;
use Bench\DataGenerator;
use Bench\ResultFormatter;
use Bench\Validators\FilterVarValidator;
use Bench\Validators\LaravelValidator;
use Bench\Validators\PropertyHookValidator;
use Bench\Validators\PurePhpValidator;
use Bench\Validators\RespectValidator;
use Bench\Validators\SymfonyAttributeValidator;
use Bench\Validators\SymfonyValidator;
use Bench\Validators\ValueObjectValidator;
use Bench\Validators\WebmozartAssertValidator;

$count = (int) (getenv('BENCH_COUNT') ?: 1_000_000);

echo "Generating {$count} product items...\n";
$genStart = hrtime(true);
$products = DataGenerator::generate($count);
$genTime = (hrtime(true) - $genStart) / 1_000_000_000;
printf(
    "Data generated in %.3f s (%s peak memory)\n\n",
    $genTime,
    DataGenerator::formatBytes(memory_get_peak_usage(true)),
);

$runner = new BenchmarkRunner();
$validators = [
    new PurePhpValidator(),
    new FilterVarValidator(),
    new ValueObjectValidator(),
    new PropertyHookValidator(),
    new WebmozartAssertValidator(),
    new SymfonyValidator(),
    new SymfonyAttributeValidator(),
    new LaravelValidator(),
    new RespectValidator(),
];

$results = [];
foreach ($validators as $validator) {
    printf("Running: %s...\n", $validator->name());
    $results[] = $runner->run($validator, $products);
}

echo "\n";
ResultFormatter::display($results);

$outputDir = getenv('RESULTS_DIR') ?: __DIR__;
ResultFormatter::exportMarkdown($results, $outputDir . '/RESULTS.md');
printf("\nResults exported to %s/RESULTS.md\n", $outputDir);

