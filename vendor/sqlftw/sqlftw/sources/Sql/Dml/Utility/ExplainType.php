<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Utility;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class ExplainType extends SqlEnum
{

    public const EXTENDED = Keyword::EXTENDED;
    public const PARTITIONS = Keyword::PARTITIONS;
    public const FORMAT_TRADITIONAL = Keyword::FORMAT . '=' . Keyword::TRADITIONAL;
    public const FORMAT_JSON = Keyword::FORMAT . '=' . Keyword::JSON;
    public const FORMAT_TREE = Keyword::FORMAT . '=TREE';
    public const ANALYZE = Keyword::ANALYZE;

}
