<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Insert;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class InsertPriority extends SqlEnum
{

    public const LOW_PRIORITY = Keyword::LOW_PRIORITY;
    public const HIGH_PRIORITY = Keyword::HIGH_PRIORITY;
    public const DELAYED = Keyword::DELAYED;

}
