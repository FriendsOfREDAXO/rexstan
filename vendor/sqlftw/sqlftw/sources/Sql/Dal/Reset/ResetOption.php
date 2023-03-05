<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Reset;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class ResetOption extends SqlEnum
{

    public const MASTER = Keyword::MASTER;
    public const REPLICA = Keyword::REPLICA;
    public const SLAVE = Keyword::SLAVE;
    public const QUERY_CACHE = Keyword::QUERY . ' ' . Keyword::CACHE;

}
