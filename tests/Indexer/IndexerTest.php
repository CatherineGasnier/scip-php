<?php

declare(strict_types=1);

namespace Tests\Indexer;

use Override;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Scip\Document;
use Scip\Index;
use Scip\Occurrence;
use Scip\SymbolRole;
use ScipPhp\File\Reader;
use ScipPhp\Indexer;
use SplFileInfo;

use function array_keys;
use function exec;
use function file_put_contents;
use function in_array;
use function is_file;
use function rename;
use function str_ends_with;
use function strlen;
use function substr;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const DIRECTORY_SEPARATOR;

final class IndexerTest extends TestCase
{
    private const string TESTDATA_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR;

    /** @var non-empty-string */
    private string $indexFile;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $tempDir = sys_get_temp_dir();
        $indexFile = tempnam($tempDir, 'scip-php-index-');
        if ($indexFile === false || !is_file($indexFile)) {
            self::fail('Cannot create temporary file.');
        }
        if (!rename($indexFile, "{$indexFile}.scip")) {
            unlink($indexFile);
            self::fail('Cannot rename temporary file.');
        }
        $this->indexFile = "{$indexFile}.scip";
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_file($this->indexFile)) {
            unlink($this->indexFile);
        }
    }

    #[RunInSeparateProcess]
    public function testIndexer(): void
    {
        $indexer = new Indexer(self::TESTDATA_DIR . 'scip-php-test', 'test', []);
        $index = $indexer->index();

        file_put_contents($this->indexFile, $index->serializeToString());

        $actualPath = self::TESTDATA_DIR . 'actual';

        $output = [];
        $result = exec("scip snapshot --strict=false --from={$this->indexFile} --to={$actualPath}", $output);
        if ($result === false) {
            self::fail('Error executing scip: ' . ($output[0] ?? 'no output') . '.');
        }

        $goldenFiles = self::files(self::TESTDATA_DIR . 'golden');
        $actualFiles = self::files($actualPath);

        self::assertEqualsCanonicalizing(array_keys($goldenFiles), array_keys($actualFiles));

        foreach ($goldenFiles as $name => $goldenPath) {
            $actualPath = $actualFiles[$name];

            $golden = Reader::read($goldenPath);
            $actual = Reader::read($actualPath);

            self::assertSame($golden, $actual);
        }
    }

    #[RunInSeparateProcess]
    public function testTestRole(): void
    {
        $indexer = new Indexer(self::TESTDATA_DIR . 'scip-php-test', 'test', []);
        $occurrences = self::occurrences($indexer->index());

        // The files that phpunit.xml makes test code: two files that PHPUnit runs, and a helper.
        $testFiles = ['src/ClassATests.php', 'tests/ClassBTests.php', 'tests/Helper.php'];
        foreach ($occurrences as $path => $occs) {
            $isTestFile = in_array($path, $testFiles, true);
            foreach ($occs as $occ) {
                $hasTestRole = ($occ->getSymbolRoles() & SymbolRole::Test) !== 0;
                self::assertSame($isTestFile, $hasTestRole, "{$path}: {$occ->getSymbol()}");
            }
        }

        $def = SymbolRole::Definition;
        $test = SymbolRole::Test;
        // Test method, in a file that PHPUnit runs.
        self::assertSame([$def | $test], self::roles($occurrences['tests/ClassBTests.php'], 'ClassBTests#testB1().'));
        // Helper, which PHPUnit does not run, but which is in the test suites and autoloaded via autoload-dev.
        self::assertSame([$def | $test], self::roles($occurrences['tests/Helper.php'], 'Helper#classB().'));
        // Reference from test code to production code.
        self::assertSame([$test], self::roles($occurrences['tests/ClassBTests.php'], 'TestData/ClassB#B1.'));
        // Production code in the test suites, which is not autoloaded via autoload-dev.
        self::assertSame([$def], self::roles($occurrences['src/ClassB.php'], 'TestData/ClassB#B1.'));
        // Code autoloaded via autoload-dev, but not in the test suites.
        self::assertSame([$def], self::roles($occurrences['bench/ClassBBench.php'], 'ClassBBench#benchB2().'));
    }

    /**
     * Returns the occurrences of each document of the index, by relative path.
     *
     * @return array<string, list<Occurrence>>
     */
    private static function occurrences(Index $index): array
    {
        // The bindings declare repeated fields as Google\Protobuf\Internal\RepeatedField,
        // a class alias which PHPStan does not know.
        $occurrences = [];
        /** @var iterable<Document> $docs */
        $docs = $index->getDocuments(); // @phpstan-ignore varTag.type
        foreach ($docs as $doc) {
            /** @var iterable<Occurrence> $occs */
            $occs = $doc->getOccurrences(); // @phpstan-ignore varTag.type
            foreach ($occs as $occ) {
                $occurrences[$doc->getRelativePath()][] = $occ;
            }
        }
        return $occurrences;
    }

    /**
     * Returns the roles of the occurrences of the symbols whose name ends with the given suffix.
     *
     * @param  list<Occurrence>  $occurrences
     * @return list<int>
     */
    private static function roles(array $occurrences, string $symbolSuffix): array
    {
        $roles = [];
        foreach ($occurrences as $occ) {
            if (str_ends_with($occ->getSymbol(), $symbolSuffix)) {
                $roles[] = $occ->getSymbolRoles();
            }
        }
        return $roles;
    }

    /** @return array<non-empty-string, non-empty-string> */
    private static function files(string $dir): array
    {
        $fileIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
        );

        $files = [];
        foreach ($fileIterator as $f) {
            if ($f instanceof SplFileInfo && $f->isFile() && $f->getExtension() === 'php') {
                $path = $f->getRealPath();
                $relPath = substr($f->getPathname(), strlen($dir));
                if ($path !== false && $path !== '' && $relPath !== '') {
                    $files[$relPath] = $path;
                }
            }
        }
        return $files;
    }
}
