<?php
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\assertFalse;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration::class)->in('Feature', 'Unit');

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

uses()->beforeEach(function () {
    // per-test temporary dir name
    $this->tmpDir = sys_get_temp_dir() . '/jds_json_test_' . bin2hex(random_bytes(6));
    if (!is_dir($this->tmpDir)) {
        mkdir($this->tmpDir, 0755, true);
    }
})->afterEach(function () {
    // recursive cleanup
    if (isset($this->tmpDir) && is_dir($this->tmpDir)) {
        $it = new RecursiveDirectoryIterator($this->tmpDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }
        @rmdir($this->tmpDir);
    }
});

/**
 * Create a temporary file with content.
 */
function create_tmp_file(string $dir, string $name, string $content): string
{
    $path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
    file_put_contents($path, $content);
    return $path;
}