<?php declare(strict_types = 1);

namespace SqlFtw\Tests\Mysql;

use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Command;
use SqlFtw\Sql\SqlMode;

class Result
{

    public string $path;

    public int $size;

    public float $time;

    public int $memory;

    public int $pid;

    public int $statements;

    public int $tokens;

    /** @var list<array{Command, TokenList, SqlMode}> */
    public array $falseNegatives;

    /** @var list<array{Command, TokenList, SqlMode}> */
    public array $falsePositives;

    /** @var list<array{Command, TokenList, SqlMode}> */
    public array $serialisationErrors;

    /** @var list<string> */
    public array $usedSerialisationExceptions;

    /**
     * @param list<array{Command, TokenList, SqlMode}> $falseNegatives
     * @param list<array{Command, TokenList, SqlMode}> $falsePositives
     * @param list<array{Command, TokenList, SqlMode}> $serialisationErrors
     * @param list<string> $usedSerialisationExceptions
     */
    public function __construct(
        string $path,
        int $size,
        float $time,
        int $memory,
        int $pid,
        int $statements,
        int $tokens,
        array $falseNegatives,
        array $falsePositives,
        array $serialisationErrors,
        array $usedSerialisationExceptions
    ) {
        $this->path = $path;
        $this->size = $size;
        $this->time = $time;
        $this->memory = $memory;
        $this->pid = $pid;
        $this->statements = $statements;
        $this->tokens = $tokens;
        $this->falseNegatives = $falseNegatives;
        $this->falsePositives = $falsePositives;
        $this->serialisationErrors = $serialisationErrors;
        $this->usedSerialisationExceptions = $usedSerialisationExceptions;
    }

}
