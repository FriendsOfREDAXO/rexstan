<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dml;

use SqlFtw\Parser\ExpressionParser;
use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Dml\DoCommand\DoCommand;
use SqlFtw\Sql\Expression\AliasExpression;
use SqlFtw\Sql\Keyword;

class DoCommandsParser
{

    private ExpressionParser $expressionParser;

    public function __construct(ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }

    /**
     * DO expr [, expr] ...
     */
    public function parseDo(TokenList $tokenList): DoCommand
    {
        $tokenList->expectKeyword(Keyword::DO);

        $expressions = [];
        do {
            $expression = $this->expressionParser->parseAssignExpression($tokenList);
            $alias = $this->expressionParser->parseAlias($tokenList);
            if ($alias !== null) {
                $expression = new AliasExpression($expression, $alias);
            }

            $expressions[] = $expression;
        } while ($tokenList->hasSymbol(','));

        return new DoCommand($expressions);
    }

}
