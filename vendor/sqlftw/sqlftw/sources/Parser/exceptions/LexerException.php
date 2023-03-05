<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser;

use Throwable;

class LexerException extends ParsingException
{

    private int $position;

    private string $input;

    public function __construct(string $message, int $position, string $input, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);

        $this->position = $position;
        $this->input = $input;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getInput(): string
    {
        return $this->input;
    }

}
