<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dal;

use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Dal\Reset\ResetCommand;
use SqlFtw\Sql\Dal\Reset\ResetOption;
use SqlFtw\Sql\Keyword;

class ResetCommandParser
{

    /**
     * RESET reset_option [, reset_option] ...
     *
     * reset_option:
     *     MASTER
     *   | REPLICA
     *   | QUERY CACHE (<= 5.7)
     *   | SLAVE
     */
    public function parseReset(TokenList $tokenList): ResetCommand
    {
        $tokenList->expectKeyword(Keyword::RESET);
        $options = [];
        do {
            $options[] = $tokenList->expectMultiKeywordsEnum(ResetOption::class);
        } while ($tokenList->hasSymbol(','));

        return new ResetCommand($options);
    }

}
