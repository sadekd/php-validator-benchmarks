<?php

declare(strict_types=1);

namespace Bench;

readonly class BenchmarkResult
{
    /** @param list<float> $times one entry per timed repeat, in seconds */
    public function __construct(
        public string $name,
        public array $times,
        public int $memoryPeakBytes,
        public int $totalItems,
        public int $validCount,
        public int $invalidCount,
        public ?string $library = null,
        public int $passesPerSample = 1,
    ) {}

    public function repeats(): int
    {
        return count($this->times);
    }

    /** Median wall time — less skewed by a single slow repeat than the mean. */
    public function timeSeconds(): float
    {
        $sorted = $this->times;
        sort($sorted);
        $mid = intdiv(count($sorted), 2);

        return count($sorted) % 2 === 1
            ? $sorted[$mid]
            : ($sorted[$mid - 1] + $sorted[$mid]) / 2.0;
    }

    public function minTimeSeconds(): float
    {
        return min($this->times);
    }

    public function maxTimeSeconds(): float
    {
        return max($this->times);
    }

    /** Spread across repeats as a percentage of the median — the noise floor for this row. */
    public function spreadPercent(): float
    {
        $median = $this->timeSeconds();

        return $median > 0.0
            ? (($this->maxTimeSeconds() - $this->minTimeSeconds()) / $median) * 100.0
            : 0.0;
    }

    /** Spread for display: a single repeat measured no noise, so say so instead of printing 0.0%. */
    public function spreadDisplay(): string
    {
        return self::formatSpread($this->spreadPercent(), $this->repeats());
    }

    public static function formatSpread(float $spreadPercent, int $repeats): string
    {
        return $repeats < 2 ? 'n/a (1 repeat)' : sprintf('%.1f%%', $spreadPercent);
    }

    public function validationsPerSecond(): float
    {
        return $this->totalItems / $this->timeSeconds();
    }

    /** Throughput of the fastest repeat — the closest thing to an uncontended measurement. */
    public function bestValidationsPerSecond(): float
    {
        return $this->totalItems / $this->minTimeSeconds();
    }
}
