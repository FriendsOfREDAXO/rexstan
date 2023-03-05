<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Formatter\Formatter;

/**
 * used in: GET_FORMAT({DATE|TIME|DATETIME}, {'EUR'|'USA'|'JIS'|'ISO'|'INTERNAL'})
 */
class TimeTypeLiteral implements KeywordLiteral
{

    /** @var 'DATE'|'TIME'|'DATETIME' */
    private string $value;

    /**
     * @param 'DATE'|'TIME'|'DATETIME' $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return 'DATE'|'TIME'|'DATETIME'
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->value;
    }

}
