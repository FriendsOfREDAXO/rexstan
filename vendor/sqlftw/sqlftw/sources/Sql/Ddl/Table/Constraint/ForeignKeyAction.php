<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Constraint;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class ForeignKeyAction extends SqlEnum
{

    public const RESTRICT = Keyword::RESTRICT;
    public const CASCADE = Keyword::CASCADE;
    public const SET_NULL = Keyword::SET . ' ' . Keyword::NULL;
    public const NO_ACTION = Keyword::NO . ' ' . Keyword::ACTION;
    public const SET_DEFAULT = Keyword::SET . ' ' . Keyword::DEFAULT;

}
