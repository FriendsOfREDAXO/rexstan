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
use SqlFtw\Parser\TokenType;
use SqlFtw\Sql\Assignment;
use SqlFtw\Sql\Dml\Insert\InsertCommand;
use SqlFtw\Sql\Dml\Insert\InsertPriority;
use SqlFtw\Sql\Dml\Insert\InsertSelectCommand;
use SqlFtw\Sql\Dml\Insert\InsertSetCommand;
use SqlFtw\Sql\Dml\Insert\InsertValuesCommand;
use SqlFtw\Sql\Dml\Insert\OnDuplicateKeyActions;
use SqlFtw\Sql\Dml\Insert\ReplaceCommand;
use SqlFtw\Sql\Dml\Insert\ReplaceSelectCommand;
use SqlFtw\Sql\Dml\Insert\ReplaceSetCommand;
use SqlFtw\Sql\Dml\Insert\ReplaceValuesCommand;
use SqlFtw\Sql\Dml\Query\ParenthesizedQueryExpression;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\ColumnIdentifier;
use SqlFtw\Sql\Expression\ExpressionNode;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SubqueryType;

class InsertCommandParser
{

    private ExpressionParser $expressionParser;

    private QueryParser $queryParser;

    private OptimizerHintParser $optimizerHintParser;

    public function __construct(
        ExpressionParser $expressionParser,
        QueryParser $queryParser,
        OptimizerHintParser $optimizerHintParser
    )
    {
        $this->expressionParser = $expressionParser;
        $this->queryParser = $queryParser;
        $this->optimizerHintParser = $optimizerHintParser;
    }

    /**
     * INSERT [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
     *     [INTO] tbl_name
     *     [PARTITION (partition_name, ...)]
     *     [(col_name, ...)]
     *     { {VALUES | VALUE} (value_list) [, (value_list)] ... }
     *     [AS row_alias[(col_alias [, col_alias] ...)]]
     *     [ON DUPLICATE KEY UPDATE assignment_list]
     *
     * INSERT [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
     *     [INTO] tbl_name
     *     [PARTITION (partition_name, ...)]
     *     SET assignment_list
     *     [AS row_alias[(col_alias [, col_alias] ...)]]
     *     [ON DUPLICATE KEY UPDATE assignment_list]
     *
     * INSERT [LOW_PRIORITY | HIGH_PRIORITY] [IGNORE]
     *     [INTO] tbl_name
     *     [PARTITION (partition_name, ...)]
     *     [(col_name, ...)]
     *     { SELECT ...
     *       | TABLE table_name
     *       | VALUES row_constructor_list
     *     }
     *     [ON DUPLICATE KEY UPDATE assignment_list]
     *
     * value_list:
     *     value [, value] ...
     *
     * value:
     *     {expr | DEFAULT}
     *
     * row_constructor_list:
     *     ROW(value_list)[, ROW(value_list)][, ...]
     *
     * assignment_list:
     *     assignment [, assignment] ...
     *
     * assignment:
     *     col_name =
     *         value
     *       | [row_alias.]col_name
     *       | [tbl_name.]col_name
     *       | [row_alias.]col_alias
     */
    public function parseInsert(TokenList $tokenList): InsertCommand
    {
        $tokenList->expectKeyword(Keyword::INSERT);

        $optimizerHints = null;
        if ($tokenList->has(TokenType::OPTIMIZER_HINT_START)) {
            $optimizerHints = $this->optimizerHintParser->parseHints($tokenList->rewind(-1));
        }

        $priority = $tokenList->getKeywordEnum(InsertPriority::class);
        $ignore = $tokenList->hasKeyword(Keyword::IGNORE);
        $tokenList->passKeyword(Keyword::INTO);
        $table = $tokenList->expectObjectIdentifier();

        $partitions = $this->parsePartitionsList($tokenList);
        $columns = $this->parseColumnList($tokenList);

        $position = $tokenList->getPosition();
        if ($tokenList->hasKeyword(Keyword::VALUE)
            || ($tokenList->hasKeyword(Keyword::VALUES) && !$tokenList->hasKeyword(Keyword::ROW))
        ) {
            $rows = $this->parseRows($tokenList);

            $alias = $columnAliases = null;
            if ($tokenList->hasKeyword(Keyword::AS)) {
                $alias = $tokenList->expectName(EntityType::ALIAS);
                if ($tokenList->hasSymbol('(')) {
                    do {
                        $columnAliases[] = $tokenList->expectName(EntityType::ALIAS);
                    } while ($tokenList->hasSymbol(','));
                    $tokenList->expectSymbol(')');
                }
            }

            $update = $this->parseOnDuplicateKeyUpdate($tokenList);

            return new InsertValuesCommand($table, $rows, $columns, $alias, $columnAliases, $partitions, $priority, $ignore, $optimizerHints, $update);
        }
        $tokenList->rewind($position);

        if ($tokenList->hasKeyword(Keyword::SET)) {
            if ($columns !== null) {
                $tokenList->rewind(-1);
                $tokenList->missingAnyKeyword(Keyword::VALUE, Keyword::VALUES, Keyword::SELECT, Keyword::WITH);
            }
            $assignments = $this->parseAssignments($tokenList);

            $alias = null;
            if ($tokenList->hasKeyword(Keyword::AS)) {
                $alias = $tokenList->expectName(EntityType::ALIAS);
            }

            $update = $this->parseOnDuplicateKeyUpdate($tokenList);

            return new InsertSetCommand($table, $assignments, $alias, $partitions, $priority, $ignore, $optimizerHints, $update);
        }

        $tokenList->startSubquery(SubqueryType::INSERT);
        $query = $this->queryParser->parseQuery($tokenList);
        $tokenList->endSubquery();

        $update = $this->parseOnDuplicateKeyUpdate($tokenList);

        return new InsertSelectCommand($table, $query, $columns, $partitions, $priority, $ignore, $optimizerHints, $update);
    }

