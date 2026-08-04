<?php

declare(strict_types=1);

namespace Bench;

readonly class Product
{
    public function __construct(
        public string $sku,
        public float $price,
        public int $quantity,
        public bool $active,
    ) {}

    /** @return array{sku: string, price: float, quantity: int, active: bool} */
    public function toArray(): array
    {
        return [
            'sku' => $this->sku,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'active' => $this->active,
        ];
    }
}
