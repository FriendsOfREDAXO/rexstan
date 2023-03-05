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

class RolesSpecificationType extends SqlEnum
{

    public const DEFAULT = Keyword::DEFAULT;
    public const NONE = Keyword::NONE;
    public const ALL = Keyword::ALL;
    public const ALL_EXCEPT = Keyword::ALL . ' ' . Keyword::EXCEPT;
    public const LIST = '';

}
