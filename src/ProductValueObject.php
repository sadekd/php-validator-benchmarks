<?php

declare(strict_types=1);

namespace Bench;

readonly class ProductValueObject
{
    public string $sku;
    public float $price;
    public int $quantity;
    public bool $active;

    public function __construct(string $sku, float $price, int $quantity, bool $active)
    {
        if ($sku === '') {
            throw new \InvalidArgumentException('SKU must not be empty');
        }
        if ($price <= 0.0) {
            throw new \InvalidArgumentException('Price must be positive');
        }
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity must be non-negative');
        }

        $this->sku = $sku;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->active = $active;
    }
}
