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

class InOutParamFlag extends SqlEnum
{

    public const IN = Keyword::IN;
    public const OUT = Keyword::OUT;
    public const INOUT = Keyword::INOUT;

}
