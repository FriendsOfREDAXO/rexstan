<?php declare(strict_types = 1);

namespace SqlFtw\Tests\Mysql;

use Dogma\Application\Colors;
use Dogma\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use SqlFtw\Formatter\Formatter;
use SqlFtw\Platform\Platform;
use SqlFtw\Session\Session;
use SqlFtw\Tests\Mysql\Data\TestSkips;
use SqlFtw\Tests\Mysql\Data\TestSuites;
use SqlFtw\Tests\Mysql\Data\VersionTags;
use SqlFtw\Tests\ResultRenderer;
use function Amp\ParallelFunctions\parallelMap;
use function Amp\Promise\wait;
use function chdir;
use function count;
use function ctype_digit;
use function dirname;
use function exec;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function in_array;
use function ini_set;
use function is_dir;
use function ltrim;
use function mkdir;
use function set_time_limit;
use function str_replace;
use function system;

class MysqlTest
{
    use VersionTags;
    use TestSkips;
    use TestSuites;

    private const MYSQL_REPOSITORY_LINK = 'git@github.com:mysql/mysql-server.git';
    private const DEFAULT_TAG = 'mysql-8.0.31';

    public string $tempDir;

    public string $tempTestsDir;

    public string $mysqlRepoDir;

    public string $mysqlTestsDir;

    public string $lastFailPath;

    public string $currentTagPath;

    private bool $specificTests = false;

    public function __construct()
    {
        $this->tempDir = str_replace('\\', '/', dirname(__DIR__, 2)) . '/temp';
        $this->tempTestsDir = $this->tempDir . '/tests';
        $this->mysqlRepoDir = $this->tempTestsDir . '/mysql-server';
        $this->mysqlTestsDir = $this->mysqlRepoDir . '/mysql-test';
        $this->lastFailPath = $this->tempTestsDir . '/last-fail.txt';
        $this->currentTagPath = $this->tempTestsDir . '/current-tag.txt';
    }

    public function listSuites(): void
    {
        echo Colors::white("MySQL Test suites:") . "\n";
        foreach (self::$suites as $i => $suite) {
            echo "  " . Colors::yellow("{$i}") . ": " . Colors::white("{$suite}") . "\n";
        }
    }

    /**
     * @param list<string> $tests
     */
    public function run(bool $singleThread, ?string $tag = null, array $tests = []): void
    {
        if ($tag === null) {
            $tag = self::DEFAULT_TAG;
        }
        $this->initMysqlRepo();
        $this->checkoutTag($tag);

        ini_set('memory_limit', '3G');

        [$paths, $fullRun] = $this->getPaths($tests);

        $platform = Platform::fromTag(Platform::MYSQL, $tag);
        $version = $platform->getVersion()->format();
        $session = new Session($platform);
        $formatter = new Formatter($session);
        $renderer = new ResultRenderer($this->mysqlTestsDir, $singleThread, $fullRun, $formatter);

        if ($singleThread) {
            // renders errors immediately
            $results = [];
            foreach ($paths as $path) {
                $results[] = (new MysqlTestJob())->run($path, $version, true, $fullRun, $renderer);
            }
        } else {
            // collects errors and renders them at the end
            $parallelRunner = static function (string $path) use ($version, $fullRun, $renderer): Result {
                ini_set('memory_limit', '3G');
                set_time_limit(25);

                return (new MysqlTestJob())->run($path, $version, false, $fullRun, $renderer);
            };

            /** @var list<Result> $results */
            $results = wait(parallelMap($paths, $parallelRunner)); // @phpstan-ignore-line Unable to resolve the template type T in call to function Amp\Promise\wait
        }

        $errorPaths = $renderer->displayResults($results);

        if (!$this->specificTests && $errorPaths !== []) {
            $this->repeatPaths($errorPaths);
        }
    }

    /**
     * @param list<string> $paths
     */
    public function repeatPaths(array $paths): void
    {
        file_put_contents($this->lastFailPath, implode("\n", $paths));
    }

