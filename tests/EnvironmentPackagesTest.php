<?php

declare(strict_types=1);

namespace Bench\Tests;

use Bench\Environment;
use Bench\ValidatorSet;
use PHPUnit\Framework\TestCase;

/**
 * Enforces the vision.md constraint: a library-backed validator must be mapped in
 * Environment::PACKAGES. Otherwise libraryFor() returns null, no library version is
 * recorded and ComparisonFormatter cannot flag library drift for it.
 */
final class EnvironmentPackagesTest extends TestCase
{
    /** Validators with no Composer library behind them, by design. */
    private const DEPENDENCY_FREE = ['Pure PHP', 'filter_var()', 'Value Objects', 'Property Hooks'];

    /** @return array<string, string> */
    private static function packages(): array
    {
        // PACKAGES is private; read it via reflection rather than widening Environment's API.
        return (new \ReflectionClassConstant(Environment::class, 'PACKAGES'))->getValue();
    }

    public function testEveryRegisteredValidatorIsMappedOrDependencyFree(): void
    {
        $packages = self::packages();
        foreach (ValidatorSet::forThisRuntime() as $validator) {
            $name = $validator->name();
            $this->assertTrue(
                isset($packages[$name]) || in_array($name, self::DEPENDENCY_FREE, true),
                "Validator '$name' is not in Environment::PACKAGES nor listed as dependency-free: "
                . 'add a PACKAGES entry (library-backed) or add it to DEPENDENCY_FREE in this test.',
            );
        }
    }

    public function testEveryPackagesKeyIsARegisteredValidator(): void
    {
        $names = array_map(static fn ($v) => $v->name(), ValidatorSet::forThisRuntime());
        foreach (array_keys(self::packages()) as $key) {
            $this->assertContains($key, $names, "Environment::PACKAGES has a stale entry '$key': no registered validator has that name.");
        }
    }
}
