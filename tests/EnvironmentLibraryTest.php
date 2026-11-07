<?php

declare(strict_types=1);

namespace Bench\Tests;

use Bench\Environment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Pins the library strings that ComparisonFormatter compares to flag library drift. */
final class EnvironmentLibraryTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function dependencyFree(): array
    {
        return [
            'Pure PHP' => ['Pure PHP'],
            'filter_var()' => ['filter_var()'],
            'Property Hooks' => ['Property Hooks'],
            'Value Objects' => ['Value Objects'],
        ];
    }

    #[DataProvider('dependencyFree')]
    public function testDependencyFreeValidatorsHaveNoLibrary(string $name): void
    {
        $this->assertNull(Environment::libraryFor($name));
    }

    public function testUnknownNameHasNoLibrary(): void
    {
        $this->assertNull(Environment::libraryFor('No Such Validator'));
    }

    public function testMappedNameReturnsPackageWithOptionalVersion(): void
    {
        $library = Environment::libraryFor('Respect Validation');

        $this->assertNotNull($library);
        $this->assertMatchesRegularExpression('#^respect/validation( [0-9][^ ]*)?$#', $library);
    }

    public function testInstalledVersionHasNoLeadingV(): void
    {
        $library = Environment::libraryFor('Respect Validation');

        if ($library === null || !str_contains($library, ' ')) {
            $this->markTestSkipped('respect/validation is not installed.');
        }
        $this->assertMatchesRegularExpression('/^[^ ]+ [0-9]/', $library);
    }
}
