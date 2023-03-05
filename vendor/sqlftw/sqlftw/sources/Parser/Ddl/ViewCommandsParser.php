<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Ddl;

use SqlFtw\Parser\Dml\QueryParser;
use SqlFtw\Parser\ExpressionParser;
use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Ddl\SqlSecurity;
use SqlFtw\Sql\Ddl\UserExpression;
use SqlFtw\Sql\Ddl\View\AlterViewCommand;
use SqlFtw\Sql\Ddl\View\CreateViewCommand;
use SqlFtw\Sql\Ddl\View\DropViewCommand;
use SqlFtw\Sql\Ddl\View\DropViewOption;
use SqlFtw\Sql\Ddl\View\ViewAlgorithm;
use SqlFtw\Sql\Ddl\View\ViewCheckOption;
use SqlFtw\Sql\Dml\Query\Query;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SubqueryType;

class ViewCommandsParser
{

    private ExpressionParser $expressionParser;

    private QueryParser $queryParser;

    public function __construct(ExpressionParser $expressionParser, QueryParser $queryParser)
    {
        $this->expressionParser = $expressionParser;
        $this->queryParser = $queryParser;
    }

    /**
     * ALTER
     *     [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}]
     *     [DEFINER = { user | CURRENT_USER }]
     *     [SQL SECURITY { DEFINER | INVOKER }]
     *     VIEW view_name [(column_list)]
     *     AS select_statement
     *     [WITH [CASCADED | LOCAL] CHECK OPTION]
     */
    public function parseAlterView(TokenList $tokenList): AlterViewCommand
    {
        $tokenList->expectKeyword(Keyword::ALTER);
        $params = $this->parseViewDefinition($tokenList);

        return new AlterViewCommand(...$params);
    }

    /**
     * CREATE
     *     [OR REPLACE]
     *     [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}]
     *     [DEFINER = { user | CURRENT_USER }]
     *     [SQL SECURITY { DEFINER | INVOKER }]
     *     VIEW view_name [(column_list)]
     *     AS select_statement
     *     [WITH [CASCADED | LOCAL] CHECK OPTION]
     */
    public function parseCreateView(TokenList $tokenList): CreateViewCommand
    {
        $tokenList->expectKeyword(Keyword::CREATE);
        $orReplace = $tokenList->hasKeywords(Keyword::OR, Keyword::REPLACE);

        $params = $this->parseViewDefinition($tokenList) + [$orReplace];
        $params[] = $orReplace;

        return new CreateViewCommand(...$params);
    }

    /**
     * @return array{ObjectIdentifier, Query, non-empty-list<string>|null, UserExpression|null, SqlSecurity|null, ViewAlgorithm|null, ViewCheckOption|null}
     */
    private function parseViewDefinition(TokenList $tokenList): array
    {
        $algorithm = $definer = $sqlSecurity = $checkOption = null;
        if ($tokenList->hasKeyword(Keyword::ALGORITHM)) {
            $tokenList->expectOperator(Operator::EQUAL);
            $algorithm = $tokenList->expectKeywordEnum(ViewAlgorithm::class);
        }
        if ($tokenList->hasKeyword(Keyword::DEFINER)) {
            $tokenList->expectOperator(Operator::EQUAL);
            $definer = $this->expressionParser->parseUserExpression($tokenList);
        }
        if ($tokenList->hasKeyword(Keyword::SQL)) {
            $tokenList->expectKeyword(Keyword::SECURITY);
            $sqlSecurity = $tokenList->expectKeywordEnum(SqlSecurity::class);
        }

        $tokenList->expectKeyword(Keyword::VIEW);
        $name = $tokenList->expectObjectIdentifier();

        $columns = null;
        if ($tokenList->hasSymbol('(')) {
            $columns = [];
            do {
                $columns[] = $tokenList->expectName(EntityType::COLUMN);
            } while ($tokenList->hasSymbol(','));

            $tokenList->expectSymbol(')');
        }

        $tokenList->expectKeyword(Keyword::AS);

        $tokenList->startSubquery(SubqueryType::CREATE_VIEW);
        $body = $this->queryParser->parseQuery($tokenList);
        $tokenList->endSubquery();

        if ($tokenList->hasKeyword(Keyword::WITH)) {
            $checkOption = $tokenList->getMultiKeywordsEnum(ViewCheckOption::class);
        }

        return [$name, $body, $columns, $definer, $sqlSecurity, $algorithm, $checkOption];
    }

    /**
     * DROP VIEW [IF EXISTS]
     *     view_name [, view_name] ...
     *     [RESTRICT | CASCADE]
     */
    public function parseDropView(TokenList $tokenList): DropViewCommand
    {
        $tokenList->expectKeywords(Keyword::DROP, Keyword::VIEW);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);

        $names = [];
        do {
            $names[] = $tokenList->expectObjectIdentifier();
        } while ($tokenList->hasSymbol(','));

        $option = $tokenList->getAnyKeyword(Keyword::RESTRICT, Keyword::CASCADE);
        if ($option !== null) {
            $option = new DropViewOption($option);
        }

        return new DropViewCommand($names, $ifExists, $option);
    }

}
