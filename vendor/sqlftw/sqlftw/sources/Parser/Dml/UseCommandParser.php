<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dml;

use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Dml\Utility\UseCommand;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Keyword;

class UseCommandParser
{

    /**
     * USE db_name
     */
    public function parseUse(TokenList $tokenList): UseCommand
    {
        $tokenList->expectKeyword(Keyword::USE);
        $schema = $tokenList->expectName(EntityType::SCHEMA);

        return new UseCommand($schema);
    }

}
