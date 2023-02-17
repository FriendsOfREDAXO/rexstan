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
use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\ParserFactory;
use SqlFtw\Parser\TokenList;
use SqlFtw\Parser\TokenType;
use SqlFtw\Platform\Platform;
use SqlFtw\Sql\Command;
use SqlFtw\Sql\CommonTableExpressionType;
use SqlFtw\Sql\Dml\Delete\DeleteCommand;
use SqlFtw\Sql\Dml\Query\GroupByExpression;
use SqlFtw\Sql\Dml\Query\ParenthesizedQueryExpression;
use SqlFtw\Sql\Dml\Query\Query;
use SqlFtw\Sql\Dml\Query\QueryExpression;
use SqlFtw\Sql\Dml\Query\QueryOperator;
use SqlFtw\Sql\Dml\Query\QueryOperatorOption;
use SqlFtw\Sql\Dml\Query\QueryOperatorType;
use SqlFtw\Sql\Dml\Query\Row;
use SqlFtw\Sql\Dml\Query\SelectCommand;
use SqlFtw\Sql\Dml\Query\SelectDistinctOption;
use SqlFtw\Sql\Dml\Query\SelectExpression;
use SqlFtw\Sql\Dml\Query\SelectInto;
use SqlFtw\Sql\Dml\Query\SelectIntoDumpfile;
use SqlFtw\Sql\Dml\Query\SelectIntoOutfile;
use SqlFtw\Sql\Dml\Query\SelectIntoVariables;
use SqlFtw\Sql\Dml\Query\SelectLocking;
use SqlFtw\Sql\Dml\Query\SelectLockOption;
use SqlFtw\Sql\Dml\Query\SelectLockWaitOption;
use SqlFtw\Sql\Dml\Query\SelectOption;
use SqlFtw\Sql\Dml\Query\TableCommand;
use SqlFtw\Sql\Dml\Query\ValuesCommand;
use SqlFtw\Sql\Dml\Query\WindowFrame;
use SqlFtw\Sql\Dml\Query\WindowFrameType;
use SqlFtw\Sql\Dml\Query\WindowFrameUnits;
use SqlFtw\Sql\Dml\Query\WindowSpecification;
use SqlFtw\Sql\Dml\Update\UpdateCommand;
use SqlFtw\Sql\Dml\WithClause;
use SqlFtw\Sql\Dml\WithExpression;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\Asterisk;
use SqlFtw\Sql\Expression\DefaultLiteral;
use SqlFtw\Sql\Expression\Identifier;
use SqlFtw\Sql\Expression\NullLiteral;
use SqlFtw\Sql\Expression\NumericValue;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\OrderByExpression;
use SqlFtw\Sql\Expression\Placeholder;
use SqlFtw\Sql\Expression\QualifiedName;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\Expression\Subquery;
use SqlFtw\Sql\Expression\TimeInterval;
use SqlFtw\Sql\Expression\TimeIntervalExpression;
use SqlFtw\Sql\Expression\TimeIntervalLiteral;
use SqlFtw\Sql\Expression\UintLiteral;
use SqlFtw\Sql\Expression\UserVariable;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\Order;
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\SubqueryType;
use function array_pop;
use function count;
use function in_array;

class QueryParser
{

    private ParserFactory $parserFactory;

    private ExpressionParser $expressionParser;

    private TableReferenceParser $tableReferenceParser;

    private OptimizerHintParser $optimizerHintParser;

    public function __construct(
        ParserFactory $parserFactory,
        ExpressionParser $expressionParser,
        TableReferenceParser $tableReferenceParser,
        OptimizerHintParser $optimizerHintParser
    ) {
        $this->parserFactory = $parserFactory;
        $this->expressionParser = $expressionParser;
        $this->tableReferenceParser = $tableReferenceParser;
        $this->optimizerHintParser = $optimizerHintParser;
    }

