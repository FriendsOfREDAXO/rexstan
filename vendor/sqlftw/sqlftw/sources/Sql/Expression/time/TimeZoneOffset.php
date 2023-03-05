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
use SqlFtw\Sql\InvalidDefinitionException;
use function preg_match;

/**
 * e.g. +01:00
 */
class TimeZoneOffset implements TimeZone
{

    private string $value;

    public function __construct(string $value)
    {
        if (preg_match('~[+-](?:[01]\d|2[0-3]):[0-5]\d~', $value) !== 1) {
            throw new InvalidDefinitionException("Invalid time zone offset $value.");
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function serialize(Formatter $formatter): string
    {
        return "'$this->value'";
    }

}
