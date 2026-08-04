<?php

declare(strict_types=1);

namespace Bench;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ValidatedProduct
{
    public function __construct(
        #[Assert\NotBlank] #[Assert\Type("string")] public string $sku,

        #[Assert\Positive] public float $price,

        #[Assert\GreaterThanOrEqual(0)] public int $quantity,

        #[Assert\Type("bool")] public bool $active,
    ) {}
}