    /**
     * with_clause:
     *   WITH [RECURSIVE]
     *     cte_name [(col_name [, col_name] ...)] AS (subquery)
     *     [, cte_name [(col_name [, col_name] ...)] AS (subquery)] ...
     *
     * @return Statement&(Query|UpdateCommand|DeleteCommand)
     */
    public function parseWith(TokenList $tokenList): Command
    {
        $tokenList->expectKeyword(Keyword::WITH);
        $recursive = $tokenList->hasKeyword(Keyword::RECURSIVE);
        $tokenList->startCommonTableExpression($recursive ? CommonTableExpressionType::WITH_RECURSIVE : CommonTableExpressionType::WITH);

        $expressions = [];
        do {
            $name = $tokenList->expectName(null);
            $columns = null;
            if ($tokenList->hasSymbol('(')) {
                $columns = [];
                do {
                    $columns[] = $tokenList->expectName(EntityType::COLUMN);
                } while ($tokenList->hasSymbol(','));
                $tokenList->expectSymbol(')');
            }
            $tokenList->expectKeyword(Keyword::AS);
            $tokenList->expectSymbol('(');
            $tokenList->startSubquery(SubqueryType::WITH);
            $query = $this->parserFactory->getQueryParser()->parseQuery($tokenList);
            $tokenList->endSubquery();
            $tokenList->expectSymbol(')');

            $expressions[] = new WithExpression($query, $name, $columns);
        } while ($tokenList->hasSymbol(','));

        $tokenList->endCommonTableExpression();
        $with = new WithClause($expressions, $recursive);

        if ($tokenList->hasSymbol('(')) {
            $next = '(';
        } else {
            $next = $tokenList->expectAnyKeyword(Keyword::SELECT, Keyword::UPDATE, Keyword::DELETE);
        }
        switch ($next) {
            case '(':
            case Keyword::SELECT:
                return $this->parserFactory->getQueryParser()->parseQuery($tokenList->rewind(-1), $with);
            case Keyword::UPDATE:
                return $this->parserFactory->getUpdateCommandParser()->parseUpdate($tokenList->rewind(-1), $with);
            case Keyword::DELETE:
                return $this->parserFactory->getDeleteCommandParser()->parseDelete($tokenList->rewind(-1), $with);
            default:
                throw new ParserException('Unexpected token after WITH clause - expected keyword SELECT, UPDATE, DELETE or "("', $tokenList);
        }
    }

