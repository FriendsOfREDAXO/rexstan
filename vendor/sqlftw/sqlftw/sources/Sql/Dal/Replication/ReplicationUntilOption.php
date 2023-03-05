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

class ReplicationUntilOption extends SqlEnum
{

    public const SQL_AFTER_MTS_GAPS = Keyword::SQL_AFTER_MTS_GAPS;
    public const SQL_BEFORE_GTIDS = Keyword::SQL_BEFORE_GTIDS;
    public const SQL_AFTER_GTIDS = Keyword::SQL_AFTER_GTIDS;
    public const MASTER_LOG_FILE = Keyword::MASTER_LOG_FILE;
    public const MASTER_LOG_POS = Keyword::MASTER_LOG_POS;
    public const RELAY_LOG_FILE = Keyword::RELAY_LOG_FILE;
    public const RELAY_LOG_POS = Keyword::RELAY_LOG_POS;

}
