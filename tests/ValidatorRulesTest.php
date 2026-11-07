<?php

declare(strict_types=1);

namespace Bench\Tests;

use Bench\Product;
use Bench\ValidatorSet;
use Bench\Validators\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Every validator must enforce the same rules (README "What it measures"), or the benchmark compares unlike work. */
final class ValidatorRulesTest extends TestCase
{
    /** @return iterable<string, array{Product, bool}> */
    public static function cases(): iterable
    {
        yield 'valid product' => [new Product('SKU-1', 9.99, 3, true), true];
        yield 'inactive product is still valid' => [new Product('SKU-1', 9.99, 3, false), true];
        yield 'empty sku' => [new Product('', 9.99, 3, true), false];
        yield 'zero price' => [new Product('SKU-1', 0.0, 3, true), false];
        yield 'negative price' => [new Product('SKU-1', -0.01, 3, true), false];
        yield 'smallest positive price' => [new Product('SKU-1', 0.01, 3, true), true];
        yield 'zero quantity' => [new Product('SKU-1', 9.99, 0, true), true];
        yield 'negative quantity' => [new Product('SKU-1', 9.99, -1, true), false];
    }

    /** @return iterable<string, array{ValidatorInterface, Product, bool}> */
    public static function validatorCases(): iterable
    {
        foreach (ValidatorSet::forThisRuntime() as $validator) {
            foreach (self::cases() as $case => [$product, $expected]) {
                yield $validator->name() . ': ' . $case => [$validator, $product, $expected];
            }
        }
    }

    #[DataProvider('validatorCases')]
    public function test_validator_applies_the_shared_rules(ValidatorInterface $validator, Product $product, bool $expected): void
    {
        $validator->setUp();

        self::assertSame($expected, $validator->validate($product));
    }
}
