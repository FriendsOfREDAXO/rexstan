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

class PartitionOption extends SqlEnum
{

    public const ENGINE = Keyword::ENGINE;
    public const COMMENT = Keyword::COMMENT;
    public const DATA_DIRECTORY = Keyword::DATA . ' ' . Keyword::DIRECTORY;
    public const INDEX_DIRECTORY = Keyword::INDEX . ' ' . Keyword::DIRECTORY;
    public const MAX_ROWS = Keyword::MAX_ROWS;
    public const MIN_ROWS = Keyword::MIN_ROWS;
    public const TABLESPACE = Keyword::TABLESPACE;
    public const NODEGROUP = Keyword::NODEGROUP;

}
