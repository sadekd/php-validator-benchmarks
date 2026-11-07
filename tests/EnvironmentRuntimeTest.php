<?php

declare(strict_types=1);

namespace Bench\Tests;

use Bench\ComparisonFormatter;
use Bench\Environment;
use PHPUnit\Framework\TestCase;

final class EnvironmentRuntimeTest extends TestCase
{
    public function testRuntimeHasExpectedShape(): void
    {
        $runtime = Environment::runtime();
        $this->assertSame(['opcache_loaded', 'opcache_enable_cli', 'opcache_jit', 'opcache_jit_buffer_size'], array_keys($runtime));
        $this->assertIsString($runtime['opcache_jit']);
    }

    public function testDescribeRuntime(): void
    {
        $this->assertNull(Environment::describeRuntime(null));
        $this->assertSame('OPcache not loaded, JIT off', Environment::describeRuntime(['opcache_loaded' => false, 'opcache_enable_cli' => false, 'opcache_jit' => 'off']));
        $this->assertSame('OPcache off (CLI), JIT inactive', Environment::describeRuntime(['opcache_loaded' => true, 'opcache_enable_cli' => false, 'opcache_jit' => 'tracing']));
        $this->assertSame('OPcache on, JIT off', Environment::describeRuntime(['opcache_loaded' => true, 'opcache_enable_cli' => true, 'opcache_jit' => 'off']));
        $this->assertSame('OPcache on, JIT tracing', Environment::describeRuntime(['opcache_loaded' => true, 'opcache_enable_cli' => true, 'opcache_jit' => 'tracing']));
        $this->assertSame('OPcache on, JIT tracing', Environment::describeRuntime(['opcache_loaded' => true, 'opcache_enable_cli' => true, 'opcache_jit' => 'tracing', 'opcache_jit_buffer_size' => 67108864]));
        // tracing is the default opcache.jit, but with a zero buffer JIT never runs
        $this->assertSame('OPcache on, JIT off', Environment::describeRuntime(['opcache_loaded' => true, 'opcache_enable_cli' => true, 'opcache_jit' => 'tracing', 'opcache_jit_buffer_size' => 0]));
    }

    private function render(array $runtimes): string
    {
        $runs = [];
        foreach ($runtimes as $series => $runtime) {
            $run = ['php_series' => $series, 'php_version' => $series . '.0', 'items' => 10, 'repeats' => 3, 'seed' => 1, 'packages' => [],
                'results' => [['name' => 'v', 'vps_best' => 1000.0, 'spread_pct' => 1.0, 'library' => null, 'valid' => 8, 'invalid' => 2]]];
            if ($runtime !== null) {
                $run['runtime'] = $runtime;
            }
            $runs[] = $run;
        }
        $path = tempnam(sys_get_temp_dir(), 'cmp');
        ComparisonFormatter::build($runs, $path);
        $md = (string) file_get_contents($path);
        unlink($path);

        return $md;
    }

    public function testComparisonFlagsDifferingConfig(): void
    {
        $off = ['opcache_loaded' => true, 'opcache_enable_cli' => false, 'opcache_jit' => 'off'];
        $on = ['opcache_loaded' => true, 'opcache_enable_cli' => true, 'opcache_jit' => 'tracing'];

        $md = $this->render(['8.2' => $off, '8.3' => $on]);
        $this->assertStringContainsString('| **OPcache / JIT** | OPcache off (CLI), JIT inactive | OPcache on, JIT tracing |', $md);
        $this->assertStringContainsString('OPcache/JIT configuration differs', $md);

        $this->assertStringNotContainsString('configuration differs', $this->render(['8.2' => $off, '8.3' => $off]));
    }

    public function testOldRunsWithoutRuntimeAreNotFlagged(): void
    {
        $off = ['opcache_loaded' => true, 'opcache_enable_cli' => false, 'opcache_jit' => 'off'];
        $md = $this->render(['8.2' => null, '8.3' => $off]);
        $this->assertStringContainsString('| **OPcache / JIT** | — | OPcache off (CLI), JIT inactive |', $md);
        $this->assertStringNotContainsString('configuration differs', $md);
    }
}