    /**
     * query:
     *     parenthesized_query_expression
     *   | query_expression
     *
     * parenthesized_query_expression:
     *   ( query_expression [order_by_clause] [limit_clause] )
     *     [order_by_clause]
     *     [limit_clause]
     *     [into_clause]
     *
     * query_expression:
     *     [with_clause]
     *     query_expression_body
     *     [order_by_clause] [limit_clause] [into_clause]
     *
     * query_expression_body:
     *      query_term
     *   |  query_expression_body UNION [ALL | DISTINCT] query_term
     *   |  query_expression_body EXCEPT [ALL | DISTINCT] query_term
     *
     * query_term:
     *      query_primary
     *   |  query_term INTERSECT [ALL | DISTINCT] query_block
     *
     * query_primary:
     *      query_block
     *   |  '(' query_expression_body [order_by_clause] [limit_clause] [into_clause] ')'
     *
     * query_bock:
     *     parenthesized_query_expression
     *   | [WITH ...] SELECT ...
     *   | TABLE ...
     *   | VALUES ...
     *
     * @return Query&Statement
     */
    public function parseQuery(TokenList $tokenList, ?WithClause $with = null): Query
    {
        $queries = [$this->parseQueryBlock($tokenList, $with)];
        $operators = [];

        $operatorType = $tokenList->getKeywordEnum(QueryOperatorType::class);
        if ($operatorType !== null) {
            $tokenList->startQueryExpression();
            if (!$operatorType->equalsValue(QueryOperatorType::UNION) && $tokenList->inCommonTableExpression(CommonTableExpressionType::WITH_RECURSIVE)) {
                // todo: exact behavior?
                //throw new ParserException("Only UNION operator is allowed inside recursive common table expression.", $tokenList);
            }
        }
        while ($operatorType !== null) {
            if ($tokenList->hasKeyword(Keyword::ALL)) {
                $operatorOption = new QueryOperatorOption(QueryOperatorOption::ALL);
            } elseif ($tokenList->hasKeyword(Keyword::DISTINCT)) {
                $operatorOption = new QueryOperatorOption(QueryOperatorOption::DISTINCT);
            } else {
                $operatorOption = new QueryOperatorOption(QueryOperatorOption::DEFAULT);
            }
            $operators[] = new QueryOperator($operatorType, $operatorOption);
            $queries[] = $this->parseQueryBlock($tokenList);
            $operatorType = $tokenList->getKeywordEnum(QueryOperatorType::class);
        }
        $tokenList->endQueryExpression();

        if (count($queries) === 1 || count($operators) === 0) {
            return $queries[0];
        }

        [$orderBy, $limit, $offset, $into] = $this->parseOrderLimitOffsetInto($tokenList, false);

        $locking = $this->parseLocking($tokenList);

        // order, limit and into of last unparenthesized query belong to the whole union result
        /** @var Query $lastQuery PHPStan assumes it might be null :E */
        $lastQuery = array_pop($queries);

        if ($lastQuery instanceof SelectCommand) {
            $queryLocking = $lastQuery->getLocking();
            if ($queryLocking !== null) {
                if ($locking !== null) {
                    throw new ParserException("Duplicate INTO clause in last query and in UNION|EXCEPT|INTERSECT.", $tokenList);
                } else {
                    $locking = $queryLocking;
                    $lastQuery = $lastQuery->removeLocking();
                }
            }
        }

        $queryOrderBy = $lastQuery->getOrderBy();
        if ($queryOrderBy !== null) {
            if ($orderBy !== null) {
                throw new ParserException("Duplicate ORDER BY clause in last query and in UNION|EXCEPT|INTERSECT.", $tokenList);
            } else {
                $orderBy = $queryOrderBy;
                $lastQuery = $lastQuery->removeOrderBy();
            }
        }

        $queryLimit = $lastQuery->getLimit();
        if ($queryLimit !== null) {
            if ($limit !== null) {
                throw new ParserException("Duplicate LIMIT clause in last query and in UNION|EXCEPT|INTERSECT.", $tokenList);
            } else {
                $limit = $queryLimit;
                $lastQuery = $lastQuery->removeLimit();
            }
        }

        $queryOffset = $lastQuery instanceof ValuesCommand ? null : $lastQuery->getOffset();
        if ($queryOffset !== null) {
            if ($offset !== null) {
                throw new ParserException("Duplicate LIMIT clause in last query and in UNION|EXCEPT|INTERSECT.", $tokenList);
            } else {
                $offset = $queryOffset;
                $lastQuery = $lastQuery->removeOffset();
            }
        }

        $queryInto = $lastQuery->getInto();
        if ($queryInto !== null) {
            if ($into !== null) {
                throw new ParserException("Duplicate INTO clause in last query and in UNION|EXCEPT|INTERSECT.", $tokenList);
            } else {
                $into = $queryInto;
                $lastQuery = $lastQuery->removeInto();
            }
        }

        $queries[] = $lastQuery;

        foreach ($queries as $i => $query) {
            if ($query instanceof SelectCommand) {
                if ($query->getLocking() !== null) {
                    throw new ParserException("Locking options are not allowed in UNION|EXCEPT|INTERSECT without parentheses around query.", $tokenList);
                }
            }
            if ($query->getLimit() !== null) {
                throw new ParserException("LIMIT not allowed in UNION|EXCEPT|INTERSECT without parentheses around query.", $tokenList);
            }
            if ($query->getOrderBy() !== null) {
                throw new ParserException("ORDER BY not allowed in UNION|EXCEPT|INTERSECT without parentheses around query.", $tokenList);
            }
            if ($query->getInto() !== null) {
                throw new ParserException("INTO not allowed in UNION|EXCEPT|INTERSECT or subquery.", $tokenList);
            }
            if ($query instanceof ParenthesizedQueryExpression) {
                if ($i !== 0 && $query->getQuery() instanceof QueryExpression) {
                    // todo: seems like a pre 8.0.31 limitation
                    //throw new ParserException("Nested UNIONs are only allowed on left side.", $tokenList);
                }
                if ($query->containsInto()) {
                    throw new ParserException("INTO not allowed in UNION or subquery.", $tokenList);
                }
            }
        }

        return new QueryExpression($queries, $operators, $orderBy, $limit, $offset, $into, $locking);
    }

