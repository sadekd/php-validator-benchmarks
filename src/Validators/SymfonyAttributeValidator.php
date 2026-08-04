<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;
use Bench\ValidatedProduct;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

final class SymfonyAttributeValidator implements ValidatorInterface
{
    private SymfonyValidatorInterface $validator;

    public function name(): string
    {
        return 'Symfony Attributes';
    }

    public function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function validate(Product $product): bool
    {
        $validated = new ValidatedProduct(
            $product->sku,
            $product->price,
            $product->quantity,
            $product->active,
        );

        return $this->validator->validate($validated)->count() === 0;
    }
}
