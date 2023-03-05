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
 * Value of named argument without a value
 *
 * e.g. value of "BOTH" in "TRIM(BOTH FROM foo)"  -> [BOTH => NoValue, FROM => foo]
 *   as compared to "TRIM(BOTH "\r\n" FROM foo)") -> [BOTH => "\r\n", FROM => foo]
 */
class NoValue implements ArgumentNode
{

    public function serialize(Formatter $formatter): string
    {
        return '';
    }

}
