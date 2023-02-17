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
 * ON, OFF
 */
class OnOffLiteral implements BoolValue, KeywordLiteral
{

    private bool $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value ? 'ON' : 'OFF';
    }

    public function asBool(): ?bool
    {
        return $this->value;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->value ? 'ON' : 'OFF';
    }

}
