<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;

interface ValidatorInterface
{
    public function name(): string;

    public function setUp(): void;

    public function validate(Product $product): bool;
}
