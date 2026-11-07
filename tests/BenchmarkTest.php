<?php

declare(strict_types=1);

namespace Bench\Tests;

use Bench\BenchmarkRunner;
use Bench\DataGenerator;
use Bench\ValidatorSet;
use Bench\Validators\PurePhpValidator;
use PHPUnit\Framework\TestCase;

final class BenchmarkTest extends TestCase
{
    private const ITEMS = 2_000;

    public function test_generator_is_deterministic_for_a_seed(): void
    {
        self::assertEquals(DataGenerator::generate(self::ITEMS, 42), DataGenerator::generate(self::ITEMS, 42));
    }

    public function test_generator_output_depends_on_the_seed(): void
    {
        self::assertNotEquals(DataGenerator::generate(self::ITEMS, 1), DataGenerator::generate(self::ITEMS, 2));
    }

    public function test_runner_counts_valid_and_invalid_items(): void
    {
        $products = DataGenerator::generate(self::ITEMS);
        $expectedValid = count(array_filter($products, static fn ($p) => $p->sku !== '' && $p->price > 0 && $p->quantity >= 0));

        $result = $this->quickRunner()->run(new PurePhpValidator(), $products);

        self::assertSame($expectedValid, $result->validCount);
        self::assertSame(self::ITEMS - $expectedValid, $result->invalidCount);
        self::assertSame(self::ITEMS, $result->totalItems);
    }

    /** The README's correctness check: every validator reports the same valid/invalid split. */
    public function test_all_validators_agree_on_the_seeded_dataset(): void
    {
        $products = DataGenerator::generate(self::ITEMS);
        $runner = $this->quickRunner();

        $splits = [];
        foreach (ValidatorSet::forThisRuntime() as $validator) {
            $result = $runner->run($validator, $products);
            $splits[$validator->name()] = [$result->validCount, $result->invalidCount];
        }

        self::assertCount(1, array_unique(array_map('serialize', $splits)), print_r($splits, true));
    }

    private function quickRunner(): BenchmarkRunner
    {
        return new BenchmarkRunner(repeats: 1, warmupItems: 10, minSampleSeconds: 0.0);
    }
}
