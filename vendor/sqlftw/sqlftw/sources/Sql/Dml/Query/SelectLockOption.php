<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Query;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class SelectLockOption extends SqlEnum
{

    public const FOR_UPDATE = Keyword::FOR . ' ' . Keyword::UPDATE;
    public const FOR_SHARE = Keyword::FOR . ' ' . Keyword::SHARE;
    public const LOCK_IN_SHARE_MODE = Keyword::LOCK . ' ' . Keyword::IN . ' ' . Keyword::SHARE . ' ' . Keyword::MODE;

}
