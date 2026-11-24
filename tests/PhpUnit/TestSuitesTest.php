<?php

declare(strict_types=1);

namespace Tests\PhpUnit;

use FilesystemIterator;
use Override;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ScipPhp\PhpUnit\TestSuites;
use SplFileInfo;

use function array_fill_keys;
use function array_keys;
use function dirname;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function realpath;
use function rmdir;
use function str_replace;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

final class TestSuitesTest extends TestCase
{
    /** @var list<non-empty-string> */
    private array $projects = [];

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->projects as $root) {
            self::remove($root);
        }
    }

    public function testLoadWithoutTestSuites(): void
    {
        $noTestSuites = '<phpunit bootstrap="vendor/autoload.php"/>';
        $malformed = '<phpunit><testsuites>';
        $missingDir = self::config('<directory>missing</directory>');

        self::assertNull(TestSuites::load($this->project([])), 'no configuration file');
        self::assertNull(TestSuites::load($this->project(['phpunit.xml' => $noTestSuites])), 'no test suites');
        self::assertNull(TestSuites::load($this->project(['phpunit.xml' => $malformed])), 'malformed file');
        self::assertNull(TestSuites::load($this->project(['phpunit.xml' => $missingDir])), 'missing directory');
    }

    public function testLoadConfigPrecedence(): void
    {
        $files = ['a/ATest.php' => '', 'b/BTest.php' => '', 'c/CTest.php' => ''];
        $xml = self::config('<directory>a</directory>');
        $distXml = self::config('<directory>b</directory>');
        $xmlDist = self::config('<directory>c</directory>');

        $root = $this->project([
            'phpunit.xml'      => $xml,
            'phpunit.dist.xml' => $distXml,
            'phpunit.xml.dist' => $xmlDist,
            ...$files,
        ]);
        $suites = self::load($root);
        self::assertTrue($suites->runs(self::path($root, 'a/ATest.php')));
        self::assertFalse($suites->runs(self::path($root, 'b/BTest.php')));

        $root = $this->project(['phpunit.dist.xml' => $distXml, 'phpunit.xml.dist' => $xmlDist, ...$files]);
        $suites = self::load($root);
        self::assertTrue($suites->runs(self::path($root, 'b/BTest.php')));
        self::assertFalse($suites->runs(self::path($root, 'c/CTest.php')));
    }

    public function testLoadTestSuiteWithoutTestSuitesElement(): void
    {
        $root = $this->project([
            'phpunit.xml' => '<phpunit><testsuite name="tests"><directory>tests</directory></testsuite></phpunit>',
            'tests/FooTest.php' => '',
        ]);
        self::assertTrue(self::load($root)->runs(self::path($root, 'tests/FooTest.php')));
    }

    public function testRunsAndContains(): void
    {
        $config = self::config(
            '<directory>tests/unit</directory>'
            . '<directory prefix="it_" suffix=".phpt">tests/integration</directory>'
            . '<directory>modules/*/tests</directory>'
            . '<directory>packages/**/tests</directory>'
            . '<file>tools/check.php</file>'
            . '<exclude>tests/unit/Excluded</exclude>',
        );

        // File => [whether PHPUnit runs it, whether it is in the test suites].
        $expected = [
            'tests/unit/FooTest.php'           => [true, true],
            'tests/unit/sub/BarTest.php'       => [true, true],
            'tests/unit/Helper.php'            => [false, true],   // No suffix.
            'tests/unit/Excluded/SkipTest.php' => [false, true],   // Excluded.
            'tests/integration/it_foo.phpt'    => [true, true],
            'tests/integration/foo.phpt'       => [false, true],   // No prefix.
            'tests/integration/it_FooTest.php' => [false, true],   // No suffix.
            'modules/a/tests/ATest.php'        => [true, true],    // Wildcard.
            'modules/a/src/ATest.php'          => [false, false],
            'packages/x/y/tests/DeepTest.php'  => [true, true],    // Globstar.
            'tools/check.php'                  => [true, true],    // File element.
            'tools/other.php'                  => [false, false],
            'tests/unit2/FooTest.php'          => [false, false],  // Sibling directory.
            'src/FooTest.php'                  => [false, false],
        ];

        $root = $this->project(['phpunit.xml' => $config, ...array_fill_keys(array_keys($expected), '')]);
        $suites = self::load($root);

        foreach ($expected as $file => [$runs, $contains]) {
            self::assertSame($runs, $suites->runs(self::path($root, $file)), "runs {$file}");
            self::assertSame($contains, $suites->contains(self::path($root, $file)), "contains {$file}");
        }
    }

    private static function config(string $testSuite): string
    {
        return "<phpunit><testsuites><testsuite name=\"tests\">{$testSuite}</testsuite></testsuites></phpunit>";
    }

    /** @param  non-empty-string  $root */
    private static function load(string $root): TestSuites
    {
        $suites = TestSuites::load($root);
        self::assertNotNull($suites);
        return $suites;
    }

    /**
     * Creates a project with the given files in a new temporary directory.
     *
     * @param  array<non-empty-string, string>  $files  the contents of the files, by path relative to the root
     * @return non-empty-string  the real path of the project root
     */
    private function project(array $files): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('scip-php-testsuites-', true);
        if (!mkdir($dir)) {
            self::fail("Cannot create directory {$dir}.");
        }
        $root = realpath($dir);
        if ($root === false) {
            self::fail("Cannot resolve directory {$dir}.");
        }
        $this->projects[] = $root;

        foreach ($files as $file => $content) {
            $path = self::path($root, $file);
            if (!is_dir(dirname($path)) && !mkdir(dirname($path), recursive: true)) {
                self::fail("Cannot create directory for {$path}.");
            }
            if (file_put_contents($path, $content) === false) {
                self::fail("Cannot write {$path}.");
            }
        }
        return $root;
    }

    /**
     * @param  non-empty-string  $root
     * @param  non-empty-string  $file  a path relative to the root, with "/" as separator
     * @return non-empty-string
     */
    private static function path(string $root, string $file): string
    {
        return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    }

    /** @param  non-empty-string  $dir */
    private static function remove(string $dir): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($files as $f) {
            if ($f instanceof SplFileInfo) {
                $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
            }
        }
        rmdir($dir);
    }
}
