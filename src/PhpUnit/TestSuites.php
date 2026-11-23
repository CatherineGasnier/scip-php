<?php

declare(strict_types=1);

namespace ScipPhp\PhpUnit;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ScipPhp\File\Reader;
use SebastianBergmann\FileIterator\Facade as FileIterator;

use function array_fill_keys;
use function dirname;
use function is_file;
use function realpath;
use function str_starts_with;
use function trim;

use const DIRECTORY_SEPARATOR;
use const LIBXML_NOERROR;
use const LIBXML_NONET;
use const LIBXML_NOWARNING;

/**
 * The files in the test suites declared in the PHPUnit XML configuration of a project.
 *
 * The files of a <directory> element are found with phpunit/php-file-iterator, the library
 * that PHPUnit itself uses for this. It expands wildcards in the path, and filters files by
 * prefix, suffix and <exclude> elements. All paths are real paths.
 */
final readonly class TestSuites
{
    /** Configuration file names, in the order in which PHPUnit looks for them. */
    private const array CONFIG_FILES = ['phpunit.xml', 'phpunit.dist.xml', 'phpunit.xml.dist'];

    /** Suffix of test files in a <directory> element without a suffix attribute. */
    private const string DEFAULT_SUFFIX = 'Test.php';

    /**
     * @param  array<non-empty-string, true>  $testFiles  the files that PHPUnit runs as tests
     * @param  array<non-empty-string, true>  $files      all files in the test suites, whatever their names
     */
    private function __construct(private array $testFiles, private array $files)
    {
    }

    /**
     * Loads the test suites from the configuration file that PHPUnit uses when it runs in the
     * project root. Returns null if there is no such file, if it cannot be parsed, or if its
     * test suites contain no files.
     *
     * @param  non-empty-string  $projectRoot
     */
    public static function load(string $projectRoot): ?self
    {
        foreach (self::CONFIG_FILES as $name) {
            $config = $projectRoot . DIRECTORY_SEPARATOR . $name;
            if (is_file($config)) {
                return self::parse($config);
            }
        }
        return null;
    }

    /**
     * Whether PHPUnit runs the file as a test: the file of a <file> element, or a file that
     * PHPUnit finds in a <directory> element.
     *
     * @param  non-empty-string  $filename  a real path
     */
    public function runs(string $filename): bool
    {
        return isset($this->testFiles[$filename]);
    }

    /**
     * Whether the file is in the test suites, whatever its name and the <exclude> elements:
     * the file of a <file> element, or a file in the directory of a <directory> element.
     *
     * @param  non-empty-string  $filename  a real path
     */
    public function contains(string $filename): bool
    {
        return isset($this->files[$filename]);
    }

    /** @param  non-empty-string  $config */
    private static function parse(string $config): ?self
    {
        $baseDir = dirname($config);
        $iterator = new FileIterator();
        $testFiles = [];
        $files = [];
        foreach (self::testSuiteElements($config) as $suite) {
            $exclude = self::paths($baseDir, $suite, 'exclude');
            foreach ($suite->getElementsByTagName('directory') as $d) {
                $dir = self::absolutePath($baseDir, $d->textContent);
                if ($dir === null) {
                    continue;
                }
                $suffix = $d->hasAttribute('suffix') ? $d->getAttribute('suffix') : self::DEFAULT_SUFFIX;
                $found = $iterator->getFilesAsArray($dir, $suffix, $d->getAttribute('prefix'), $exclude);
                $testFiles += array_fill_keys($found, true);
                $files += array_fill_keys($iterator->getFilesAsArray($dir), true);
            }
            foreach (self::paths($baseDir, $suite, 'file') as $path) {
                $file = realpath($path);
                if ($file !== false && is_file($file)) {
                    $testFiles[$file] = true;
                    $files[$file] = true;
                }
            }
        }
        return $files === [] ? null : new self($testFiles, $files);
    }

    /**
     * Returns the <testsuite> elements of the configuration file,
     * or an empty list if the file cannot be parsed.
     *
     * @param  non-empty-string  $config
     * @return list<DOMElement>
     */
    private static function testSuiteElements(string $config): array
    {
        $xml = Reader::read($config);
        $doc = new DOMDocument();
        if ($xml === '' || !$doc->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            return [];
        }

        // PHPUnit also accepts a single <testsuite> element without <testsuites>.
        $nodes = (new DOMXPath($doc))->query('/*/testsuites/testsuite | /*/testsuite');
        if ($nodes === false) {
            return [];
        }

        $suites = [];
        foreach ($nodes as $n) {
            if ($n instanceof DOMElement) {
                $suites[] = $n;
            }
        }
        return $suites;
    }

    /**
     * Returns the absolute paths in the child elements of a <testsuite> element with the given name.
     *
     * @return list<non-empty-string>
     */
    private static function paths(string $baseDir, DOMElement $suite, string $tagName): array
    {
        $paths = [];
        foreach ($suite->getElementsByTagName($tagName) as $e) {
            $path = self::absolutePath($baseDir, $e->textContent);
            if ($path !== null) {
                $paths[] = $path;
            }
        }
        return $paths;
    }

    /**
     * Makes a path from the configuration file absolute, or returns null if it is empty.
     * Like PHPUnit, it interprets relative paths relative to the directory of the
     * configuration file.
     *
     * @return ?non-empty-string
     */
    private static function absolutePath(string $baseDir, string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }
        return $baseDir . DIRECTORY_SEPARATOR . $path;
    }
}
