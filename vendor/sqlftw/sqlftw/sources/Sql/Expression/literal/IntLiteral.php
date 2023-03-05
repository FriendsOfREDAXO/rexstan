<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

/**
 * e.g. -123
 */
class IntLiteral extends NumericLiteral implements IntValue
{

    public function asNumber(): int
    {
        return (int) $this->value;
    }

    public function asInt(): int
    {
        return (int) $this->value;
    }

}
