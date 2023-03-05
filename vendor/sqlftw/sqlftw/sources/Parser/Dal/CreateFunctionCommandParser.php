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
use SqlFtw\Sql\Dal\Routine\CreateFunctionSonameCommand;
use SqlFtw\Sql\Ddl\Routine\UdfReturnDataType;
use SqlFtw\Sql\Keyword;

class CreateFunctionCommandParser
{

    /**
     * CREATE [AGGREGATE] FUNCTION function_name RETURNS {STRING|INTEGER|REAL|DECIMAL}
     *     SONAME shared_library_name
     */
    public function parseCreateFunction(TokenList $tokenList): CreateFunctionSonameCommand
    {
        $tokenList->expectKeyword(Keyword::CREATE);
        $aggregate = $tokenList->hasKeyword(Keyword::AGGREGATE);
        $tokenList->expectKeyword(Keyword::FUNCTION);

        $name = $tokenList->expectObjectIdentifier();

        $tokenList->expectKeyword(Keyword::RETURNS);
        $type = $tokenList->expectKeywordEnum(UdfReturnDataType::class);

        $tokenList->expectKeyword(Keyword::SONAME);
        $libName = $tokenList->expectNonReservedNameOrString();

        return new CreateFunctionSonameCommand($name, $libName, $type, $aggregate);
    }

}
