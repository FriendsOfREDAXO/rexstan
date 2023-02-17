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
use SqlFtw\Sql\Dal\Shutdown\ShutdownCommand;
use SqlFtw\Sql\Keyword;

class ShutdownCommandParser
{

    /**
     * SHUTDOWN
     */
    public function parseShutdown(TokenList $tokenList): ShutdownCommand
    {
        $tokenList->expectKeyword(Keyword::SHUTDOWN);

        return new ShutdownCommand();
    }

}
