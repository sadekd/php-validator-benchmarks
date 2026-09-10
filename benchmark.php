#!/usr/bin/env php
<?php
declare(strict_types=1);

ini_set("memory_limit", "1G");

require __DIR__ . "/vendor/autoload.php";

use Bench\BenchmarkRunner;
use Bench\DataGenerator;
use Bench\Environment;
use Bench\ResultFormatter;
use Bench\Validators\FilterVarValidator;
use Bench\Validators\LaravelValidator;
use Bench\Validators\PropertyHookValidator;
use Bench\Validators\PurePhpValidator;
use Bench\Validators\RespectV2Validator;
use Bench\Validators\RespectV3Validator;
use Bench\Validators\SymfonyAttributeValidator;
use Bench\Validators\SymfonyValidator;
use Bench\Validators\ValinorValidator;
use Bench\Validators\ValueObjectValidator;
use Bench\Validators\WebmozartAssertValidator;

$count = (int) (getenv('BENCH_COUNT') ?: 1_000_000);
$repeats = max(1, (int) (getenv('BENCH_REPEATS') ?: 1));
$seed = (int) (getenv('BENCH_SEED') ?: DataGenerator::DEFAULT_SEED);
$series = Environment::phpSeries();

printf("PHP %s — %d items, %d timed repeat(s), seed %d\n", PHP_VERSION, $count, $repeats, $seed);
foreach (Environment::packages() as $package => $version) {
    printf("  %-24s %s\n", $package, $version);
}
echo "\n";

echo "Generating {$count} product items...\n";
$genStart = hrtime(true);
$products = DataGenerator::generate($count, $seed);
$genTime = (hrtime(true) - $genStart) / 1_000_000_000;
printf(
    "Data generated in %.3f s (%s peak memory)\n\n",
    $genTime,
    DataGenerator::formatBytes(memory_get_peak_usage(true)),
);

$runner = new BenchmarkRunner(repeats: $repeats);

$validators = [
    new PurePhpValidator(),
    new FilterVarValidator(),
    new ValueObjectValidator(),
];

// Property hooks are PHP 8.4+ syntax — the class cannot even be parsed on 8.2/8.3.
if (Environment::supportsPropertyHooks()) {
    $validators[] = new PropertyHookValidator();
} else {
    printf("Skipping: Property Hooks (needs PHP 8.4+, running %s)\n\n", PHP_VERSION);
}

$validators[] = new WebmozartAssertValidator();
$validators[] = new SymfonyValidator();
$validators[] = new SymfonyAttributeValidator();
$validators[] = new LaravelValidator();
$validators[] = new ValinorValidator();

// respect/validation 3.x requires PHP >= 8.5; older versions resolve to 2.x, which has a
// different API for the same rules.
$validators[] = class_exists(\Respect\Validation\ValidatorBuilder::class)
    ? new RespectV3Validator()
    : new RespectV2Validator();

$results = [];
foreach ($validators as $validator) {
    printf("Running: %s...\n", $validator->name());
    $result = $runner->run($validator, $products);
    printf(
        "  median %.4fs/pass  (min %.4fs / max %.4fs, spread %.1f%%, %d pass(es) per sample)\n",
        $result->timeSeconds(),
        $result->minTimeSeconds(),
        $result->maxTimeSeconds(),
        $result->spreadPercent(),
        $result->passesPerSample,
    );
    $results[] = $result;
}

echo "\n";
ResultFormatter::display($results);

$outputDir = getenv('RESULTS_DIR') ?: __DIR__;
$markdownPath = $outputDir . "/RESULTS-{$series}.md";
$jsonPath = $outputDir . "/results-{$series}.json";

ResultFormatter::exportMarkdown($results, $markdownPath);
ResultFormatter::exportJson($results, $jsonPath, $seed, $repeats);

printf("\nResults exported to %s and %s\n", $markdownPath, $jsonPath);
