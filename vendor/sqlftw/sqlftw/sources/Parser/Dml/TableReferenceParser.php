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
use SqlFtw\Parser\TokenList;
use SqlFtw\Parser\TokenType;
use SqlFtw\Sql\Dml\Query\Query;
use SqlFtw\Sql\Dml\TableReference\EscapedTableReference;
use SqlFtw\Sql\Dml\TableReference\IndexHint;
use SqlFtw\Sql\Dml\TableReference\IndexHintAction;
use SqlFtw\Sql\Dml\TableReference\IndexHintTarget;
use SqlFtw\Sql\Dml\TableReference\InnerJoin;
use SqlFtw\Sql\Dml\TableReference\JoinSide;
use SqlFtw\Sql\Dml\TableReference\NaturalJoin;
use SqlFtw\Sql\Dml\TableReference\OuterJoin;
use SqlFtw\Sql\Dml\TableReference\StraightJoin;
use SqlFtw\Sql\Dml\TableReference\TableReferenceJsonTable;
use SqlFtw\Sql\Dml\TableReference\TableReferenceList;
use SqlFtw\Sql\Dml\TableReference\TableReferenceNode;
use SqlFtw\Sql\Dml\TableReference\TableReferenceParentheses;
use SqlFtw\Sql\Dml\TableReference\TableReferenceSubquery;
use SqlFtw\Sql\Dml\TableReference\TableReferenceTable;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\PrimaryLiteral;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\SubqueryType;
use function count;

class TableReferenceParser
{

    private ExpressionParser $expressionParser;

    /** @var callable(): QueryParser */
    private $queryParserProxy;

    /**
     * @param callable(): QueryParser $queryParserProxy
     */
    public function __construct(ExpressionParser $expressionParser, callable $queryParserProxy)
    {
        $this->expressionParser = $expressionParser;
        $this->queryParserProxy = $queryParserProxy;
    }

    /**
     * table_references:
     *     escaped_table_reference [, escaped_table_reference] ...
     */
    public function parseTableReferences(TokenList $tokenList): TableReferenceNode
    {
        $references = [];
        do {
            $references[] = $this->parseTableReference($tokenList);
        } while ($tokenList->hasSymbol(','));

        if (count($references) === 1) {
            return $references[0];
        } else {
            return new TableReferenceList($references);
        }
    }

    /**
     * escaped_table_reference:
     *     table_reference
     *   | { OJ table_reference }
     */
    public function parseTableReference(TokenList $tokenList): TableReferenceNode
    {
        if ($tokenList->hasSymbol('{')) {
            $tokenList->expectKeyword(Keyword::OJ);
            $reference = $this->parseTableReference($tokenList);
            $tokenList->expectSymbol('}');

            return new EscapedTableReference($reference);
        } else {
            return $this->parseTableReferenceInternal($tokenList);
        }
    }

    /**
     * table_reference:
     *     table_factor
     *   | joined_table
     *
     * joined_table: {
     *     table_reference {[INNER | CROSS] JOIN | STRAIGHT_JOIN} table_factor [join_specification]
     *   | table_reference {LEFT|RIGHT} [OUTER] JOIN table_reference join_specification
     *   | table_reference NATURAL [INNER | {LEFT|RIGHT} [OUTER]] JOIN table_factor
     * }
     *
     * join_condition:
     *     ON conditional_expr
     *   | USING (column_list)
     */
    private function parseTableReferenceInternal(TokenList $tokenList): TableReferenceNode
    {
        $left = $this->parseTableFactor($tokenList);

        do {
            if ($tokenList->hasKeyword(Keyword::NATURAL)) {
                // NATURAL JOIN
                $side = null;
                if (!$tokenList->hasKeyword(Keyword::INNER)) {
                    $side = $tokenList->getKeywordEnum(JoinSide::class);
                    if ($side !== null) {
                        $tokenList->passKeyword(Keyword::OUTER);
                    }
                }
                $tokenList->expectKeyword(Keyword::JOIN);
                $right = $this->parseTableReference($tokenList);

                $left = new NaturalJoin($left, $right, $side);
                continue;
            }

            $side = $tokenList->getKeywordEnum(JoinSide::class);
            if ($side !== null) {
                // {LEFT|RIGHT} [OUTER] JOIN
                $tokenList->passKeyword(Keyword::OUTER);
                $tokenList->expectKeyword(Keyword::JOIN);
                $right = $this->parseTableReferenceInternal($tokenList);
                [$on, $using] = $this->parseJoinCondition($tokenList);

                $left = new OuterJoin($left, $right, $side, $on, $using);
                continue;
            }

            if ($tokenList->hasKeyword(Keyword::STRAIGHT_JOIN)) {
                // STRAIGHT_JOIN
                $right = $this->parseTableReference($tokenList);
                [$on, $using] = $this->parseJoinCondition($tokenList);

                $left = new StraightJoin($left, $right, $on, $using);
                continue;
            }

            $keyword = $tokenList->getAnyKeyword(Keyword::INNER, Keyword::CROSS, Keyword::JOIN);
            if ($keyword !== null) {
                // [INNER | CROSS] JOIN
                $cross = false;
                if ($keyword === Keyword::INNER) {
                    $tokenList->expectKeyword(Keyword::JOIN);
                } elseif ($keyword === Keyword::CROSS) {
                    $tokenList->expectKeyword(Keyword::JOIN);
                    $cross = true;
                }
                $right = $this->parseTableReference($tokenList);
                [$on, $using] = $this->parseJoinCondition($tokenList);

                $left = new InnerJoin($left, $right, $cross, $on, $using);
                continue;
            }

            return $left;
        } while (true);
    }

