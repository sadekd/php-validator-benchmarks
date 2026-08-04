<?php

declare(strict_types=1);

namespace Bench;

final class DataGenerator
{
    public const DEFAULT_SEED = 20260804;

    /**
     * Seeded so every PHP version in the matrix validates byte-identical input — MT19937 output
     * for a given seed has been stable since PHP 7.1, so the valid/invalid split does not drift
     * between runs or versions.
     *
     * @return list<Product>
     */
    public static function generate(int $count, int $seed = self::DEFAULT_SEED): array
    {
        mt_srand($seed, MT_RAND_MT19937);

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
