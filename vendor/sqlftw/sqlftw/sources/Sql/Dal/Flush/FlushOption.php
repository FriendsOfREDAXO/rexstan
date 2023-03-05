<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Flush;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class FlushOption extends SqlEnum
{

    public const BINARY_LOGS = Keyword::BINARY . ' ' . Keyword::LOGS;
    public const DES_KEY_FILE = Keyword::DES_KEY_FILE;
    public const ENGINE_LOGS = Keyword::ENGINE . ' ' . Keyword::LOGS;
    public const ERROR_LOGS = Keyword::ERROR . ' ' . Keyword::LOGS;
    public const GENERAL_LOGS = Keyword::GENERAL . ' ' . Keyword::LOGS;
    public const HOSTS = Keyword::HOSTS;
    public const LOGS = Keyword::LOGS;
    public const OPTIMIZER_COSTS = Keyword::OPTIMIZER_COSTS;
    public const PRIVILEGES = Keyword::PRIVILEGES;
    public const QUERY_CACHE = Keyword::QUERY . ' ' . Keyword::CACHE;
    public const RELAY_LOGS = Keyword::RELAY . ' ' . Keyword::LOGS;
    public const SLOW_LOGS = Keyword::SLOW . ' ' . Keyword::LOGS;
    public const STATUS = Keyword::STATUS;
    public const USER_RESOURCES = Keyword::USER_RESOURCES;

}
