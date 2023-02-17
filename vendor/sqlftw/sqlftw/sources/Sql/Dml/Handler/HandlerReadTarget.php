<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Handler;

use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;
use function in_array;

class HandlerReadTarget extends SqlEnum
{

    public const FIRST = Keyword::FIRST;
    public const LAST = Keyword::LAST;
    public const NEXT = Keyword::NEXT;
    public const PREV = Keyword::PREV;

    public const EQUAL = Operator::EQUAL;
    public const LESS_OR_EQUAL = Operator::LESS_OR_EQUAL;
    public const GREATER_OR_EQUAL = Operator::GREATER_OR_EQUAL;
    public const LESS = Operator::LESS;
    public const GREATER = Operator::GREATER;

    /**
     * @return list<string>
     */
    public static function getKeywords(): array
    {
        return [self::FIRST, self::LAST, self::NEXT, self::PREV];
    }

    /**
     * @return list<string>
     */
    public static function getOperators(): array
    {
        return [self::EQUAL, self::LESS_OR_EQUAL, self::GREATER_OR_EQUAL, self::LESS, self::GREATER];
    }

    public function isKeyword(): bool
    {
        return in_array($this->getValue(), self::getKeywords(), true);
    }

    public function isOperator(): bool
    {
        return in_array($this->getValue(), self::getOperators(), true);
    }

}