    /**
     * @return Query&Statement
     */
    public function parseQueryBlock(TokenList $tokenList, ?WithClause $with = null): Query
    {
        if ($tokenList->hasSymbol('(')) {
            return $this->parseParenthesizedQueryExpression($tokenList->rewind(-1), $with);
        }

        $keywords = $with !== null
            ? [Keyword::SELECT]
            : [Keyword::SELECT, Keyword::TABLE, Keyword::VALUES, Keyword::WITH];

        $keyword = $tokenList->expectAnyKeyword(...$keywords);
        if ($keyword === Keyword::SELECT) {
            return $this->parseSelect($tokenList->rewind(-1), $with);
        } elseif ($keyword === Keyword::TABLE) {
            return $this->parseTable($tokenList->rewind(-1));
        } elseif ($keyword === Keyword::VALUES) {
            return $this->parseValues($tokenList->rewind(-1));
        } else {
            $statement = $this->parseWith($tokenList->rewind(-1));
            if (!$statement instanceof Query) {
                throw new ParserException('Expected a query.', $tokenList);
            }

            return $statement;
        }
    }

    private function parseParenthesizedQueryExpression(TokenList $tokenList, ?WithClause $with = null): ParenthesizedQueryExpression
    {
        $tokenList->expectSymbol('(');
        $query = $this->parseQuery($tokenList);
        $tokenList->expectSymbol(')');

        [$orderBy, $limit, $offset, $into] = $this->parseOrderLimitOffsetInto($tokenList);

        if ($orderBy !== null) {
            foreach ($orderBy as $order) {
                $column = $order->getColumn();
                if ($column !== null && !$column instanceof SimpleName) {
                    throw new ParserException('Qualified name in ORDER BY is not allowed in parenthesized query expression.', $tokenList);
                }
            }
        }

        $queryInto = $query->getInto();
        if ($queryInto !== null) {
            if ($into !== null) {
                throw new ParserException("Duplicate INTO clause in query and in parenthesized query expression.", $tokenList);
            } else {
                $into = $queryInto;
                $query = $query->removeInto();
            }
        }

        return new ParenthesizedQueryExpression($query, $with, $orderBy, $limit, $offset, $into);
    }

