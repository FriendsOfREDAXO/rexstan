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
 * e.g. -123.456
 */
class NumericLiteral implements NumericValue
{

    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return int|float
     */
    public function asNumber() // @phpstan-ignore-line "never returns int" because of descendants
    {
        return (float) $this->value;
    }

    public function isNegative(): bool
    {
        return ((float) $this->value) < 0.0;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->value;
    }

}
