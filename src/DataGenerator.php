<?php

declare(strict_types=1);

namespace Bench;

final class DataGenerator
{
    /** @return list<Product> */
    public static function generate(int $count): array
    {
        $products = [];

        for ($i = 0; $i < $count; $i++) {
            $isValid = mt_rand(1, 100) <= 80;

            if ($isValid) {
                $products[] = new Product(
                    sku: 'SKU-' . $i,
                    price: mt_rand(100, 99999) / 100.0,
                    quantity: mt_rand(0, 500),
                    active: (bool) mt_rand(0, 1),
                );
            } else {
                $defect = mt_rand(1, 3);
                $products[] = new Product(
                    sku: $defect === 1 ? '' : 'SKU-' . $i,
                    price: $defect === 2 ? -1.0 * mt_rand(1, 100) : mt_rand(100, 99999) / 100.0,
                    quantity: $defect === 3 ? -1 * mt_rand(1, 100) : mt_rand(0, 500),
                    active: (bool) mt_rand(0, 1),
                );
            }
        }

        return $products;
    }

    public static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        return sprintf('%.2f %s', $bytes / (1024 ** $power), $units[$power]);
    }
}