    /**
     * table_factor:
     *     tbl_name [PARTITION (partition_names)] [[AS] alias] [index_hint_list]
     *   | [LATERAL] [(] table_subquery [)] [AS] alias [(col_list)]
     *   | ( table_references )
     */
    private function parseTableFactor(TokenList $tokenList): TableReferenceNode
    {
        $position = $tokenList->getPosition();

        if ($tokenList->hasKeyword(Keyword::JSON_TABLE)) {
            $tokenList->expectSymbol('(');
            $table = $this->expressionParser->parseJsonTable($tokenList->rewind($position));
            $alias = $this->expressionParser->parseAlias($tokenList);
            if ($alias === null) {
                throw new ParserException('Every json_table call must have an alias.', $tokenList);
            }

            return new TableReferenceJsonTable($table, $alias);
        }

        $lateral = $tokenList->hasKeyword(Keyword::LATERAL);
        if ($lateral) {
            $query = $this->parseSubquery($tokenList);
            [$alias, $columns] = $this->parseAliasAndColumns($tokenList);

            return new TableReferenceSubquery($query, $alias, $columns, true);
        } elseif ($tokenList->hasAnyKeyword(Keyword::SELECT, Keyword::TABLE, Keyword::VALUES, Keyword::WITH)) {
            $query = $this->parseSubquery($tokenList->rewind($position));
            [$alias, $columns] = $this->parseAliasAndColumns($tokenList);

            return new TableReferenceSubquery($query, $alias, $columns, false);
        } elseif ($tokenList->hasSymbol('(')) {
            // any number of open parens
            $openParens = 0;
            $mayBeQuery = $isQuery = false;
            do {
                if ($tokenList->hasAnyKeyword(Keyword::SELECT, Keyword::TABLE, Keyword::VALUES, Keyword::WITH)) {
                    $mayBeQuery = true;
                }
                $openParens++;
            } while ($tokenList->hasSymbol('('));
            // seek forward to find out if an alias follows
            if ($mayBeQuery) {
                do {
                    $token = $tokenList->get();
                    if ($token === null) {
                        break;
                    } elseif ($token->value === '(' && ($token->type & TokenType::SYMBOL) !== 0) {
                        $openParens++;
                    } elseif ($token->value === ')' && ($token->type & TokenType::SYMBOL) !== 0) {
                        $openParens--;
                        if ($openParens === 0) {
                            if ($tokenList->hasKeyword(Keyword::AS)) {
                                $isQuery = true;
                            } elseif ($tokenList->getNonKeywordName(EntityType::ALIAS) !== null) {
                                $isQuery = true;
                            }
                        }
                    }
                } while (true);
            }

            if ($isQuery) {
                $query = $this->parseSubquery($tokenList->rewind($position));
                [$alias, $columns] = $this->parseAliasAndColumns($tokenList);

                return new TableReferenceSubquery($query, $alias, $columns, false);
            } else {
                $tokenList->rewind($position);
                $tokenList->expectSymbol('(');
                $references = $this->parseTableReferences($tokenList);
                $tokenList->expectSymbol(')');
                if ($references instanceof TableReferenceSubquery) {
                    // throws, because there is no alias
                    $this->parseAliasAndColumns($tokenList);
                }

                return new TableReferenceParentheses($references);
            }
        } elseif ($tokenList->hasKeyword(Keyword::DUAL)) {
            return new TableReferenceTable(new SimpleName(Keyword::DUAL));
        } else {
            // tbl_name [PARTITION (partition_names)] [[AS] alias] [index_hint_list]
            $table = $tokenList->expectObjectIdentifier();
            $partitions = null;
            if ($tokenList->hasKeyword(Keyword::PARTITION)) {
                $tokenList->expectSymbol('(');
                $partitions = [];
                do {
                    // phpcs:ignore SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.NoAssignment
                    /** @var non-empty-list<string> $partitions */
                    $partitions[] = $tokenList->expectName(EntityType::PARTITION);
                } while ($tokenList->hasSymbol(','));
                $tokenList->expectSymbol(')');
            }
            $alias = $this->expressionParser->parseAlias($tokenList);

            $indexHints = null;
            if ($tokenList->hasAnyKeyword(Keyword::USE, Keyword::IGNORE, Keyword::FORCE)) {
                $indexHints = $this->parseIndexHints($tokenList->rewind(-1));
            }

            return new TableReferenceTable($table, $alias, $partitions, $indexHints);
        }
    }

