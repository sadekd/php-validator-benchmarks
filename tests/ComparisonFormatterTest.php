<?php

declare(strict_types=1);

namespace Bench\Tests;

use Bench\ComparisonFormatter;
use PHPUnit\Framework\TestCase;

final class ComparisonFormatterTest extends TestCase
{
    /** @param array<string, array{0: float, 1: float}>|null $rows name => [vps_best, spread_pct] */
    private static function fixtureRun(string $series, array $rows, ?string $library = null, int $valid = 8, int $invalid = 2): array
    {
        $results = [];
        foreach ($rows as $name => [$vps, $spread]) {
            $results[] = ['name' => $name, 'vps_best' => $vps, 'spread_pct' => $spread, 'library' => $library, 'valid' => $valid, 'invalid' => $invalid];
        }

        return ['php_series' => $series, 'php_version' => $series . '.0', 'items' => 10, 'repeats' => 3, 'seed' => 1, 'packages' => [], 'results' => $results];
    }

    private function render(array $runs): string
    {
        $path = tempnam(sys_get_temp_dir(), 'cmp');
        ComparisonFormatter::build($runs, $path);
        $md = (string) file_get_contents($path);
        unlink($path);

        return $md;
    }

    private function rowOf(string $md, string $name, int $occurrence): string
    {
        preg_match_all('/^\| \*\*' . preg_quote($name, '/') . '\*\*.*$/m', $md, $m);

        return $m[0][$occurrence];
    }

    public function testDeltaInsideSpreadIsMarkedAndAboveIsNot(): void
    {
        $md = $this->render([
            self::fixtureRun('8.2', ['small' => [1000.0, 5.0], 'big' => [1000.0, 2.0], 'gone' => [1000.0, 1.0]]),
            self::fixtureRun('8.3', ['small' => [1030.0, 1.0], 'big' => [1100.0, 2.0]]),
            self::fixtureRun('8.4', ['small' => [1030.0, 1.0], 'big' => [1210.0, 2.0]]),
        ]);

        // Throughput table row: first-to-last delta.
        $this->assertStringContainsString('▲ +3.0% ≈ within noise', $this->rowOf($md, 'small', 0));
        $this->assertStringContainsString('▲ +21.0%', $this->rowOf($md, 'big', 0));
        $this->assertStringNotContainsString('within noise', $this->rowOf($md, 'big', 0));

        // Step table: 8.2 → 8.3 is within noise (3 < 5); 8.3 → 8.4 for big is +10% > 2%.
        $this->assertStringContainsString('▲ +3.0% ≈ within noise', $this->rowOf($md, 'small', 1));
        $this->assertStringNotContainsString('within noise', $this->rowOf($md, 'big', 1));
    }

    public function testMissingSideStaysNotAvailable(): void
    {
        $md = $this->render([
            self::fixtureRun('8.2', ['gone' => [1000.0, 50.0]]),
            self::fixtureRun('8.3', ['other' => [1000.0, 50.0]]),
        ]);

        $row = $this->rowOf($md, 'gone', 0);
        $this->assertStringEndsWith('| n/a |', $row);
        $this->assertStringNotContainsString('within noise', $row);
    }

    public function testLibraryDriftIsMarkedAndFootnoted(): void
    {
        $md = $this->render([
            self::fixtureRun('8.2', ['lib' => [1000.0, 1.0]], 'x 1.0'),
            self::fixtureRun('8.3', ['lib' => [1000.0, 1.0]], 'x 2.0'),
        ]);

        $this->assertStringContainsString(' ⚠️', $this->rowOf($md, 'lib', 0));
        $this->assertStringContainsString('Library version changed', $md);
        $this->assertStringContainsString('PHP 8.3 → `x 2.0`', $md);
    }

    public function testSameLibraryIsNotFlagged(): void
    {
        $md = $this->render([
            self::fixtureRun('8.2', ['lib' => [1000.0, 1.0]], 'x 1.0'),
            self::fixtureRun('8.3', ['lib' => [1000.0, 1.0]], 'x 1.0'),
        ]);

        $this->assertStringNotContainsString('⚠️', $this->rowOf($md, 'lib', 0));
        $this->assertStringNotContainsString('Library version changed', $md);
    }

    public function testVerdictSplitDisagreementIsReported(): void
    {
        $md = $this->render([
            self::fixtureRun('8.2', ['v' => [1000.0, 1.0]], null, 8, 2),
            self::fixtureRun('8.3', ['v' => [1000.0, 1.0]], null, 7, 3),
        ]);

        $this->assertStringContainsString('Validators disagreed on the verdict split', $md);
        $this->assertStringNotContainsString('returned the same verdict split', $md);
    }

    public function testIdenticalVerdictSplitsAgree(): void
    {
        $md = $this->render([
            self::fixtureRun('8.2', ['v' => [1000.0, 1.0]]),
            self::fixtureRun('8.3', ['v' => [1000.0, 1.0]]),
        ]);

        $this->assertStringContainsString('Every validator on every PHP version returned the same verdict split', $md);
        $this->assertStringNotContainsString('Validators disagreed', $md);
    }
}
