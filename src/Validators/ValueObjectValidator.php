<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;
use Bench\ProductValueObject;

final class ValueObjectValidator implements ValidatorInterface
{
    public function name(): string
    {
        return 'Value Objects';
    }

    public function setUp(): void {}

    public function validate(Product $product): bool
    {
        try {
            new ProductValueObject(
                $product->sku,
                $product->price,
                $product->quantity,
                $product->active,
            );
            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
