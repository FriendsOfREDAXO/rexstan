<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Partition;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class PartitioningConditionType extends SqlEnum
{

    public const HASH = Keyword::HASH;
    public const LINEAR_HASH = Keyword::LINEAR . ' ' . Keyword::HASH;
    public const KEY = Keyword::KEY;
    public const LINEAR_KEY = Keyword::LINEAR . ' ' . Keyword::KEY;
    public const RANGE = Keyword::RANGE;
    public const LIST = Keyword::LIST;

}
