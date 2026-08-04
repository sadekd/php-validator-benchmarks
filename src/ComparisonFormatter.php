<?php

declare(strict_types=1);

namespace Bench;

/**
 * Builds COMPARISON.md from the per-version results-<series>.json files.
 *
 * Two things move between columns, not one: the PHP runtime and — because dependencies are
 * resolved per PHP version rather than locked — the library behind some validators. Rows where
 * the library changed are marked so a throughput jump is not read as a runtime win.
 */
final class ComparisonFormatter
{
    /**
     * @param  list<array<string, mixed>> $runs decoded results-<series>.json payloads
     */
    public static function build(array $runs, string $path): void
    {
        usort($runs, static fn(array $a, array $b): int => version_compare((string) $a['php_series'], (string) $b['php_series']));

        $seriesList = array_map(static fn(array $r): string => (string) $r['php_series'], $runs);
        $baseSeries = $seriesList[0];

        // validator name => series => row
        $byName = [];
        foreach ($runs as $run) {
            foreach ($run['results'] as $row) {
                $byName[$row['name']][$run['php_series']] = $row;
            }
        }

        // Order rows the way the per-version reports do: fastest first on the newest PHP.
        $newest = end($seriesList);
        uasort($byName, static function (array $a, array $b) use ($newest): int {
            $rank = static function (array $bySeries) use ($newest): float {
                if (isset($bySeries[$newest])) {
                    return (float) $bySeries[$newest]['vps_best'];
                }

                return max(array_map(static fn(array $row): float => (float) $row['vps_best'], $bySeries));
            };

            return $rank($b) <=> $rank($a);
        });

        $items = (int) $runs[0]['items'];
        $repeats = (int) $runs[0]['repeats'];
        $seed = (int) $runs[0]['seed'];

        $md = "# PHP Version Comparison — Validation Throughput\n\n";
        $md .= sprintf(
            "%s items, seed %d, %d timed repeat(s) per validator after an untimed 1,000-item warmup. "
            . "Fast validators repeat the pass inside each sample until it clears 0.5s, so a 20ms loop is not "
            . "timed against scheduler jitter. Same machine, same dataset, PHP %s as the baseline column.\n\n"
            . "**Every number below is the fastest of the %d repeats**, normalised to one pass. Machine "
            . "interference only ever makes a sample slower, so the best sample is the closest available estimate "
            . "of uncontended throughput — the per-version `RESULTS-*.md` files report medians instead.\n\n",
            number_format($items),
            $seed,
            $repeats,
            $baseSeries,
            $repeats,
        );
        $md .= "Generated " . date('Y-m-d') . " from `results-*.json`.\n\n";
        $md .= "---\n\n";

        // Environment matrix
        $md .= "## What Each Column Ran\n\n";
        $md .= "| | " . implode(' | ', array_map(static fn(string $s): string => "PHP {$s}", $seriesList)) . " |\n";
        $md .= "|---" . str_repeat('|---:', count($runs)) . "|\n";

        $md .= "| **PHP** | " . implode(' | ', array_map(static fn(array $r): string => (string) $r['php_version'], $runs)) . " |\n";

        $packageNames = [];
        foreach ($runs as $run) {
            foreach (array_keys($run['packages']) as $package) {
                $packageNames[$package] = true;
            }
        }
        foreach (array_keys($packageNames) as $package) {
            $cells = [];
            foreach ($runs as $run) {
                $cells[] = $run['packages'][$package] ?? '—';
            }
            $md .= "| `{$package}` | " . implode(' | ', $cells) . " |\n";
        }
        $md .= "\n";

        // Throughput matrix
        $md .= "## Throughput (validations/s, best of {$repeats})\n\n";
        $md .= "| Validator | " . implode(' | ', array_map(static fn(string $s): string => "PHP {$s}", $seriesList));
        $md .= " | " . $baseSeries . " → " . end($seriesList) . " |\n";
        $md .= "|---" . str_repeat('|---:', count($runs) + 1) . "|\n";

        $footnotes = [];
        foreach ($byName as $name => $bySeries) {
            $cells = [];
            foreach ($seriesList as $series) {
                $cells[] = isset($bySeries[$series])
                    ? number_format((int) $bySeries[$series]['vps_best'])
                    : 'n/a';
            }

            $first = $bySeries[$baseSeries] ?? null;
            $last = $bySeries[end($seriesList)] ?? null;
            $change = $first !== null && $last !== null
                ? self::changeLabel((float) $first['vps_best'], (float) $last['vps_best'])
                : 'n/a';

            $marker = '';
            if (self::libraryChanged($bySeries)) {
                $marker = ' ⚠️';
                $footnotes[$name] = self::libraryTrail($bySeries, $seriesList);
            }

            $md .= "| **{$name}**{$marker} | " . implode(' | ', $cells) . " | {$change} |\n";
        }
        $md .= "\n";
        $md .= "Positive = faster on the newer PHP. `n/a` = the approach does not exist on that version.\n\n";

        if ($footnotes !== []) {
            $md .= "> ⚠️ **Library version changed across these columns** — the delta is runtime *plus* library, not runtime alone:\n>\n";
            foreach ($footnotes as $name => $trail) {
                $md .= "> - **{$name}**: {$trail}\n";
            }
            $md .= "\n";
        }
        $md .= "---\n\n";

        // Step-by-step deltas
        if (count($seriesList) > 2) {
            $md .= "## Version-to-Version Change (%)\n\n";
            $steps = [];
            for ($i = 1; $i < count($seriesList); $i++) {
                $steps[] = [$seriesList[$i - 1], $seriesList[$i]];
            }

            $md .= "| Validator | " . implode(' | ', array_map(static fn(array $s): string => "{$s[0]} → {$s[1]}", $steps)) . " |\n";
            $md .= "|---" . str_repeat('|---:', count($steps)) . "|\n";

            foreach ($byName as $name => $bySeries) {
                $cells = [];
                foreach ($steps as [$from, $to]) {
                    $cells[] = isset($bySeries[$from], $bySeries[$to])
                        ? self::changeLabel((float) $bySeries[$from]['vps_best'], (float) $bySeries[$to]['vps_best'])
                        : 'n/a';
                }
                $md .= "| **{$name}** | " . implode(' | ', $cells) . " |\n";
            }
            $md .= "\n---\n\n";
        }

        // Noise floor
        $md .= "## Noise Floor\n\n";
        $md .= "Spread between the fastest and slowest repeat, as a percentage of the median. ";
        $md .= "The tables above use the fastest repeat, which sidesteps most of this, but a row with a large ";
        $md .= "spread was contended while it ran — treat small deltas on those rows with suspicion.\n\n";
        $md .= "| Validator | " . implode(' | ', array_map(static fn(string $s): string => "PHP {$s}", $seriesList)) . " |\n";
        $md .= "|---" . str_repeat('|---:', count($runs)) . "|\n";
        foreach ($byName as $name => $bySeries) {
            $cells = [];
            foreach ($seriesList as $series) {
                $cells[] = isset($bySeries[$series])
                    ? sprintf('%.1f%%', (float) $bySeries[$series]['spread_pct'])
                    : 'n/a';
            }
            $md .= "| **{$name}** | " . implode(' | ', $cells) . " |\n";
        }
        $md .= "\n";

        // Correctness cross-check
        $md .= "## Correctness Cross-Check\n\n";
        $splits = [];
        foreach ($runs as $run) {
            foreach ($run['results'] as $row) {
                $splits[sprintf('%s valid / %s invalid', number_format((int) $row['valid']), number_format((int) $row['invalid']))] = true;
            }
        }
        $md .= count($splits) === 1
            ? "Every validator on every PHP version returned the same verdict split: **"
                . array_key_first($splits) . "** — the dataset is seeded, so this is comparable across columns.\n"
            : "⚠️ Validators disagreed on the verdict split across versions: `"
                . implode('`, `', array_keys($splits)) . "`.\n";

        file_put_contents($path, $md);
    }

    private static function changeLabel(float $from, float $to): string
    {
        if ($from <= 0.0) {
            return 'n/a';
        }

        $pct = (($to - $from) / $from) * 100.0;
        $arrow = $pct >= 0 ? '▲' : '▼';

        return sprintf('%s %+.1f%%', $arrow, $pct);
    }

    /** @param array<string, array<string, mixed>> $bySeries */
    private static function libraryChanged(array $bySeries): bool
    {
        $libraries = [];
        foreach ($bySeries as $row) {
            if ($row['library'] !== null) {
                $libraries[(string) $row['library']] = true;
            }
        }

        return count($libraries) > 1;
    }

    /**
     * @param array<string, array<string, mixed>> $bySeries
     * @param list<string> $seriesList
     */
    private static function libraryTrail(array $bySeries, array $seriesList): string
    {
        $parts = [];
        $previous = null;
        foreach ($seriesList as $series) {
            $library = $bySeries[$series]['library'] ?? null;
            if ($library !== null && $library !== $previous) {
                $parts[] = "PHP {$series} → `{$library}`";
                $previous = $library;
            }
        }

        return implode(', ', $parts);
    }
}
