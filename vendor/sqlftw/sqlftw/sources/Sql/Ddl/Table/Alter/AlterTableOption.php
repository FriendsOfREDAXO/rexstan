<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Alter;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class AlterTableOption extends SqlEnum
{

    public const ALGORITHM = Keyword::ALGORITHM;
    public const FORCE = Keyword::FORCE;
    public const LOCK = Keyword::LOCK;
    public const VALIDATION = Keyword::VALIDATION;
    public const ONLINE = Keyword::ONLINE; // NDB

}
