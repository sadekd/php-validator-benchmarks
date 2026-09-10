<?php

declare(strict_types=1);

namespace Bench;

/**
 * Mapped by CuyZ\Valinor\MapperBuilder. Constructor throws \RuntimeException on invalid
 * data — the mapper is configured with filterExceptions() to convert those into a
 * MappingError (see ValinorValidator); uncaught exceptions otherwise propagate as-is.
 */
readonly class ValinorProduct
{
    /** @param non-empty-string $sku */
    public function __construct(
        public string $sku,
        public float $price,
        public int $quantity,
        public bool $active,
    ) {
        if ($price <= 0.0) {
            throw new \RuntimeException('Price must be positive');
        }
        if ($quantity < 0) {
            throw new \RuntimeException('Quantity must be non-negative');
        }
    }
}
