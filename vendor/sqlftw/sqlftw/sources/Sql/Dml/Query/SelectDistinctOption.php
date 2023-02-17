<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Query;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class SelectDistinctOption extends SqlEnum
{

    public const ALL = Keyword::ALL;
    public const DISTINCT = Keyword::DISTINCT;
    public const DISTINCT_ROW = Keyword::DISTINCTROW;

}
