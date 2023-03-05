<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Error;

use SqlFtw\Sql\SqlEnum;

class SqlStateCategory extends SqlEnum
{

    public const SUCCESS = 'success';
    public const WARNING = 'warning';
    public const NO_DATA = 'no_data';
    public const ERROR = 'error';

}
