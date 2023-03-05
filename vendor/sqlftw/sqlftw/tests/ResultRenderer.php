<?php declare(strict_types = 1);

namespace SqlFtw\Tests;

use Dogma\Application\Colors;
use Dogma\Debug\Debugger;
use Dogma\Debug\Dumper;
use Dogma\Debug\Units;
use Dogma\Str;
use SqlFtw\Formatter\Formatter;
use SqlFtw\Parser\InvalidCommand;
use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Command;
use SqlFtw\Sql\SqlMode;
use SqlFtw\Tests\Mysql\MysqlTestJob;
use SqlFtw\Tests\Mysql\Result;
use function count;
use function rd;
use function rdf;
use function rl;
use function usort;

class ResultRenderer
{

    private string $baseDir;

    private bool $singleThread;

    private bool $fullRun;

    private Formatter $formatter;

    public function __construct(string $baseDir, bool $singleThread, bool $fullRun, Formatter $formatter)
    {
        $this->baseDir = $baseDir;
        $this->singleThread = $singleThread;
        $this->fullRun = $fullRun;
        $this->formatter = $formatter;
    }

    /**
     * @param list<Result> $results
     * @return list<string> error paths
     */
    public function displayResults(array $results): array
    {
        $size = $time = $statements = $tokens = 0;
        $falseNegatives = [];
        $falsePositives = [];
        $serialisationErrors = [];
        $usedExceptions = [];
        $unusedExceptions = [];
        foreach ($results as $result) {
            $size += $result->size;
            $time += $result->time;
            $statements += $result->statements;
            $tokens += $result->tokens;
            if ($result->falseNegatives !== []) {
                $falseNegatives[$result->path] = $result->falseNegatives;
            }
            if ($result->falsePositives !== []) {
                $falsePositives[$result->path] = $result->falsePositives;
            }
            if ($result->serialisationErrors !== []) {
                $serialisationErrors[$result->path] = $result->serialisationErrors;
            }
            if ($this->fullRun) {
                $usedExceptions = array_merge($result->usedSerialisationExceptions);
            }
        }

        if (!$this->singleThread) {
            $this->renderFalseNegatives($falseNegatives);
            $this->renderFalsePositives($falsePositives);
            $this->renderSerialisationErrors($serialisationErrors);
        }
        if ($this->fullRun) {
            $unusedExceptions = MysqlTestJob::getUnusedExceptions($usedExceptions);
            //$this->renderUnusedSerialisationExceptions($unusedExceptions);
        }

        echo "\n\n";
        if ($falseNegatives !== [] || $falsePositives !== [] || $serialisationErrors !== []) {
            $errors = count($falseNegatives) + count($falsePositives) + count($serialisationErrors);
            echo Colors::white(" $errors failing test" . ($errors > 1 ? 's ' : ' '), Colors::RED) . "\n\n";
        } else {
            echo Colors::white(" No errors ", Colors::GREEN) . "\n\n";
        }

        if ($falseNegatives !== []) {
            echo 'False negatives: ' . Colors::white((string) array_sum(array_map(static function ($a): int {
                return count($a);
			}, $falseNegatives))) . "\n";
        }
        if ($falsePositives !== []) {
            echo 'False positives: ' . Colors::white((string) array_sum(array_map(static function ($a): int {
                return count($a);
			}, $falsePositives))) . "\n";
        }
        if ($serialisationErrors !== []) {
            echo 'Serialisation errors: ' . Colors::white((string) array_sum(array_map(static function ($a): int {
                return count($a);
			}, $serialisationErrors))) . "\n";
        }

        echo 'Running time: ' . Units::time(microtime(true) - Debugger::getStart()) . "\n";
        echo 'Parse time: ' . Units::time($time) . "\n";
        echo 'Code parsed: ' . Units::memory($size) . "\n";
        echo "Statements parsed: {$statements}\n";
        echo "Tokens parsed: {$tokens}\n";

        $this->renderOutliers($results);

        if ($this->fullRun && $unusedExceptions !== []) {
            echo "Unused serialisation exceptions: " . Colors::white((string) count($unusedExceptions)) . "\n";
        }

        return array_merge(array_keys($falseNegatives), array_keys($falsePositives), array_keys($serialisationErrors));
    }

    /**
     * @param array<string, non-empty-list<array{Command, TokenList, SqlMode}>> $falseNegatives
     */
    public function renderFalseNegatives(array $falseNegatives): void
    {
        foreach ($falseNegatives as $path => $falseNegative) {
            foreach ($falseNegative as [$command, $tokenList, $mode]) {
                $this->renderTestPath($path);
                $this->renderFalseNegative($command, $tokenList, $mode);
            }
        }
    }

