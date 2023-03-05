<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dml;

use Countable;
use SqlFtw\Parser\ExpressionParser;
use SqlFtw\Parser\TokenList;
use SqlFtw\Parser\TokenType;
use SqlFtw\Sql\Assignment;
use SqlFtw\Sql\Dml\Update\UpdateCommand;
use SqlFtw\Sql\Dml\WithClause;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Keyword;

class UpdateCommandParser
{

    private ExpressionParser $expressionParser;

    private TableReferenceParser $tableReferenceParser;

    private OptimizerHintParser $optimizerHintParser;

    public function __construct(
        ExpressionParser $expressionParser,
        TableReferenceParser $tableReferenceParser,
        OptimizerHintParser $optimizerHintParser
    )
    {
        $this->expressionParser = $expressionParser;
        $this->tableReferenceParser = $tableReferenceParser;
        $this->optimizerHintParser = $optimizerHintParser;
    }

    /**
     * UPDATE [LOW_PRIORITY] [IGNORE] table_reference
     *     SET col_name1={expr1|DEFAULT} [, col_name2={expr2|DEFAULT}] ...
     *     [WHERE where_condition]
     *     [ORDER BY ...]
     *     [LIMIT row_count]
     *
     * UPDATE [LOW_PRIORITY] [IGNORE] table_references
     *     SET col_name1={expr1|DEFAULT} [, col_name2={expr2|DEFAULT}] ...
     *     [WHERE where_condition]
     */
    public function parseUpdate(TokenList $tokenList, ?WithClause $with = null): UpdateCommand
    {
        $tokenList->expectKeyword(Keyword::UPDATE);

        $optimizerHints = null;
        if ($tokenList->has(TokenType::OPTIMIZER_HINT_START)) {
            $optimizerHints = $this->optimizerHintParser->parseHints($tokenList->rewind(-1));
        }

        $lowPriority = $tokenList->hasKeyword(Keyword::LOW_PRIORITY);
        $ignore = $tokenList->hasKeyword(Keyword::IGNORE);

        $tableReferences = $this->tableReferenceParser->parseTableReferences($tokenList);

        $tokenList->expectKeyword(Keyword::SET);
        $values = [];
        do {
            $column = $this->expressionParser->parseColumnIdentifier($tokenList);

            $operator = $tokenList->expectAnyOperator(Operator::EQUAL, Operator::ASSIGN);

            $value = $this->expressionParser->parseAssignExpression($tokenList);
            $values[] = new Assignment($column, $value, $operator);
        } while ($tokenList->hasSymbol(','));

        $where = null;
        if ($tokenList->hasKeyword(Keyword::WHERE)) {
            $where = $this->expressionParser->parseExpression($tokenList);
        }

        $orderBy = $limit = null;
        if (!$tableReferences instanceof Countable || $tableReferences->count() === 1) {
            if ($tokenList->hasKeywords(Keyword::ORDER, Keyword::BY)) {
                $orderBy = $this->expressionParser->parseOrderBy($tokenList);
            }
            if ($tokenList->hasKeyword(Keyword::LIMIT)) {
                $limit = $this->expressionParser->parseLimitOrOffsetValue($tokenList);
            }
        }

        return new UpdateCommand($tableReferences, $values, $where, $with, $orderBy, $limit, $ignore, $lowPriority, $optimizerHints);
    }

}
