<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Routine;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class UdfReturnDataType extends SqlEnum
{

    public const STRING = Keyword::STRING;
    public const INTEGER = Keyword::INTEGER;
    public const REAL = Keyword::REAL;
    public const DECIMAL = Keyword::DECIMAL;

    // aliases
    // todo: moar aliases
    public const INT = Keyword::INT;

}