    public function renderFalseNegative(Command $command, TokenList $tokenList, SqlMode $mode): void
    {
        rl('False negative (should not fail):', null, 'r');
        rl("'{$mode->getValue()}'", 'sql_mode', 'C');

        $tokensSerialized = trim($tokenList->serialize());
        rl($tokensSerialized, null, 'y');

        //$commandSerialized = $formatter->serialize($command);
        //$commandSerialized = preg_replace('~\s+~', ' ', $commandSerialized);
        //rl($commandSerialized);

        if ($command instanceof InvalidCommand) {
            //rl($mode->getValue(), 'mode', 'C');
            $exception = $command->getException();
            $parsedCommand = $command->getCommand();
            if ($parsedCommand !== null) {
                rd($parsedCommand);
            }
            re($exception);
        } else {
            rd($command);
        }
        //rd($tokenList);
    }

    /**
     * @param array<string, non-empty-list<array{Command, TokenList, SqlMode}>> $falsePositives
     */
    public function renderFalsePositives(array $falsePositives): void
    {
        foreach ($falsePositives as $path => $falsePositive) {
            foreach ($falsePositive as [$command, $tokenList, $mode]) {
                $this->renderTestPath($path);
                $this->renderFalsePositive($command, $tokenList, $mode);
            }
        }
    }

    public function renderFalsePositive(Command $command, TokenList $tokenList, SqlMode $mode): void
    {
        rl('False positive (should fail):', null, 'r');
        rl($mode->getValue(), 'mode', 'C');

        $tokensSerialized = trim($tokenList->serialize());
        rl($tokensSerialized, null, 'y');

        //$commandSerialized = $formatter->serialize($command);
        //$commandSerialized = preg_replace('~\s+~', ' ', $commandSerialized);
        //rl($commandSerialized);

        rd($command, 4);
        //rd($tokenList);
    }

    /**
     * @param array<string, non-empty-list<array{Command, TokenList, SqlMode}>> $serialisationErrors
     */
    public function renderSerialisationErrors(array $serialisationErrors): void
    {
        $job = new MysqlTestJob();
        foreach ($serialisationErrors as $path => $serialisationError) {
            foreach ($serialisationError as [$command, $tokenList, $mode]) {
                $this->renderTestPath($path);
                $this->renderSerialisationError($command, $tokenList, $mode, $job);
            }
        }
    }

    public function renderSerialisationError(Command $command, TokenList $tokenList, SqlMode $mode, MysqlTestJob $job): void
    {
        rl('Serialisation error:', null, 'r');

        [$origin, $originNorm] = $job->normalizeOriginalSql($tokenList, true);
        [$parsed, $parsedNorm] = $job->normalizeParsedSql($command, $this->formatter, $tokenList->getSession(), true);

        // before normalization
        Dumper::$escapeWhiteSpace = false;
        rd($origin);
        rd($parsed);
        Dumper::$escapeWhiteSpace = true;

        // after normalization
        rd($originNorm);
        rd($parsedNorm);

        // diff
        rdf($originNorm, $parsedNorm);

        rd($command, 20);
        rd($tokenList);
    }

    /**
     * @param list<string> $exceptions
     */
    public function renderUnusedSerialisationExceptions(array $exceptions): void
    {
        rl('Unused serialisation exceptions:', null, 'r');
        foreach ($exceptions as $exception) {
            rl($exception);
        }
    }

    /**
     * @param list<Result> $results
     */
    private function renderOutliers(array $results): void
    {
        usort($results, static function (Result $a, Result $b) {
            return $b->time <=> $a->time;
        });
        echo "Slowest:\n";
        $n = 0;
        foreach ($results as $result) {
            $time = Units::time($result->time);
            $memory = Units::memory($result->memory);
            $size = Units::memory($result->size);
            $path = Str::after($result->path, $this->baseDir);
            echo "  {$time}, {$memory}, pid: {$result->pid}, {$result->statements} st ({$path} - {$size})\n";
            $n++;
            if ($n >= 10) {
                break;
            }
        }

        usort($results, static function (Result $a, Result $b) {
            return $b->memory <=> $a->memory;
        });
        echo "Hungriest:\n";
        $n = 0;
        foreach ($results as $result) {
            $time = Units::time($result->time);
            $memory = Units::memory($result->memory);
            $size = Units::memory($result->size);
            $path = Str::after($result->path, $this->baseDir);
            echo "  {$time}, {$memory}, pid: {$result->pid}, {$result->statements} st ({$path} - {$size})\n";
            $n++;
            if ($n >= 10) {
                break;
            }
        }
    }

    public function renderTestPath(string $path): void
    {
        rl($path, null, 'g');
    }

}
