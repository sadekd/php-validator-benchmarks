<?php

declare(strict_types=1);

namespace Bench;

use Bench\Validators\ValidatorInterface;

final class BenchmarkRunner
{
    /** @param list<Product> $products */
    public function run(ValidatorInterface $validator, array $products): BenchmarkResult
    {
        gc_collect_cycles();
        gc_mem_caches();

        $validator->setUp();

        memory_reset_peak_usage();

        $validCount = 0;
        $invalidCount = 0;

        $start = hrtime(true);

        foreach ($products as $product) {
            if ($validator->validate($product)) {
                $validCount++;
            } else {
                $invalidCount++;
            }
        }

        $elapsed = (hrtime(true) - $start) / 1_000_000_000;
        $peakMem = memory_get_peak_usage(true);

        return new BenchmarkResult(
            name: $validator->name(),
            timeSeconds: $elapsed,
            memoryPeakBytes: $peakMem,
            totalItems: count($products),
            validCount: $validCount,
            invalidCount: $invalidCount,
        );
    }
}
