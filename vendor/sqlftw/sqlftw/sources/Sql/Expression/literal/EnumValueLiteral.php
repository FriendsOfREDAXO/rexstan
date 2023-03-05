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
use function preg_match;

/**
 * Value of ENUM-typed system variables
 */
class EnumValueLiteral implements Literal
{

    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function serialize(Formatter $formatter): string
    {
        // things like AES-128-ECB etc.
        if (preg_match('~^[A-Za-z\d_]+$~', $this->value) !== 1) {
            return $formatter->formatString($this->value);
        }

        return $this->value;
    }

}
