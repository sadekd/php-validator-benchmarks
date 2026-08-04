<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;
use Respect\Validation\Validator as v;

/**
 * Respect Validation 2.x — what PHP 8.2 to 8.4 resolve to, since 3.x requires PHP >= 8.5.
 *
 * Same rules as {@see RespectV3Validator}, expressed in the 2.x API: `Validator` is a
 * concrete class with static factories, `notEmpty()` still exists, `min()` is inclusive,
 * and the check method is `validate()` rather than `isValid()`.
 */
final class RespectV2Validator implements ValidatorInterface
{
    private v $skuRule;
    private v $priceRule;
    private v $quantityRule;
    private v $activeRule;

    public function name(): string
    {
        return 'Respect Validation';
    }

    public function setUp(): void
    {
        $this->skuRule = v::stringType()->notEmpty();
        $this->priceRule = v::floatType()->positive();
        $this->quantityRule = v::intType()->min(0);
        $this->activeRule = v::boolType();
    }

    public function validate(Product $product): bool
    {
        return $this->skuRule->validate($product->sku)
            && $this->priceRule->validate($product->price)
            && $this->quantityRule->validate($product->quantity)
            && $this->activeRule->validate($product->active);
    }
}
