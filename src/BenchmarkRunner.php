<?php

declare(strict_types=1);

namespace Bench;

use Bench\Validators\ValidatorInterface;

final class BenchmarkRunner
{
    public function __construct(
        private readonly int $repeats = 1,
        private readonly int $warmupItems = 1000,
        private readonly float $minSampleSeconds = 0.5,
    ) {}

    /** @param list<Product> $products */
    public function run(ValidatorInterface $validator, array $products): BenchmarkResult
    {
        $validator->setUp();

        // Untimed warmup: Symfony and Laravel build constraint metadata lazily on the first
        // validate() call, and that one-off cost belongs to setUp, not to steady-state throughput.
        $warmup = min($this->warmupItems, count($products));
        for ($i = 0; $i < $warmup; $i++) {
            $validator->validate($products[$i]);
        }

        $passes = $this->calibratePasses($validator, $products);

        $times = [];
        $validCount = 0;
        $invalidCount = 0;
        $peakMem = 0;

        for ($repeat = 0; $repeat < $this->repeats; $repeat++) {
            gc_collect_cycles();
            gc_mem_caches();
            memory_reset_peak_usage();

            $validCount = 0;
            $invalidCount = 0;

            $start = hrtime(true);

            for ($pass = 0; $pass < $passes; $pass++) {
                $validCount = 0;
                $invalidCount = 0;

                foreach ($products as $product) {
                    if ($validator->validate($product)) {
                        $validCount++;
                    } else {
                        $invalidCount++;
                    }
                }
            }

            // Report per-pass time so throughput stays items/second regardless of pass count.
            $times[] = ((hrtime(true) - $start) / 1_000_000_000) / $passes;
            $peakMem = max($peakMem, memory_get_peak_usage(true));
        }

        return new BenchmarkResult(
            name: $validator->name(),
            times: $times,
            memoryPeakBytes: $peakMem,
            totalItems: count($products),
            validCount: $validCount,
            invalidCount: $invalidCount,
            library: Environment::libraryFor($validator->name()),
            passesPerSample: $passes,
        );
    }

    /**
     * A single pass over 200k items takes ~20ms for the fast validators, which is short enough
     * that scheduler jitter swamps the measurement (observed spreads above 100%). Time one pass,
     * then stack enough passes into each timed sample to clear minSampleSeconds.
     *
     * @param list<Product> $products
     */
    private function calibratePasses(ValidatorInterface $validator, array $products): int
    {
        $start = hrtime(true);
        foreach ($products as $product) {
            $validator->validate($product);
        }
        $onePass = (hrtime(true) - $start) / 1_000_000_000;

        if ($onePass <= 0.0 || $onePass >= $this->minSampleSeconds) {
            return 1;
        }

        return (int) max(1, min(1000, ceil($this->minSampleSeconds / $onePass)));
    }
}
