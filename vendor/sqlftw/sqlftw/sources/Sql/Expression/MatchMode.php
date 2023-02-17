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

class MatchMode extends SqlEnum
{

    public const NATURAL_LANGUAGE_MODE = Keyword::NATURAL . ' ' . Keyword::LANGUAGE . ' ' . Keyword::MODE;
    public const BOOLEAN_MODE = Keyword::BOOLEAN . ' ' . Keyword::MODE;

}
