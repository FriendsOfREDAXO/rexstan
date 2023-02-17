<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Transaction;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class LockTableType extends SqlEnum
{

    public const READ_LOCAL = Keyword::READ . ' ' . Keyword::LOCAL;
    public const READ = Keyword::READ;
    public const WRITE = Keyword::WRITE;
    public const LOW_PRIORITY_WRITE = Keyword::LOW_PRIORITY . ' ' . Keyword::WRITE;

}
