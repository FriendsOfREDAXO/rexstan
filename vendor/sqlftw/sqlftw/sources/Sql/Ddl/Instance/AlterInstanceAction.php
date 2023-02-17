<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Instance;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class AlterInstanceAction extends SqlEnum
{

    public const ENABLE_INNODB_REDO_LOG = Keyword::ENABLE . ' ' . Keyword::INNODB . ' ' . Keyword::REDO_LOG;
    public const DISABLE_INNODB_REDO_LOG = Keyword::DISABLE . ' ' . Keyword::INNODB . ' ' . Keyword::REDO_LOG;
    public const ROTATE_INNODB_MASTER_KEY = Keyword::ROTATE . ' ' . Keyword::INNODB . ' ' . Keyword::MASTER . ' ' . Keyword::KEY;
    public const ROTATE_BINLOG_MASTER_KEY = Keyword::ROTATE . ' ' . Keyword::BINLOG . ' ' . Keyword::MASTER . ' ' . Keyword::KEY;
    public const RELOAD_TLS = Keyword::RELOAD . ' ' . Keyword::TLS;
    public const RELOAD_KEYRING = Keyword::RELOAD . ' ' . Keyword::KEYRING;

}
