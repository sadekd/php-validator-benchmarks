<?php

declare(strict_types=1);

namespace Bench\Tests;

use Bench\BenchmarkResult;
use Bench\ResultFormatter;
use PHPUnit\Framework\TestCase;

final class ResultFormatterTest extends TestCase
{
    /** @var list<string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->tempFiles = [];
    }

    private function tempPath(string $suffix): string
    {
        $path = sys_get_temp_dir() . '/' . uniqid('resultformatter_', true) . $suffix;
        $this->tempFiles[] = $path;

        return $path;
    }

    public function test_export_json_has_the_contract_keys_and_values(): void
    {
        $result = new BenchmarkResult('Pure PHP', [2.0, 4.0], 2048, 100, 80, 20, 'some/lib', 3);
        $path = $this->tempPath('.json');

        ResultFormatter::exportJson([$result], $path, 20260804, 2);

        $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        foreach (['php_version', 'php_series', 'date', 'items', 'repeats', 'seed', 'packages', 'results'] as $key) {
            self::assertArrayHasKey($key, $data);
        }
        self::assertSame(100, $data['items']);
        self::assertSame(2, $data['repeats']);
        self::assertSame(20260804, $data['seed']);
        self::assertCount(1, $data['results']);

        $row = $data['results'][0];
        $expectedKeys = [
            'name', 'library', 'median_time', 'min_time', 'max_time', 'spread_pct',
            'passes_per_sample', 'times', 'vps_median', 'vps_best', 'memory_bytes', 'valid', 'invalid',
        ];
        $actualKeys = array_keys($row);
        sort($expectedKeys);
        sort($actualKeys);
        self::assertSame($expectedKeys, $actualKeys);

        self::assertSame('Pure PHP', $row['name']);
        self::assertSame('some/lib', $row['library']);
        self::assertEqualsWithDelta(3.0, $row['median_time'], 1e-9);
        self::assertEqualsWithDelta(2.0, $row['min_time'], 1e-9);
        self::assertEqualsWithDelta(4.0, $row['max_time'], 1e-9);
        self::assertEqualsWithDelta(66.67, $row['spread_pct'], 1e-9);
        self::assertSame(3, $row['passes_per_sample']);
        self::assertEqualsWithDelta(2.0, $row['times'][0], 1e-9);
        self::assertEqualsWithDelta(4.0, $row['times'][1], 1e-9);
        self::assertEqualsWithDelta(33.3, $row['vps_median'], 1e-9);
        self::assertEqualsWithDelta(50.0, $row['vps_best'], 1e-9);
        self::assertSame(2048, $row['memory_bytes']);
        self::assertSame(80, $row['valid']);
        self::assertSame(20, $row['invalid']);
    }

    public function test_tier_boundaries_are_inclusive_at_one_million_and_one_hundred_thousand(): void
    {
        // One 1.0s repeat, so validations/s equals totalItems exactly.
        $results = [
            new BenchmarkResult('Tier One Approach', [1.0], 0, 1_000_000, 800_000, 200_000),
            new BenchmarkResult('Tier Two Approach', [1.0], 0, 100_000, 80_000, 20_000),
            new BenchmarkResult('Tier Three Approach', [1.0], 0, 99_999, 79_999, 20_000),
        ];
        $path = $this->tempPath('.md');

        ResultFormatter::exportMarkdown($results, $path);

        $md = (string) file_get_contents($path);
        $start = strpos($md, '## 🔬 Tiers Analysis');
        self::assertNotFalse($start);
        $end = strpos($md, "\n---\n", $start);
        self::assertNotFalse($end);
        $section = substr($md, $start, $end - $start);

        $parts = preg_split('/^### /m', $section);
        self::assertIsArray($parts);
        array_shift($parts);
        self::assertCount(3, $parts);

        $expected = [
            'Tier 1' => 'Tier One Approach',
            'Tier 2' => 'Tier Two Approach',
            'Tier 3' => 'Tier Three Approach',
        ];
        $i = 0;
        foreach ($expected as $tier => $name) {
            self::assertStringContainsString($tier, strtok($parts[$i], "\n"));
            self::assertStringContainsString("**{$name}**", $parts[$i]);
            foreach ($expected as $other) {
                if ($other !== $name) {
                    self::assertStringNotContainsString("**{$other}**", $parts[$i]);
                }
            }
            $i++;
        }
    }
}
