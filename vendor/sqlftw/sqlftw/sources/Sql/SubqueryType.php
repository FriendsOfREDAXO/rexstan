<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

class SubqueryType extends SqlEnum
{

    public const IN = Keyword::IN;
    public const ALL = Keyword::ALL;
    public const ANY = Keyword::ANY;
    public const SOME = Keyword::SOME;
    public const EXISTS = Keyword::EXISTS;
    public const EXPRESSION = Keyword::EXPRESSION;
    public const CURSOR = Keyword::CURSOR;
    public const CREATE_TABLE = Keyword::CREATE . ' ' . Keyword::TABLE;
    public const CREATE_VIEW = Keyword::CREATE . ' ' . Keyword::VIEW;
    public const INSERT = Keyword::INSERT;
    public const REPLACE = Keyword::REPLACE;
    public const WITH = Keyword::WITH;
    public const FROM = Keyword::FROM;

}
