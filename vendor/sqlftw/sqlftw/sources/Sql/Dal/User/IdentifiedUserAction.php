<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\User;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class IdentifiedUserAction extends SqlEnum
{

    public const SET_PLUGIN = Keyword::WITH;
    public const SET_PASSWORD = Keyword::BY;
    public const SET_HASH = Keyword::AS;
    public const DISCARD_OLD_PASSWORD = Keyword::DISCARD . ' ' . Keyword::OLD . ' ' . Keyword::PASSWORD;

}