    /**
     * SELECT
     *     [ALL | DISTINCT | DISTINCTROW ]
     *     [HIGH_PRIORITY]
     *     [STRAIGHT_JOIN]
     *     [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
     *     [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
     *     select_expr [, select_expr ...]
     *     [into_option]
     *     [FROM table_references
     *       [PARTITION partition_list]
     *     [WHERE where_condition]
     *     [GROUP BY {col_name | expr | position}
     *       [ASC | DESC], ... [WITH ROLLUP]]
     *     [HAVING where_condition]
     *     [WINDOW window_name AS (window_spec)
     *       [, window_name AS (window_spec)] ...]
     *     [ORDER BY {col_name | expr | position}
     *       [ASC | DESC], ...]
     *     [LIMIT {[offset,] row_count | row_count OFFSET offset}]
     *     [into_option]
     *     [FOR UPDATE | LOCK IN SHARE MODE]]
     *     [FOR {UPDATE | SHARE} [OF tbl_name [, tbl_name] ...] [NOWAIT | SKIP LOCKED]
     *       | LOCK IN SHARE MODE]]
     *     [into_option]
     *
     * @return Query&Statement
     */
    public function parseSelect(TokenList $tokenList, ?WithClause $with = null): Query
    {
        $tokenList->expectKeyword(Keyword::SELECT);

        $optimizerHints = null;
        if ($tokenList->has(TokenType::OPTIMIZER_HINT_START)) {
            $optimizerHints = $this->optimizerHintParser->parseHints($tokenList->rewind(-1));
        }

        $keywords = [
            Keyword::ALL, Keyword::DISTINCT, Keyword::DISTINCTROW, Keyword::HIGH_PRIORITY, Keyword::STRAIGHT_JOIN,
            Keyword::SQL_SMALL_RESULT, Keyword::SQL_BIG_RESULT, Keyword::SQL_BUFFER_RESULT, Keyword::SQL_CACHE,
            Keyword::SQL_NO_CACHE, Keyword::SQL_CALC_FOUND_ROWS,
        ];
        $distinct = null;
        $options = [];
        while (($keyword = $tokenList->getAnyKeyword(...$keywords)) !== null) {
            switch ($keyword) {
                case Keyword::ALL:
                case Keyword::DISTINCT:
                case Keyword::DISTINCTROW:
                    if ($keyword === Keyword::DISTINCTROW) {
                        $keyword = Keyword::DISTINCT;
                    }
                    if ($distinct !== null) {
                        if (!$distinct->equalsValue($keyword)) {
                            throw new ParserException('Cannot use both DISTINCT and ALL', $tokenList);
                        }
                    }
                    $distinct = new SelectDistinctOption($keyword);
                    break;
                case Keyword::HIGH_PRIORITY:
                    $options[SelectOption::HIGH_PRIORITY] = true;
                    break;
                case Keyword::STRAIGHT_JOIN:
                    $options[SelectOption::STRAIGHT_JOIN] = true;
                    break;
                case Keyword::SQL_SMALL_RESULT:
                    $options[SelectOption::SMALL_RESULT] = true;
                    break;
                case Keyword::SQL_BIG_RESULT:
                    $options[SelectOption::BIG_RESULT] = true;
                    break;
                case Keyword::SQL_BUFFER_RESULT:
                    $options[SelectOption::BUFFER_RESULT] = true;
                    break;
                case Keyword::SQL_CACHE:
                    if (isset($options[SelectOption::NO_CACHE])) {
                        throw new ParserException('Cannot combine SQL_CACHE and SQL_NO_CACHE options.', $tokenList);
                    }
                    $options[SelectOption::CACHE] = true;
                    break;
                case Keyword::SQL_NO_CACHE:
                    if (isset($options[SelectOption::CACHE])) {
                        throw new ParserException('Cannot combine SQL_CACHE and SQL_NO_CACHE options.', $tokenList);
                    }
                    $options[SelectOption::NO_CACHE] = true;
                    break;
                case Keyword::SQL_CALC_FOUND_ROWS:
                    $options[SelectOption::CALC_FOUND_ROWS] = true;
                    break;
            }
        }

        $what = [];
        do {
            if ($tokenList->hasOperator(Operator::MULTIPLY)) {
                $expression = new Asterisk();
            } else {
                $expression = $this->expressionParser->parseAssignExpression($tokenList);
            }
            $window = null;
            if ($tokenList->hasKeyword(Keyword::OVER)) {
                if ($tokenList->hasSymbol('(')) {
                    $window = $this->parseWindow($tokenList);
                    $tokenList->expectSymbol(')');
                } else {
                    $window = $tokenList->expectName(null);
                }
            }
            $alias = $this->expressionParser->parseAlias($tokenList);
            if ($alias !== null && $expression instanceof QualifiedName && $expression->getName() === '*') {
                throw new ParserException('Cannot use alias after *.', $tokenList);
            }

            $what[] = new SelectExpression($expression, $alias, $window);
        } while ($tokenList->hasSymbol(','));

        $into = null;
        if ($tokenList->inSubquery() === null) {
            if ($tokenList->hasKeyword(Keyword::INTO)) {
                $into = $this->parseInto($tokenList, SelectInto::POSITION_BEFORE_FROM);
            }
        }

        $from = null;
        if ($tokenList->hasKeyword(Keyword::FROM)) {
            $from = $this->tableReferenceParser->parseTableReferences($tokenList);
        }

        $where = null;
        if ($tokenList->hasKeyword(Keyword::WHERE)) {
            $where = $this->expressionParser->parseAssignExpression($tokenList);
        }

        $groupBy = null;
        $withRollup = false;
        if ($tokenList->hasKeywords(Keyword::GROUP, Keyword::BY)) {
            $groupBy = [];
            do {
                $expression = $this->expressionParser->parseAssignExpression($tokenList);
                $order = null;
                if ($tokenList->using(Platform::MYSQL, null, 50799)) {
                    $order = $tokenList->getKeywordEnum(Order::class);
                }
                $groupBy[] = new GroupByExpression($expression, $order);
            } while ($tokenList->hasSymbol(','));

            $withRollup = $tokenList->hasKeywords(Keyword::WITH, Keyword::ROLLUP);
        }

        $having = null;
        if ($tokenList->hasKeyword(Keyword::HAVING)) {
            $having = $this->expressionParser->parseAssignExpression($tokenList);
        }

        $windows = null;
        if ($tokenList->hasKeyword(Keyword::WINDOW)) {
            $windows = [];
            do {
                $name = $tokenList->expectName(null);
                $tokenList->expectKeyword(Keyword::AS);

                $tokenList->expectSymbol('(');
                $window = $this->parseWindow($tokenList);
                $tokenList->expectSymbol(')');

                if (isset($windows[$name])) {
                    throw new ParserException('Duplicit window name.', $tokenList);
                }
                $windows[$name] = $window;
            } while ($tokenList->hasSymbol(','));
        }

        $orderBy = null;
        if ($tokenList->hasKeywords(Keyword::ORDER, Keyword::BY)) {
            $orderBy = $this->expressionParser->parseOrderBy($tokenList);
        }

        $limit = $offset = null;
        if ($tokenList->hasKeyword(Keyword::LIMIT)) {
            $subquery = $tokenList->inSubquery();
            if (in_array($subquery, [SubqueryType::IN, SubqueryType::ALL, SubqueryType::ANY, SubqueryType::SOME], true)) {
                throw new ParserException('LIMIT is not supported in IN/ALL/ANY/SOME subquery.', $tokenList);
            }
            $limit = $this->expressionParser->parseLimitOrOffsetValue($tokenList);
            if ($tokenList->hasKeyword(Keyword::OFFSET)) {
                $offset = $this->expressionParser->parseLimitOrOffsetValue($tokenList);
            } elseif ($tokenList->hasSymbol(',')) {
                $offset = $limit;
                $limit = $this->expressionParser->parseLimitOrOffsetValue($tokenList);
            }
        }

        if ($tokenList->hasKeywords(Keyword::PROCEDURE, Keyword::ANALYSE)) {
            $tokenList->expectSymbol('(');
            // todo: ignored
            $this->expressionParser->parseFunctionCall($tokenList, 'PROCEDURE ANALYSE');
        }

        $locking = $this->parseLocking($tokenList);

        if ($tokenList->inSubquery() === null && !($tokenList->inQueryExpression() && $from !== null)) {
            if ($into === null && $tokenList->hasKeyword(Keyword::INTO)) {
                $into = $this->parseInto($tokenList, $locking !== null ? SelectInto::POSITION_AFTER_LOCKING : SelectInto::POSITION_BEFORE_LOCKING);
            }
        }

        if ($locking === null) {
            $locking = $this->parseLocking($tokenList);
        }

        if ($from === null && count($what) === 1 && $what[0]->getExpression() instanceof Asterisk && $tokenList->inSubquery() !== SubqueryType::EXISTS) {
            throw new ParserException('No tables used in query.', $tokenList);
        }

        return new SelectCommand($what, $from, $where, $groupBy, $having, $with, $windows, $orderBy, $limit, $offset, $distinct, $options, $into, $locking, $withRollup, $optimizerHints);
    }

