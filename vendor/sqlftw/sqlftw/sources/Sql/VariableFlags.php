<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

class VariableFlags
{

    public const NONE = 0;
    public const SET_VAR = 1;
    public const NULLABLE = 2;
    public const CLAMP = 4; // restricts given out-of-bounds value (with or without warning)
    public const CLAMP_MIN = 8; // restricts value to min (with or without warning)
    public const NO_DEFAULT = 16; // cannot set to DEFAULT
    public const NO_PERSIST = 32; // https://dev.mysql.com/doc/refman/8.0/en/nonpersistible-system-variables.html (terribly incomplete list)
    public const NON_EMPTY = 64; // applies to SET and CHAR
    public const NON_ZERO = 128; // applies to SIGNED and UNSIGNED
    public const SESSION_READONLY = 256; // (GLOBAL is writeable)

}
