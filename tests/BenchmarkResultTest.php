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

    public function test_median_of_odd_count_is_middle_value(): void
    {
        self::assertEqualsWithDelta(2.0, $this->makeResult([3.0, 1.0, 2.0])->timeSeconds(), 1e-9);
    }

    public function test_median_of_even_count_is_mean_of_middle_two(): void
    {
        self::assertEqualsWithDelta(2.5, $this->makeResult([1.0, 4.0, 2.0, 3.0])->timeSeconds(), 1e-9);
    }

    public function test_single_repeat_median_min_and_max_agree(): void
    {
        $r = $this->makeResult([1.5]);

        self::assertEqualsWithDelta(1.5, $r->timeSeconds(), 1e-9);
        self::assertEqualsWithDelta(1.5, $r->minTimeSeconds(), 1e-9);
        self::assertEqualsWithDelta(1.5, $r->maxTimeSeconds(), 1e-9);
    }

    public function test_min_and_max_of_unsorted_input_leave_times_unchanged(): void
    {
        $times = [3.0, 1.0, 4.0, 2.0];
        $r = $this->makeResult($times);

        self::assertEqualsWithDelta(1.0, $r->minTimeSeconds(), 1e-9);
        self::assertEqualsWithDelta(4.0, $r->maxTimeSeconds(), 1e-9);
        $r->timeSeconds();
        self::assertSame($times, $r->times);
    }

    public function test_throughput_uses_median_and_best_uses_fastest_repeat(): void
    {
        $r = $this->makeResult([2.0, 4.0]);

        self::assertEqualsWithDelta(100 / 3.0, $r->validationsPerSecond(), 1e-9);
        self::assertEqualsWithDelta(50.0, $r->bestValidationsPerSecond(), 1e-9);
    }
}
