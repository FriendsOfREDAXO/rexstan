<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

/**
 * System variable scope: GLOBAL, SESSION, PERSIST, PERSIST_ONLY
 */
class Scope extends SqlEnum
{

    public const GLOBAL = Keyword::GLOBAL;
    public const SESSION = Keyword::SESSION;
    public const PERSIST = Keyword::PERSIST;
    public const PERSIST_ONLY = Keyword::PERSIST_ONLY;

}
