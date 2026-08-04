<?php

declare(strict_types=1);

namespace Bench;

use Composer\InstalledVersions;

/**
 * What actually got installed on this PHP version.
 *
 * The matrix resolves dependencies per PHP version instead of from a lock file, so the
 * library backing a given validator changes underneath it: symfony/validator 8.x needs
 * PHP >= 8.4.1 and respect/validation 3.x needs PHP >= 8.5. Every run records what it ran
 * against so the comparison can flag rows where the library moved, not just the runtime.
 */
final class Environment
{
    /** Composer package backing each validator, keyed by validator name. */
    private const PACKAGES = [
        'Respect Validation' => 'respect/validation',
        'Symfony Validator' => 'symfony/validator',
        'Symfony Attributes' => 'symfony/validator',
        'Laravel Validation' => 'illuminate/validation',
        'webmozart/assert' => 'webmozart/assert',
    ];

    public static function phpSeries(): string
    {
        return PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    }

    public static function supportsPropertyHooks(): bool
    {
        return PHP_VERSION_ID >= 80400;
    }

    public static function version(string $package): ?string
    {
        if (!class_exists(InstalledVersions::class) || !InstalledVersions::isInstalled($package)) {
            return null;
        }

        return ltrim((string) InstalledVersions::getPrettyVersion($package), 'v');
    }

    /** e.g. "symfony/validator 8.1.2" — null for the dependency-free approaches. */
    public static function libraryFor(string $validatorName): ?string
    {
        $package = self::PACKAGES[$validatorName] ?? null;
        if ($package === null) {
            return null;
        }

        $version = self::version($package);

        return $version === null ? $package : $package . ' ' . $version;
    }

    /** @return array<string, string> package => version, for the run metadata block */
    public static function packages(): array
    {
        $versions = [];
        foreach (array_unique(array_values(self::PACKAGES)) as $package) {
            $version = self::version($package);
            if ($version !== null) {
                $versions[$package] = $version;
            }
        }
        ksort($versions);

        return $versions;
    }
}