    /**
     * TABLE table_name [ORDER BY column_name] [LIMIT number [OFFSET number]]
     *   [into_option]
     */
    public function parseTable(TokenList $tokenList): TableCommand
    {
        $tokenList->expectKeyword(Keyword::TABLE);
        $name = $tokenList->expectObjectIdentifier();

        [$orderBy, $limit, $offset, $into] = $this->parseOrderLimitOffsetInto($tokenList);

        return new TableCommand($name, $orderBy, $limit, $offset, $into);
    }

    /**
     * VALUES row_constructor_list [ORDER BY column_designator] [LIMIT number]
     *
     * row_constructor_list:
     *   ROW(value_list)[, ROW(value_list)][, ...]
     *
     * value_list:
     *   value[, value][, ...]
     *
     * column_designator:
     *   column_index
     */
    public function parseValues(TokenList $tokenList): ValuesCommand
    {
        $tokenList->expectKeyword(Keyword::VALUES);
        $rows = [];
        do {
            $tokenList->expectKeyword(Keyword::ROW);
            $tokenList->expectSymbol('(');
            $values = [];
            if (!$tokenList->hasSymbol(')') || $tokenList->inSubquery() !== SubqueryType::INSERT) {
                do {
                    $value = $this->expressionParser->parseExpression($tokenList);
                    if ($value instanceof DefaultLiteral && $tokenList->inSubquery() !== SubqueryType::INSERT) {
                        throw new ParserException('Cannot use DEFAULT in ROW() expression outside and INSERT.', $tokenList);
                    }
                    $values[] = $value;
                } while ($tokenList->hasSymbol(','));
                $tokenList->expectSymbol(')');
            }
            $rows[] = new Row($values);
        } while ($tokenList->hasSymbol(','));

        [$orderBy, $limit, , $into] = $this->parseOrderLimitOffsetInto($tokenList, false);

        return new ValuesCommand($rows, $orderBy, $limit, $into);
    }