    /**
     * REPLACE [LOW_PRIORITY | DELAYED]
     *     [INTO] tbl_name
     *     [PARTITION (partition_name, ...)]
     *     [(col_name, ...)]
     *     {VALUES | VALUE} ({expr | DEFAULT}, ...), (...), ...
     *
     * REPLACE [LOW_PRIORITY | DELAYED]
     *     [INTO] tbl_name
     *     [PARTITION (partition_name, ...)]
     *     SET col_name={expr | DEFAULT}, ...
     *
     * REPLACE [LOW_PRIORITY | DELAYED]
     *     [INTO] tbl_name
     *     [PARTITION (partition_name, ...)]
     *     [(col_name, ...)]
     *     SELECT ...
     */
    public function parseReplace(TokenList $tokenList): ReplaceCommand
    {
        $tokenList->expectKeyword(Keyword::REPLACE);

        $optimizerHints = null;
        if ($tokenList->has(TokenType::OPTIMIZER_HINT_START)) {
            $optimizerHints = $this->optimizerHintParser->parseHints($tokenList->rewind(-1));
        }

        $priority = $tokenList->getKeywordEnum(InsertPriority::class);
        $ignore = $tokenList->hasKeyword(Keyword::IGNORE);
        $tokenList->passKeyword(Keyword::INTO);
        $table = $tokenList->expectObjectIdentifier();

        $partitions = $this->parsePartitionsList($tokenList);
        $columns = $this->parseColumnList($tokenList);

        if ($tokenList->hasSymbol('(')) {
            $tokenList->expectAnyKeyword(Keyword::SELECT, Keyword::WITH, Keyword::TABLE, Keyword::VALUES);
            $tokenList->startSubquery(SubqueryType::REPLACE);
            $query = $this->queryParser->parseQuery($tokenList->rewind(-1));
            $tokenList->endSubquery();
            $tokenList->expectSymbol(')');
            $query = new ParenthesizedQueryExpression($query);

            return new ReplaceSelectCommand($table, $query, $columns, $partitions, $priority, $ignore, $optimizerHints);
        } elseif ($tokenList->hasAnyKeyword(Keyword::SELECT, Keyword::WITH, Keyword::TABLE)) { // no Keyword::VALUES!
            $tokenList->startSubquery(SubqueryType::REPLACE);
            $query = $this->queryParser->parseQuery($tokenList->rewind(-1));
            $tokenList->endSubquery();

            return new ReplaceSelectCommand($table, $query, $columns, $partitions, $priority, $ignore, $optimizerHints);
        } elseif ($tokenList->hasKeyword(Keyword::SET)) {
            $assignments = $this->parseAssignments($tokenList);

            return new ReplaceSetCommand($table, $assignments, $columns, $partitions, $priority, $ignore, $optimizerHints);
        } else {
            $tokenList->expectAnyKeyword(Keyword::VALUE, Keyword::VALUES);
            $rows = $this->parseRows($tokenList);

            return new ReplaceValuesCommand($table, $rows, $columns, $partitions, $priority, $ignore, $optimizerHints);
        }
    }

