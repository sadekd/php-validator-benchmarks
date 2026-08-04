<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

final class SymfonyValidator implements ValidatorInterface
{
    private SymfonyValidatorInterface $validator;
    private Assert\Collection $constraints;

    public function name(): string
    {
        return 'Symfony Validator';
    }

    public function setUp(): void
    {
        $this->validator = Validation::createValidator();
        $this->constraints = new Assert\Collection([
            'sku' => [new Assert\NotBlank(), new Assert\Type('string')],
            'price' => [new Assert\Type('numeric'), new Assert\Positive()],
            'quantity' => [new Assert\Type('integer'), new Assert\GreaterThanOrEqual(0)],
            'active' => [new Assert\Type('bool')],
        ]);
    }

    public function validate(Product $product): bool
    {
        $violations = $this->validator->validate($product->toArray(), $this->constraints);

        return $violations->count() === 0;
    }
}
