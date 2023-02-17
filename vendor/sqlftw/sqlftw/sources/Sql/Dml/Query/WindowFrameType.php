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

class WindowFrameType extends SqlEnum
{

    public const CURRENT_ROW = Keyword::CURRENT . ' ' . Keyword::ROW;
    public const UNBOUNDED_PRECEDING = Keyword::UNBOUNDED . ' ' . Keyword::PRECEDING;
    public const UNBOUNDED_FOLLOWING = Keyword::UNBOUNDED . ' ' . Keyword::FOLLOWING;
    public const PRECEDING = Keyword::PRECEDING;
    public const FOLLOWING = Keyword::FOLLOWING;

}
