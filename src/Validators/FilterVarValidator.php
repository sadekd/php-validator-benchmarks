<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;

final class FilterVarValidator implements ValidatorInterface
{
    public function name(): string
    {
        return 'filter_var()';
    }

    public function setUp(): void {}

    public function validate(Product $product): bool
    {
        if ($product->sku === '') {
            return false;
        }

        if (filter_var($product->price, FILTER_VALIDATE_FLOAT) === false || $product->price <= 0.0) {
            return false;
        }

        if (filter_var($product->quantity, FILTER_VALIDATE_INT) === false || $product->quantity < 0) {
            return false;
        }

        return true;
    }
}
