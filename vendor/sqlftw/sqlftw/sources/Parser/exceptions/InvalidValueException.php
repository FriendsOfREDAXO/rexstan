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
use function sprintf;

class InvalidValueException extends ParserException
{

    public string $expectedType;

    public function __construct(string $expectedType, TokenList $tokenList, ?Throwable $previous = null)
    {
        $value = $tokenList->getLast();

        $context = self::formatContext($tokenList);

        parent::__construct(
            sprintf("Invalid value $value->original for type $expectedType at position %d in:\n%s", $tokenList->getPosition(), $context),
            $tokenList,
            $previous
        );

        $this->expectedType = $expectedType;
    }

}
