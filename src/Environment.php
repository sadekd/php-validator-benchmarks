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
        'Valinor' => 'cuyz/valinor',
    ];

    public static function phpSeries(): string
    {
        return PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    }

    public static function supportsPropertyHooks(): bool
    {
        return PHP_VERSION_ID >= 80400;
    }

    /**
     * OPcache / JIT state of this process, recorded so a reader can tell which config produced the ratios.
     *
     * @return array{opcache_loaded: bool, opcache_enable_cli: bool, opcache_jit: string, opcache_jit_buffer_size: int}
     */
    public static function runtime(): array
    {
        $loaded = extension_loaded('Zend OPcache');
        $jit = $loaded ? ini_get('opcache.jit') : false;

        $buffer = $loaded ? ini_get('opcache.jit_buffer_size') : false;

        return [
            'opcache_loaded' => $loaded,
            'opcache_enable_cli' => $loaded && filter_var(ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOLEAN),
            'opcache_jit' => $jit === false || $jit === '' ? 'off' : (string) $jit,
            // JIT only runs with a non-zero buffer, whatever opcache.jit says (it defaults to "tracing").
            'opcache_jit_buffer_size' => $buffer === false || $buffer === '' ? 0 : ini_parse_quantity($buffer),
        ];
    }

    /**
     * One-line label for a runtime() block (also decoded from JSON); null when the run predates it.
     *
     * @param array<string, mixed>|null $runtime
     */
    public static function describeRuntime(?array $runtime): ?string
    {
        if ($runtime === null) {
            return null;
        }
        if (empty($runtime['opcache_loaded'])) {
            return 'OPcache not loaded, JIT off';
        }
        if (empty($runtime['opcache_enable_cli'])) {
            return 'OPcache off (CLI), JIT inactive';
        }
        $jit = (string) ($runtime['opcache_jit'] ?? 'off');
        $jitOff = in_array(strtolower($jit), ['', '0', 'off', 'disable', 'disabled'], true)
            || (isset($runtime['opcache_jit_buffer_size']) && (int) $runtime['opcache_jit_buffer_size'] <= 0);

        return 'OPcache on, JIT ' . ($jitOff ? 'off' : $jit);
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