    /**
     * @param list<string> $tests
     * @return array{list<string>, bool} ($paths, $fullRun)
     */
    public function getPaths(array $tests): array
    {
        if ($tests === []) {
            $tests = [''];
        }

        $this->specificTests = false;
        $files = [];
        $suites = [];
        foreach ($tests as $test) {
            if (Str::endsWith($test, '.test')) {
                $this->specificTests = true;
                $files[] = $test;
                continue;
            }

            if (!in_array($test, self::$suites, true)) {
                if (in_array("extra/{$test}", self::$suites, true)) {
                    $test = "extra/{$test}";
                } elseif (in_array("suite/{$test}/t", self::$suites, true)) {
                    $test = "suite/{$test}/t";
                } elseif (ctype_digit($test) && $test < count(self::$suites)) {
                    $test = self::$suites[(int) $test];
                } elseif ($test === '') {
                    // all
                } else {
                    echo "Test suite '{$test}' not found.\n";
                    exit(1);
                }
            }
            $suitePath = $test !== '' ? $this->mysqlTestsDir . '/' . $test : $this->mysqlTestsDir;
            $suites[] = $suitePath;
        }

        // last time failed tests
        if (!$this->specificTests && file_exists($this->lastFailPath)) {
            $paths = file_get_contents($this->lastFailPath);

            if ($paths !== '' && $paths !== false) {
                $paths = explode("\n", $paths);
                $count = count($paths);
                echo "Running only last time failed tests ({$count})\n\n";
                file_put_contents($this->lastFailPath, '');

                return [$paths, false];
            }
        }

        // test suites
        $paths = [];
        foreach ($suites as $suitePath) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($suitePath));
            /** @var SplFileInfo $fileInfo */
            foreach ($it as $fileInfo) {
                if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'test') {
                    continue;
                }
                $path = str_replace('\\', '/', $fileInfo->getPathname());

                foreach (self::$skips as $skip) {
                    if (Str::contains($path, $skip)) {
                        continue 2;
                    }
                }

                $paths[] = $path;
            }
        }

        // specific tests
        if ($files !== []) {
            $suitePath = $this->mysqlTestsDir;
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($suitePath));
            /** @var SplFileInfo $fileInfo */
            foreach ($it as $fileInfo) {
                if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'test') {
                    continue;
                }
                $path = str_replace('\\', '/', $fileInfo->getPathname());

                foreach ($files as $file) {
                    if (Str::startsWith($file, '*')) {
                        $file = ltrim($file, '*');
                        if (Str::endsWith($path, $file)) {
                            $paths[] = $path;
                        }
                    } else {
                        if (Str::endsWith($path, '/' . $file)) {
                            $paths[] = $path;
                        }
                    }
                }
            }
        }

        $count = count($paths);
        if ($files !== []) {
            echo "Running specific tests ({$count})\n";
        }
        if ($suites !== []) {
            $all = implode(',', $suites);
            echo "Running all tests in {$all} ({$count})\n";
        }

        if (!$this->specificTests) {
            file_put_contents($this->lastFailPath, '');
        }

        return [$paths, $tests === ['']];
    }

    public function initMysqlRepo(): void
    {
        if (!is_dir($this->tempDir)) {
            if (!mkdir($this->tempDir)) {
                echo "Cannot create directory {$this->tempDir}.\n";
                exit(1);
            }
        }
        if (!is_dir($this->tempTestsDir)) {
            if (!mkdir($this->tempTestsDir)) {
                echo "Cannot create directory {$this->tempTestsDir}.\n";
                exit(1);
            }
        }
        if (is_dir($this->mysqlRepoDir)) {
            // already initiated
            return;
        }

        chdir($this->tempTestsDir);

        // sparse checkout setup (~4.5 GB -> ~270 MB)
        // https://git-scm.com/docs/git-sparse-checkout
        // https://github.blog/2020-01-17-bring-your-monorepo-down-to-size-with-sparse-checkout/#sparse-checkout-and-partial-clones
        // todo: there is still some space to optimize, because sparse checkout of branch '8.0' has only ~70 MB. tags suck
        echo Colors::lyellow("git clone --depth 1 --filter=blob:none --sparse " . self::MYSQL_REPOSITORY_LINK) . "\n";
        system("git clone --depth 1 --filter=blob:none --sparse " . self::MYSQL_REPOSITORY_LINK);
        chdir($this->mysqlRepoDir);
        exec("git config core.sparseCheckout true");
        exec("git config core.sparseCheckoutCone false");
        file_put_contents($this->mysqlRepoDir . '/.git/info/sparse-checkout', '*.test');
    }

    public function checkoutTag(string $tag): void
    {
        chdir($this->mysqlRepoDir);

        exec("git rev-parse {$tag}", $out, $result);
        if ($result !== 0) {
            //echo Colors::lyellow("git fetch --tags") . "\n"; // slightly bigger (~290 MB)
            //system("git fetch --tags");
            echo Colors::lyellow("git fetch origin refs/tags/{$tag}:refs/tags/{$tag}") . "\n";
            system("git fetch origin refs/tags/{$tag}:refs/tags/{$tag}");
        }

        $currentTag = null;
        if (file_exists($this->currentTagPath)) {
            $currentTag = file_get_contents($this->currentTagPath);
        }
        if ($tag !== $currentTag) {
            echo Colors::lyellow("git checkout {$tag}") . "\n";
            system("git checkout {$tag}");
        }

        file_put_contents($this->currentTagPath, $tag);
    }

}
