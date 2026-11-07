<?php

declare(strict_types=1);

namespace Bench\Tests;

use Bench\BenchmarkResult;
use PHPUnit\Framework\TestCase;

final class BenchmarkResultTest extends TestCase
{
    /** @param list<float> $times */
    private function makeResult(array $times): BenchmarkResult
    {
        return new BenchmarkResult('x', $times, 0, 100, 80, 20);
    }

    public function test_one_repeat_reports_no_measured_spread(): void
    {
        $r = $this->makeResult([1.0]);

        self::assertSame(1, $r->repeats());
        self::assertSame('n/a (1 repeat)', $r->spreadDisplay());
    }

    public function test_two_repeats_report_numeric_spread(): void
    {
        $r = $this->makeResult([1.0, 1.2]);

        self::assertEqualsWithDelta(18.18, $r->spreadPercent(), 0.01);
        self::assertSame('18.2%', $r->spreadDisplay());
    }
}
