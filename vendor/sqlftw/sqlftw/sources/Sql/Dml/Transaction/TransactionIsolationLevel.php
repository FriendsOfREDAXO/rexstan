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

class TransactionIsolationLevel extends SqlEnum
{

    public const REPEATABLE_READ = Keyword::REPEATABLE . ' ' . Keyword::READ;
    public const READ_COMMITTED = Keyword::READ . ' ' . Keyword::COMMITTED;
    public const READ_UNCOMMITTED = Keyword::READ . ' ' . Keyword::UNCOMMITTED;
    public const SERIALIZABLE = Keyword::SERIALIZABLE;

}
