<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;

final class PurePhpValidator implements ValidatorInterface
{
    public function name(): string
    {
        return 'Pure PHP';
    }

    public function setUp(): void {}

    public function validate(Product $product): bool
    {
        if ($product->sku === '') {
            return false;
        }
        if ($product->price <= 0.0) {
            return false;
        }
        if ($product->quantity < 0) {
            return false;
        }

        return true;
    }
}
