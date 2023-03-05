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

class UserTlsOptionType extends SqlEnum
{

    public const SSL = Keyword::SSL;
    public const X509 = Keyword::X509;
    public const CIPHER = Keyword::CIPHER;
    public const ISSUER = Keyword::ISSUER;
    public const SUBJECT = Keyword::SUBJECT;

}
