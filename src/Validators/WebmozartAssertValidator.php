<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

final class WebmozartAssertValidator implements ValidatorInterface
{
    public function name(): string
    {
        return 'webmozart/assert';
    }

    public function setUp(): void {}

    public function validate(Product $product): bool
    {
        try {
            Assert::notEmpty($product->sku);
            Assert::greaterThan($product->price, 0.0);
            Assert::greaterThanEq($product->quantity, 0);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
