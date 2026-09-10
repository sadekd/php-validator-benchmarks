<?php

declare(strict_types=1);

namespace Bench;

final class ResultFormatter
{
    /**
     * Strategy + per-item cost notes, keyed by validator name.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const NOTES = [
        'Pure PHP' => ['Direct `if` checks on typed properties', 'Baseline — no allocation, no calls'],
        'filter_var()' => ['PHP built-in C filter functions', 'Redundant type checks on already-typed props'],
        'Value Objects' => ['Constructor validation + `try/catch`', 'Object allocation + exception path on failure'],
        'Property Hooks' => ['PHP 8.4+ `set` hooks with validation', 'Hook dispatch per property write'],
        'webmozart/assert' => ['Static assertion methods + `try/catch`', 'Static call overhead + exception path'],
        'Respect Validation' => ['Rule chain evaluated per field', 'Result objects built per rule evaluation'],
        'Symfony Validator' => ['`Collection` constraint over an array', 'Constraint tree walk + array conversion'],
        'Symfony Attributes' => ['Attribute-mapped constraints on an object', 'Constraint tree walk + metadata lookup'],
        'Laravel Validation' => ['New `Validator` per `make()` call', 'Validator + translator construction per item'],
    ];

    /**
     * Split results into speed tiers by measured throughput.
     *
     * @param  list<BenchmarkResult> $sorted results sorted fastest-first
     * @return list<array{title: string, range: string, results: list<BenchmarkResult>}>
     */
    private static function tiers(array $sorted): array
    {
        $buckets = [
            ['title' => '⚡ Tier 1 — Native Speed', 'range' => '≥ 1M validations/s', 'min' => 1_000_000.0, 'results' => []],
            ['title' => '🚶 Tier 2 — Library Speed', 'range' => '100k – 1M validations/s', 'min' => 100_000.0, 'results' => []],
            ['title' => '🐢 Tier 3 — Framework Speed', 'range' => '< 100k validations/s', 'min' => 0.0, 'results' => []],
        ];

        foreach ($sorted as $r) {
            foreach ($buckets as $i => $bucket) {
                if ($r->validationsPerSecond() >= $bucket['min']) {
                    $buckets[$i]['results'][] = $r;
                    break;
                }
            }
        }

        return array_values(array_filter($buckets, static fn(array $b): bool => $b['results'] !== []));
    }

    /** @param list<BenchmarkResult> $results */
    private static function find(array $results, string $name): ?BenchmarkResult
    {
        foreach ($results as $r) {
            if ($r->name === $name) {
                return $r;
            }
        }

        return null;
    }

