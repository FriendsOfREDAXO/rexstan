<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Command;
use SqlFtw\Sql\StatementImpl;
use Throwable;

/**
 * Returned when encountered a syntax error
 */
class InvalidCommand extends StatementImpl implements Command
{

    private Throwable $exception;

    private ?Command $command;

    /**
     * @param list<string> $commentsBefore
     */
    public function __construct(array $commentsBefore, Throwable $exception, ?Command $command = null)
    {
        $this->commentsBefore = $commentsBefore;
        $this->exception = $exception;
        $this->command = $command;
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }

    public function getCommand(): ?Command
    {
        return $this->command;
    }

    public function serialize(Formatter $formatter): string
    {
        if ($this->command !== null) {
            return 'InvalidCommand: ' . $this->command->serialize($formatter);
        } else {
            return 'Invalid command';
        }
    }

}
