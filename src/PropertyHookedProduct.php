<?php

declare(strict_types=1);

namespace Bench;

class PropertyHookedProduct
{
    public string $sku {
        set(string $value) {
            if ($value === '') {
                throw new \InvalidArgumentException('SKU must not be empty');
            }
            $this->sku = $value;
        }
    }

    public float $price {
        set(float $value) {
            if ($value <= 0.0) {
                throw new \InvalidArgumentException('Price must be positive');
            }
            $this->price = $value;
        }
    }

    public int $quantity {
        set(int $value) {
            if ($value < 0) {
                throw new \InvalidArgumentException('Quantity must be non-negative');
            }
            $this->quantity = $value;
        }
    }

    public bool $active {
        set(bool $value) {
            $this->active = $value;
        }
    }

    public function __construct(string $sku, float $price, int $quantity, bool $active)
    {
        $this->sku = $sku;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->active = $active;
    }
}
