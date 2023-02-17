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
use SqlFtw\Parser\TokenType;
use SqlFtw\Sql\Dml\Utility\DelimiterCommand;
use SqlFtw\Sql\Keyword;

class DelimiterCommandParser
{

    public function parseDelimiter(TokenList $tokenList): DelimiterCommand
    {
        $tokenList->expectKeyword(Keyword::DELIMITER);
        $delimiter = $tokenList->expect(TokenType::DELIMITER_DEFINITION)->value;

        // Parser uses delimiter token to detect if the command has been parsed completely,
        // but there might be no delimiter after delimiter definition
        $tokenList->rewind(-1);

        return new DelimiterCommand($delimiter);
    }

}
