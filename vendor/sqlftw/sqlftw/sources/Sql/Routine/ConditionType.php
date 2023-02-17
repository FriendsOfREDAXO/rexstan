<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Routine;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class ConditionType extends SqlEnum
{

    public const ERROR = 'ERROR'; // special type. does not indicate usage of the actual usage of ERROR keyword
    public const CONDITION = 'CONDITION'; // special type. does not indicate usage of the actual usage of CONDITION keyword
    public const SQL_STATE = Keyword::SQLSTATE;
    public const SQL_WARNING = Keyword::SQLWARNING;
    public const SQL_EXCEPTION = Keyword::SQLEXCEPTION;
    public const NOT_FOUND = Keyword::NOT . ' ' . Keyword::FOUND;

}
