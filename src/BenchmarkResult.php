<?php

declare(strict_types=1);

namespace Bench;

readonly class BenchmarkResult
{
    public function __construct(
        public string $name,
        public float $timeSeconds,
        public int $memoryPeakBytes,
        public int $totalItems,
        public int $validCount,
        public int $invalidCount,
    ) {}

    public function validationsPerSecond(): float
    {
        return $this->totalItems / $this->timeSeconds;
    }
}
