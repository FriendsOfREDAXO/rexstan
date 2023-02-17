<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dal;

use SqlFtw\Parser\InvalidValueException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Dal\Binlog\BinlogCommand;
use SqlFtw\Sql\Keyword;

class BinlogCommandParser
{

    /**
     * BINLOG 'str'
     */
    public function parseBinlog(TokenList $tokenList): BinlogCommand
    {
        $tokenList->expectKeyword(Keyword::BINLOG);
        $value = $tokenList->expectString();
        if ($value === '') {
            throw new InvalidValueException('base64 encoded binlog command', $tokenList);
        }

        return new BinlogCommand($value);
    }

}