    /**
     * @return array{non-empty-list<OrderByExpression>|null, int|SimpleName|Placeholder|null, int|SimpleName|Placeholder|null, SelectInto|null}
     */
    private function parseOrderLimitOffsetInto(TokenList $tokenList, bool $parseOffset = true): array
    {
        $orderBy = $limit = $offset = $into = null;
        if ($tokenList->hasKeywords(Keyword::ORDER, Keyword::BY)) {
            $orderBy = $this->expressionParser->parseOrderBy($tokenList);
        }
        if ($tokenList->hasKeyword(Keyword::LIMIT)) {
            $limit = $this->expressionParser->parseLimitOrOffsetValue($tokenList);
            if ($parseOffset && $tokenList->hasKeyword(Keyword::OFFSET)) {
                $offset = $this->expressionParser->parseLimitOrOffsetValue($tokenList);
            }
        }

        if ($tokenList->inSubquery() === null) {
            if ($tokenList->hasKeyword(Keyword::INTO)) {
                $into = $this->parseInto($tokenList, SelectInto::POSITION_AFTER_LOCKING);
            }
        }

        return [$orderBy, $limit, $offset, $into];
    }

    /**
     * into_option: {
     *     INTO OUTFILE 'file_name'
     *       [CHARACTER SET charset_name]
     *       export_options
     *   | INTO DUMPFILE 'file_name'
     *   | INTO var_name [, var_name] ...
     * }
     *
     * @param SelectInto::POSITION_* $position
     */
    private function parseInto(TokenList $tokenList, int $position): SelectInto
    {
        if ($tokenList->hasKeyword(Keyword::OUTFILE)) {
            $outFile = $tokenList->expectString();
            $charset = null;
            if ($tokenList->hasKeywords(Keyword::CHARACTER, Keyword::SET) || $tokenList->hasKeyword(Keyword::CHARSET)) {
                $charset = $tokenList->expectCharsetName();
            }
            $format = $this->expressionParser->parseFileFormat($tokenList);

            return new SelectIntoOutfile($outFile, $charset, $format, $position);
        } elseif ($tokenList->hasKeyword(Keyword::DUMPFILE)) {
            $dumpFile = $tokenList->expectString();

            return new SelectIntoDumpfile($dumpFile, $position);
        } else {
            $variables = [];
            do {
                $token = $tokenList->get(TokenType::AT_VARIABLE);
                if ($token !== null) {
                    $variable = new UserVariable($token->value);
                } else {
                    $name = $tokenList->expectName(null);
                    $variable = new SimpleName($name);
                }
                $variables[] = $variable;
            } while ($tokenList->hasSymbol(','));

            return new SelectIntoVariables($variables, $position);
        }
    }

    /**
     * @return non-empty-list<SelectLocking>|null
     */
    private function parseLocking(TokenList $tokenList): ?array
    {
        $locking = [];
        do {
            $updated = false;
            if ($tokenList->hasKeywords(Keyword::LOCK, Keyword::IN, Keyword::SHARE, Keyword::MODE)) {
                $lockOption = new SelectLockOption(SelectLockOption::LOCK_IN_SHARE_MODE);
                $locking[] = new SelectLocking($lockOption);
                $updated = true;
            } elseif ($tokenList->hasKeyword(Keyword::FOR)) {
                if ($tokenList->hasKeyword(Keyword::UPDATE)) {
                    $lockOption = new SelectLockOption(SelectLockOption::FOR_UPDATE);
                } else {
                    $tokenList->expectKeyword(Keyword::SHARE);
                    $lockOption = new SelectLockOption(SelectLockOption::FOR_SHARE);
                }
                $lockTables = null;
                if ($tokenList->hasKeyword(Keyword::OF)) {
                    $lockTables = [];
                    do {
                        $lockTables[] = $tokenList->expectObjectIdentifier();
                    } while ($tokenList->hasSymbol(','));
                }
                $lockWaitOption = $tokenList->getMultiKeywordsEnum(SelectLockWaitOption::class);
                $locking[] = new SelectLocking($lockOption, $lockWaitOption, $lockTables);
                $updated = true;
            }
        } while ($updated);

        if ($locking === []) {
            return null;
        }

        return $locking;
    }

