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

class ReplicationPrimaryKeyCheckOption extends SqlEnum
{

    public const STREAM = Keyword::STREAM;
    public const ON = Keyword::ON;
    public const OFF = Keyword::OFF;
    public const GENERATE = Keyword::GENERATE;

}