    /**
     * @return Query&Statement
     */
    private function parseSubquery(TokenList $tokenList): Query
    {
        $tokenList->startSubquery(SubqueryType::FROM);
        $query = ($this->queryParserProxy)()->parseQuery($tokenList);
        $tokenList->endSubquery();

        return $query;
    }

    /**
     * @return array{string|null, non-empty-list<string>|null}
     */
    private function parseAliasAndColumns(TokenList $tokenList): array
    {
        $alias = $this->expressionParser->parseAlias($tokenList, true);

        $columns = null;
        if ($tokenList->hasSymbol('(')) {
            $columns = [];
            do {
                $columns[] = $tokenList->expectNonReservedName(EntityType::COLUMN);
            } while ($tokenList->hasSymbol(','));
            $tokenList->expectSymbol(')');
        }

        return [$alias, $columns];
    }

    /**
     * @return array{RootNode|null, non-empty-list<string>|null}
     */
    private function parseJoinCondition(TokenList $tokenList): array
    {
        $on = $using = null;
        if ($tokenList->hasKeyword(Keyword::ON)) {
            $on = $this->expressionParser->parseExpression($tokenList);
        } elseif ($tokenList->hasKeyword(Keyword::USING)) {
            $tokenList->expectSymbol('(');
            $using = [];
            do {
                $using[] = $tokenList->expectName(EntityType::COLUMN);
            } while ($tokenList->hasSymbol(','));
            $tokenList->expectSymbol(')');
        }

        return [$on, $using];
    }

    /**
     * index_hint_list:
     *     index_hint [[,] index_hint] ...
     *
     * index_hint:
     *     USE {INDEX|KEY} [FOR {JOIN|ORDER BY|GROUP BY}] ([index_list])
     *   | IGNORE {INDEX|KEY} [FOR {JOIN|ORDER BY|GROUP BY}] (index_list)
     *   | FORCE {INDEX|KEY} [FOR {JOIN|ORDER BY|GROUP BY}] (index_list)
     *
     * index_list:
     *     index_name [, index_name] ...
     *
     * @return non-empty-list<IndexHint>
     */
    private function parseIndexHints(TokenList $tokenList): array
    {
        $hints = [];
        $action = $tokenList->expectKeywordEnum(IndexHintAction::class);
        do {
            $tokenList->getAnyKeyword(Keyword::INDEX, Keyword::KEY);
            $target = null;
            if ($tokenList->hasKeyword(Keyword::FOR)) {
                $target = $tokenList->expectMultiKeywordsEnum(IndexHintTarget::class);
            }

            $tokenList->expectSymbol('(');
            $indexes = [];
            if (!$tokenList->hasSymbol(')')) {
                do {
                    if ($tokenList->hasKeyword(Keyword::PRIMARY)) {
                        $indexes[] = new PrimaryLiteral();
                    } else {
                        $indexes[] = $tokenList->expectName(EntityType::INDEX);
                    }
                } while ($tokenList->hasSymbol(','));
                $tokenList->expectSymbol(')');
            }
            if (!$action->equalsValue(IndexHintAction::USE) && $indexes === []) {
                throw new ParserException('Indexes cannot be empty for action ' . $action->getValue() . '.', $tokenList);
            }

            $hints[] = new IndexHint($action, $target, $indexes);
            $beforeComma = $tokenList->getPosition();
        } while (
            // phpcs:ignore SlevomatCodingStandard.ControlStructures.AssignmentInCondition
            (($trailingComma = $tokenList->hasSymbol(',')) && ($action = $tokenList->getKeywordEnum(IndexHintAction::class)) !== null)
            || ($action = $tokenList->getKeywordEnum(IndexHintAction::class)) !== null
        );

        if ($trailingComma) {
            $tokenList->rewind($beforeComma);
        }

        return $hints;
    }

}
