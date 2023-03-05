<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Show;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class ShowEngineOption extends SqlEnum
{

    public const STATUS = Keyword::STATUS;
    public const MUTEX = Keyword::MUTEX;
    public const LOGS = Keyword::LOGS;

}
