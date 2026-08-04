<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;
use Respect\Validation\ValidatorBuilder;

/**
 * Respect Validation 3.x — only installable on PHP >= 8.5. Older PHP versions in the
 * matrix get {@see RespectV2Validator} instead; both report the same validator name so
 * the cross-version comparison lines them up.
 */
final class RespectV3Validator implements ValidatorInterface
{
    private ValidatorBuilder $skuRule;
    private ValidatorBuilder $priceRule;
    private ValidatorBuilder $quantityRule;
    private ValidatorBuilder $activeRule;

    public function name(): string
    {
        return 'Respect Validation';
    }

    public function setUp(): void
    {
        // Respect 3.x: rules are built through ValidatorBuilder (aliased as `v`).
        // NotEmpty was removed, `min()` now takes a Validator — use not(blank()) / greaterThanOrEqual().
        $this->skuRule = ValidatorBuilder::stringType()->not(ValidatorBuilder::blank());
        $this->priceRule = ValidatorBuilder::floatType()->positive();
        $this->quantityRule = ValidatorBuilder::intType()->greaterThanOrEqual(0);
        $this->activeRule = ValidatorBuilder::boolType();
    }

    public function validate(Product $product): bool
    {
        return $this->skuRule->isValid($product->sku)
            && $this->priceRule->isValid($product->price)
            && $this->quantityRule->isValid($product->quantity)
            && $this->activeRule->isValid($product->active);
    }
}
