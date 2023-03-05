<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Replication;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class ReplicationThreadType extends SqlEnum
{

    public const IO_THREAD = Keyword::IO_THREAD;
    public const SQL_THREAD = Keyword::SQL_THREAD;

}