    /**
     * @return non-empty-list<string>|null
     */
    private function parsePartitionsList(TokenList $tokenList): ?array
    {
        $partitions = null;
        if ($tokenList->hasKeyword(Keyword::PARTITION)) {
            $tokenList->expectSymbol('(');
            $partitions = [];
            do {
                $partitions[] = $tokenList->expectName(EntityType::PARTITION);
            } while ($tokenList->hasSymbol(','));
            $tokenList->expectSymbol(')');
        }

        return $partitions;
    }

    /**
     * @return list<ColumnIdentifier>|null
     */
    private function parseColumnList(TokenList $tokenList): ?array
    {
        $position = $tokenList->getPosition();
        $columns = null;
        if ($tokenList->hasSymbol('(')) {
            if ($tokenList->hasSymbol(')')) {
                return [];
            }
            if ($tokenList->hasAnyKeyword(Keyword::SELECT, Keyword::TABLE, Keyword::VALUES, Keyword::WITH)) {
                // this is not a column list
                $tokenList->rewind($position);
                return null;
            }
            $columns = [];
            do {
                $columns[] = $this->expressionParser->parseColumnIdentifier($tokenList);
            } while ($tokenList->hasSymbol(','));
            $tokenList->expectSymbol(')');
        }

        return $columns;
    }

    private function parseOnDuplicateKeyUpdate(TokenList $tokenList): ?OnDuplicateKeyActions
    {
        if (!$tokenList->hasKeywords(Keyword::ON, Keyword::DUPLICATE, Keyword::KEY, Keyword::UPDATE)) {
            return null;
        }

        $assignments = $this->parseAssignments($tokenList);

        return new OnDuplicateKeyActions($assignments);
    }

    /**
     * @return non-empty-list<Assignment>
     */
    private function parseAssignments(TokenList $tokenList): array
    {
        $assignments = [];
        do {
            $column = $this->expressionParser->parseColumnIdentifier($tokenList);
            $operator = $tokenList->expectAnyOperator(Operator::EQUAL, Operator::ASSIGN);
            $assignments[] = new Assignment($column, $this->expressionParser->parseExpression($tokenList), $operator);
        } while ($tokenList->hasSymbol(','));

        return $assignments;
    }

    /**
     * @return non-empty-list<list<ExpressionNode>>
     */
    private function parseRows(TokenList $tokenList): array
    {
        $rows = [];
        do {
            $tokenList->expectSymbol('(');

            $values = [];
            if (!$tokenList->hasSymbol(')')) {
                do {
                    $values[] = $this->expressionParser->parseAssignExpression($tokenList);
                } while ($tokenList->hasSymbol(','));
                $tokenList->expectSymbol(')');
            }

            $rows[] = $values;
        } while ($tokenList->hasSymbol(','));

        return $rows;
    }

}