    /**
     * window_spec:
     *   [window_name] [partition_clause] [order_clause] [frame_clause]
     *
     * partition_clause:
     *   PARTITION BY expr [, expr] ...
     *
     * order_clause:
     *   ORDER BY expr [ASC|DESC] [, expr [ASC|DESC]] ...
     *
     * frame_clause:
     *   frame_units frame_extent
     *
     * frame_units:
     *   {ROWS | RANGE}
     *
     * frame_extent:
     *   {frame_start | frame_between}
     *
     * frame_between:
     *   BETWEEN frame_start AND frame_end
     */
    public function parseWindow(TokenList $tokenList): WindowSpecification
    {
        $name = $tokenList->getNonReservedName(null);

        $partitionBy = $orderBy = $frame = null;
        if ($tokenList->hasKeywords(Keyword::PARTITION, Keyword::BY)) {
            $partitionBy = [];
            do {
                $partitionBy[] = $this->expressionParser->parseExpression($tokenList);
            } while ($tokenList->hasSymbol(','));
        }

        if ($tokenList->hasKeywords(Keyword::ORDER, Keyword::BY)) {
            $orderBy = $this->expressionParser->parseOrderBy($tokenList);
        }

        $keyword = $tokenList->getAnyKeyword(Keyword::ROWS, Keyword::RANGE);
        $rows = $keyword === Keyword::ROWS;
        if ($keyword !== null) {
            $units = new WindowFrameUnits($keyword);
            if ($tokenList->hasKeyword(Keyword::BETWEEN)) {
                [$startType, $startExpression] = $this->parseFrameBorder($tokenList, $rows, true);
                $tokenList->expectKeyword(Keyword::AND);
                [$endType, $endExpression] = $this->parseFrameBorder($tokenList, $rows, false);
            } else {
                [$startType, $startExpression] = $this->parseFrameBorder($tokenList, $rows, null);
                $endType = $endExpression = null;
            }
            if ($rows && ($startExpression instanceof TimeInterval || $endExpression instanceof TimeInterval)) {
                throw new ParserException('INTERVAL can only be used with RANGE frames.', $tokenList);
            }

            $frame = new WindowFrame($units, $startType, $endType, $startExpression, $endExpression);
        }

        return new WindowSpecification($name, $partitionBy, $orderBy, $frame);
    }

    /**
     * frame_start, frame_end: {
     *     CURRENT ROW
     *   | UNBOUNDED PRECEDING
     *   | UNBOUNDED FOLLOWING
     *   | expr PRECEDING
     *   | expr FOLLOWING
     * }
     *
     * @return array{WindowFrameType, RootNode|null}
     */
    private function parseFrameBorder(TokenList $tokenList, bool $rows, ?bool $start): array
    {
        $expression = null;
        if ($tokenList->hasKeywords(Keyword::CURRENT, Keyword::ROW)) {
            $type = new WindowFrameType(WindowFrameType::CURRENT_ROW);
        } elseif ($start !== false && $tokenList->hasKeywords(Keyword::UNBOUNDED, Keyword::PRECEDING)) {
            $type = new WindowFrameType(WindowFrameType::UNBOUNDED_PRECEDING);
        } elseif ($start !== true && $tokenList->hasKeywords(Keyword::UNBOUNDED, Keyword::FOLLOWING)) {
            $type = new WindowFrameType(WindowFrameType::UNBOUNDED_FOLLOWING);
        } else {
            $expression = $this->expressionParser->parseExpression($tokenList);

            if ($rows && (!$expression instanceof UintLiteral && !$expression instanceof Placeholder)) {
                throw new ParserException("Window frame extent must be a positive int for ROWS.", $tokenList);
            }

            if ($expression instanceof NumericValue && $expression->isNegative()) {
                throw new ParserException("Window frame extent cannot be a negative number.", $tokenList);
            } elseif ($expression instanceof TimeIntervalLiteral && $expression->isNegative()) {
                throw new ParserException("Window frame extent cannot be a negative interval.", $tokenList);
            } elseif ($expression instanceof TimeIntervalExpression) {
                if ($expression->getExpression() instanceof NullLiteral) {
                    throw new ParserException("Window frame extent cannot be a null interval.", $tokenList);
                }
                // todo: should resolve and check for negative
            } elseif ($expression instanceof Identifier && !$expression instanceof Placeholder) {
                throw new ParserException("Window frame extent cannot be an identifier.", $tokenList);
            } elseif ($expression instanceof Subquery) {
                throw new ParserException("Window frame extent cannot be a subquery.", $tokenList);
            }

            $keyword = $tokenList->expectAnyKeyword(Keyword::PRECEDING, Keyword::FOLLOWING);
            $type = new WindowFrameType($keyword);
        }

        return [$type, $expression];
    }

}
