<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;
use Bench\PropertyHookedProduct;

final class PropertyHookValidator implements ValidatorInterface
{
    public function name(): string
    {
        return 'Property Hooks';
    }

    public function setUp(): void {}

    public function validate(Product $product): bool
    {
        try {
            new PropertyHookedProduct(
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