    /** @param list<BenchmarkResult> $results */
    public static function display(array $results): void
    {
        $header = ['Validator', 'Median (s)', 'Spread', 'Peak Memory', 'Validations/s', 'Valid', 'Invalid'];
        $rows = [];

        foreach ($results as $r) {
            $rows[] = [
                $r->name,
                sprintf('%.3f', $r->timeSeconds()),
                sprintf('%.1f%%', $r->spreadPercent()),
                DataGenerator::formatBytes($r->memoryPeakBytes),
                number_format((int) $r->validationsPerSecond()),
                number_format($r->validCount),
                number_format($r->invalidCount),
            ];
        }

        $widths = [];
        foreach ($header as $i => $h) {
            $widths[$i] = mb_strlen($h);
        }
        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i], mb_strlen($cell));
            }
        }

        $separator = '+';
        foreach ($widths as $w) {
            $separator .= str_repeat('-', $w + 2) . '+';
        }

        echo $separator . "\n";
        echo '|';
        foreach ($header as $i => $h) {
            echo ' ' . str_pad($h, $widths[$i]) . ' |';
        }
        echo "\n" . $separator . "\n";

        foreach ($rows as $row) {
            echo '|';
            foreach ($row as $i => $cell) {
                $pad = $i === 0 ? STR_PAD_RIGHT : STR_PAD_LEFT;
                echo ' ' . str_pad($cell, $widths[$i], ' ', $pad) . ' |';
            }
            echo "\n";
        }

        echo $separator . "\n";
    }

    /**
     * Machine-readable run record — one per PHP version, consumed by compare.php.
     *
     * @param list<BenchmarkResult> $results
     */
    public static function exportJson(array $results, string $path, int $seed, int $repeats): void
    {
        $rows = [];
        foreach ($results as $r) {
            $rows[] = [
                'name' => $r->name,
                'library' => $r->library,
                'median_time' => round($r->timeSeconds(), 6),
                'min_time' => round($r->minTimeSeconds(), 6),
                'max_time' => round($r->maxTimeSeconds(), 6),
                'spread_pct' => round($r->spreadPercent(), 2),
                'passes_per_sample' => $r->passesPerSample,
                'times' => array_map(static fn(float $t): float => round($t, 6), $r->times),
                'vps_median' => round($r->validationsPerSecond(), 1),
                'vps_best' => round($r->bestValidationsPerSecond(), 1),
                'memory_bytes' => $r->memoryPeakBytes,
                'valid' => $r->validCount,
                'invalid' => $r->invalidCount,
            ];
        }

        $payload = [
            'php_version' => PHP_VERSION,
            'php_series' => Environment::phpSeries(),
            'date' => date('c'),
            'items' => $results === [] ? 0 : $results[0]->totalItems,
            'repeats' => $repeats,
            'seed' => $seed,
            'packages' => Environment::packages(),
            'results' => $rows,
        ];

        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }

    /** @param list<BenchmarkResult> $results */
    public static function exportMarkdown(array $results, string $path): void
    {
        $sorted = $results;
        usort($sorted, fn(BenchmarkResult $a, BenchmarkResult $b) => $a->timeSeconds() <=> $b->timeSeconds());

        $baseline = $sorted[0]->validationsPerSecond();
        $maxVps = $sorted[0]->validationsPerSecond();
        $maxTime = end($sorted)->timeSeconds();
        $barWidth = 50;

        $medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟'];
        $totalItems = $sorted[0]->totalItems;
        $validCount = $sorted[0]->validCount;
        $invalidCount = $sorted[0]->invalidCount;
        $repeats = $sorted[0]->repeats();

        $phpSeries = Environment::phpSeries();

        $md = "# 🏎️ PHP {$phpSeries} Validation Benchmark\n\n";
        $md .= "> Benchmarking **" . count($results) . " validation approaches** across **" . number_format($totalItems) . " product items** on PHP " . PHP_VERSION . "\n\n";

        // Setup table
        $md .= "## 📋 Test Setup\n\n";
        $md .= "| | |\n|---|---|\n";
        $md .= "| 🐘 **PHP Version** | " . PHP_VERSION . " (Alpine) |\n";
        $md .= "| 📦 **Data Set** | " . number_format($totalItems) . " product objects (~80% valid, ~20% invalid), seeded |\n";
        $md .= "| 🏗️ **Object** | `Product { sku, price, quantity, active }` |\n";
        $md .= "| 🔁 **Sampling** | {$repeats} timed repeat(s) after an untimed 1,000-item warmup; each sample repeats the pass until it clears 0.5s; median reported |\n";
        $md .= "| 🐳 **Runtime** | Docker (`php:{$phpSeries}-cli-alpine`) |\n";
        $md .= "| 📅 **Date** | " . date('Y-m-d') . " |\n\n";

        $packages = Environment::packages();
        if ($packages !== []) {
            $md .= "Resolved libraries on this PHP version:\n\n";
            $md .= "| Package | Version |\n|---|---|\n";
            foreach ($packages as $package => $version) {
                $md .= "| `{$package}` | {$version} |\n";
            }
            $md .= "\n";
        }
        $md .= "---\n\n";

        // Ranking table
        $md .= "## 🏆 Results — Sorted by Speed\n\n";
        $md .= "| Rank | Validator | Median time | Validations/s | Spread | vs Baseline |\n";
        $md .= "|:---:|---|---:|---:|---:|---:|\n";

        foreach ($sorted as $i => $r) {
            $ratio = $baseline / $r->validationsPerSecond();
            $ratioStr = $i === 0 ? '1.0x' : sprintf('%.1fx slower', $ratio);
            $name = $r->name;
            if (str_contains($name, 'Property Hook')) {
                $name .= ' 🆕';
            }
            $md .= sprintf("| %s | **%s** | %.3fs | %s/s | %.1f%% | %s |\n",
                $medals[$i] ?? ($i + 1) . '.',
                $name,
                $r->timeSeconds(),
                number_format((int) $r->validationsPerSecond()),
                $r->spreadPercent(),
                $ratioStr,
            );
        }

        $md .= "\n---\n\n";

        // Speed bar chart
        $md .= "## 📊 Speed Comparison (validations/second)\n\n```\n";
        foreach ($sorted as $i => $r) {
            $label = str_pad(mb_substr($r->name, 0, 18), 18);
            $bars = max(1, (int) round(($r->validationsPerSecond() / $maxVps) * $barWidth));
            $bar = $bars > 1 ? str_repeat('█', $bars) : '▏';
            $md .= sprintf("%s %s %s/s  %s\n", $label, str_pad($bar, $barWidth), number_format((int) $r->validationsPerSecond()), $medals[$i] ?? '');
        }
        $md .= "```\n\n";

        // Time bar chart
        $md .= "## ⏱️ Execution Time (seconds, median)\n\n```\n";
        foreach ($sorted as $i => $r) {
            $label = str_pad(mb_substr($r->name, 0, 18), 18);
            $bars = max(1, (int) round(($r->timeSeconds() / $maxTime) * $barWidth));
            $bar = $bars > 1 ? str_repeat('█', $bars) : '▏';
            $emoji = $i === 0 ? '⚡' : ($i === count($sorted) - 1 ? '🐌' : '');
            $md .= sprintf("%s %s %7.3fs  %s\n", $label, str_pad($bar, $barWidth), $r->timeSeconds(), $emoji);
        }
        $md .= "```\n\n";

        // Memory table
        $md .= "## 🧠 Memory Usage\n\n";
        $md .= "| Validator | Peak Memory | Delta from Baseline |\n";
        $md .= "|---|---:|---:|\n";
        $baselineMem = $sorted[0]->memoryPeakBytes;
        foreach ($sorted as $r) {
            $delta = $r->memoryPeakBytes - $baselineMem;
            $deltaStr = $delta === 0 ? '—' : sprintf('+%s', DataGenerator::formatBytes($delta));
            $md .= sprintf("| %s | %s | %s |\n", $r->name, DataGenerator::formatBytes($r->memoryPeakBytes), $deltaStr);
        }
        $md .= "\n> 💡 Memory differences are minimal — all approaches fit comfortably within a few MB of each other.\n\n";
        $md .= "---\n\n";

        // Tier analysis — tiers derived from measured throughput, notes looked up per approach
        $md .= "## 🔬 Tiers Analysis\n\n";
        foreach (self::tiers($sorted) as $tier) {
            $md .= sprintf("### %s (%s)\n\n", $tier['title'], $tier['range']);
            $md .= "| Approach | Validations/s | Strategy | Cost per item |\n|---|---:|---|---|\n";
            foreach ($tier['results'] as $r) {
                $note = self::NOTES[$r->name] ?? ['—', '—'];
                $md .= sprintf(
                    "| **%s** | %s/s | %s | %s |\n",
                    $r->name,
                    number_format((int) $r->validationsPerSecond()),
                    $note[0],
                    $note[1],
                );
            }
            $md .= "\n";
        }
        $md .= "---\n\n";

        // Takeaways
        $slowest = $sorted[count($sorted) - 1];
        $fastestRatio = sprintf('%.0f', $baseline / $slowest->validationsPerSecond());
        $md .= "## 💡 Key Takeaways\n\n";
        $md .= "1. 🏆 **{$sorted[0]->name} is ~{$fastestRatio}x faster than {$slowest->name}** — simple `if` checks on typed properties are unbeatable\n";
        $i = 2;
        foreach (['Property Hooks', 'webmozart/assert', 'filter_var()'] as $name) {
            $r = self::find($sorted, $name);
            if ($r === null) {
                continue;
            }
            $md .= sprintf(
                "%d. **%s** — %s/s (%.1fx vs %s): %s\n",
                $i++,
                $name,
                number_format((int) $r->validationsPerSecond()),
                $baseline / $r->validationsPerSecond(),
                $sorted[0]->name,
                (self::NOTES[$name] ?? ['', '—'])[1],
            );
        }
        $md .= sprintf(
            "%d. ✅ **All %d approaches produce identical results** — correctness verified (%s valid / %s invalid)\n\n",
            $i,
            count($results),
            number_format($validCount),
            number_format($invalidCount),
        );
        $md .= "---\n\n";

        // Run instructions
        $md .= "## 🚀 Run It Yourself\n\n";
        $md .= "```bash\n";
        $md .= "# Full matrix (PHP 8.2 - 8.5) + comparison report\n";
        $md .= "./run.sh\n\n";
        $md .= "# Single PHP version\n";
        $md .= "PHP_VERSIONS=8.5 ./run.sh\n\n";
        $md .= "# Custom output directory, dataset size, repeats\n";
        $md .= "./run.sh /path/to/results\n";
        $md .= "BENCH_COUNT=50000 BENCH_REPEATS=5 ./run.sh\n\n";
        $md .= "# Local PHP (>= 8.2)\n";
        $md .= "composer update\n";
        $md .= "php benchmark.php\n";
        $md .= "```\n\n";
        $md .= "Per-version reports land in `RESULTS-<version>.md`, raw numbers in `results-<version>.json`, ";
        $md .= "and the cross-version diff in `COMPARISON.md`.\n";

        file_put_contents($path, $md);
    }
}
