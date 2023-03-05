<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Table;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class CheckTableOption extends SqlEnum
{

    public const FOR_UPGRADE = Keyword::FOR . ' ' . Keyword::UPGRADE;
    public const QUICK = Keyword::QUICK;
    public const FAST = Keyword::FAST;
    public const MEDIUM = Keyword::MEDIUM;
    public const EXTENDED = Keyword::EXTENDED;
    public const CHANGED = Keyword::CHANGED;

}
