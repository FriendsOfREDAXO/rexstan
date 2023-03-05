<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable PSR2.Methods.FunctionCallSignature.MultipleArguments

namespace SqlFtw\Parser\Dml;

use LogicException;
use SqlFtw\Parser\ExpressionParser;
use SqlFtw\Parser\InvalidTokenException;
use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Parser\TokenType;
use SqlFtw\Sql\Ddl\Table\Option\StorageEngine;
use SqlFtw\Sql\Dml\OptimizerHint\HintTableIdentifier;
use SqlFtw\Sql\Dml\OptimizerHint\IndexLevelHint;
use SqlFtw\Sql\Dml\OptimizerHint\JoinFixedOrderHint;
use SqlFtw\Sql\Dml\OptimizerHint\JoinOrderHint;
use SqlFtw\Sql\Dml\OptimizerHint\MaxExecutionTimeHint;
use SqlFtw\Sql\Dml\OptimizerHint\NameWithQueryBlock;
use SqlFtw\Sql\Dml\OptimizerHint\OptimizerHint;
use SqlFtw\Sql\Dml\OptimizerHint\OptimizerHintType;
use SqlFtw\Sql\Dml\OptimizerHint\QueryBlockNameHint;
use SqlFtw\Sql\Dml\OptimizerHint\ResourceGroupHint;
use SqlFtw\Sql\Dml\OptimizerHint\SemijoinHint;
use SqlFtw\Sql\Dml\OptimizerHint\SemijoinHintStrategy;
use SqlFtw\Sql\Dml\OptimizerHint\SetVarHint;
use SqlFtw\Sql\Dml\OptimizerHint\SubqueryHint;
use SqlFtw\Sql\Dml\OptimizerHint\SubqueryHintStrategy;
use SqlFtw\Sql\Dml\OptimizerHint\TableLevelHint;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\EnumValueLiteral;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\QualifiedName;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\MysqlVariable;
use function substr;

class OptimizerHintParser
{

    private ExpressionParser $expressionParser;

    public function __construct(ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }

    /**
     * SELECT /*+ ... * / ...
     * INSERT /*+ ... * / ...
     * REPLACE /*+ ... * / ...
     * UPDATE /*+ ... * / ...
     * DELETE /*+ ... * / ...
     *
     * (SELECT /*+ ... * / ... )
     * (SELECT ... ) UNION (SELECT /*+ ... * / ... )
     * (SELECT /*+ ... * / ... ) UNION (SELECT /*+ ... * / ... )
     * UPDATE ... WHERE x IN (SELECT /*+ ... * / ...)
     * INSERT ... SELECT /*+ ... * / ...
     *
     * EXPLAIN SELECT /*+ ... * / ...
     * EXPLAIN UPDATE ... WHERE x IN (SELECT /*+ ... * / ...)
     *
     * optimizer_hint:
     *     join_fixed_order_hint | join_order_hint | table_level_hint | index_level_hint | subquery_hint
     *   | statement_execution_hint | variable_setting_hint | resource_group_hint | named_query_block_hint
     *
     * join_fixed_order_hint:
     *     JOIN_FIXED_ORDER([@query_block_name])
     *
     * join_order_hint:
     *     join_order_hint_name([@query_block_name] tbl_name [, tbl_name] ...)
     *   | join_order_hint_name(tbl_name[@query_block_name] [, tbl_name[@query_block_name]] ...)
     *
     * join_order_hint_name:
     *     JOIN_ORDER | JOIN_PREFIX | JOIN_SUFFIX
     *
     * table_level_hint:
     *     table_level_hint_name([@query_block_name] [tbl_name [, tbl_name] ...])
     *   | table_level_hint_name([tbl_name@query_block_name [, tbl_name@query_block_name] ...])
     *
     * table_level_hint_name:
     *     BKA | NO_BKA | BNL | NO_BNL | DERIVED_CONDITION_PUSHDOWN | NO_DERIVED_CONDITION_PUSHDOWN | HASH_JOIN | NO_HASH_JOIN | MERGE | NO_MERGE
     *
     * index_level_hint:
     *     index_level_hint_name([@query_block_name] tbl_name [index_name [, index_name] ...])
     *   | index_level_hint_name(tbl_name@query_block_name [index_name [, index_name] ...])
     *
     * index_level_hint_name:
     *     GROUP_INDEX | NO_GROUP_INDEX | INDEX | NO_INDEX | INDEX_MERGE | NO_INDEX_MERGE | JOIN_INDEX | NO_JOIN_INDEX
     *   | MRR | NO_MRR | NO_ICP | NO_RANGE_OPTIMIZATION | ORDER_INDEX, NO_ORDER_INDEX | SKIP_SCAN | NO_SKIP_SCAN
     *
     * subquery_hint:
     *     semijoin_hint_name([@query_block_name] [strategy [, strategy] ...])
     *   | SUBQUERY([@query_block_name] {INTOEXISTS | MATERIALIZATION})
     *
     * semijoin_hint_name:
     *     SEMIJOIN | NO_SEMIJOIN
     *
     * strategy:
     *     DUPSWEEDOUT | FIRSTMATCH | LOOSESCAN | MATERIALIZATION
     *
     * statement_execution_hint:
     *     MAX_EXECUTION_TIME(N)
     *
     * variable_setting_hint:
     *     SET_VAR(var_name = value)
     *
     * resource_group_hint:
     *     RESOURCE_GROUP(group_name)
     *
     * named_query_block_hint:
     *     QB_NAME(name)
     *
     * @return non-empty-list<OptimizerHint>|null
     */
    public function parseHints(TokenList $tokenList): ?array
    {
        $tokenList->expect(TokenType::OPTIMIZER_HINT_START);

        $hints = [];
        do {
            try {
                $type = $tokenList->expectNameEnum(OptimizerHintType::class)->getValue();
            } catch (InvalidTokenException $e) {
                // fallback to regular comment (ignored)
                while ($token = $tokenList->get()) {
                    if ($token->type === TokenType::OPTIMIZER_HINT_END) {
                        // todo: parser warning
                        return null;
                    }
                }

                throw new ParserException('End of invalid optimizer hint comment not found.', $tokenList);
            }

            $open = $tokenList->hasSymbol('(');
            switch ($type) {
                case OptimizerHintType::JOIN_FIXED_ORDER:
                    $queryBlock = $this->parseQueryBlockUsageName($tokenList);
                    $hints[] = new JoinFixedOrderHint($queryBlock);
                    break;
                case OptimizerHintType::JOIN_ORDER:
                case OptimizerHintType::JOIN_PREFIX:
                case OptimizerHintType::JOIN_SUFFIX:
                    $queryBlock = $this->parseQueryBlockUsageName($tokenList);

                    $tables = [];
                    do {
                        $tables[] = $this->parseTableName($tokenList);
                    } while ($tokenList->hasSymbol(','));

                    $hints[] = new JoinOrderHint($type, $queryBlock, $tables);
                    break;
                case OptimizerHintType::BATCHED_KEY_ACCESS:
                case OptimizerHintType::NO_BATCHED_KEY_ACCESS:
                case OptimizerHintType::BLOCK_NESTED_LOOPS:
                case OptimizerHintType::NO_BLOCK_NESTED_LOOPS:
                case OptimizerHintType::DERIVED_CONDITION_PUSHDOWN:
                case OptimizerHintType::NO_DERIVED_CONDITION_PUSHDOWN:
                case OptimizerHintType::HASH_JOIN:
                case OptimizerHintType::NO_HASH_JOIN:
                case OptimizerHintType::MERGE:
                case OptimizerHintType::NO_MERGE:
                    $queryBlock = $this->parseQueryBlockUsageName($tokenList);
                    $tables = $this->parseTableNamesOrNull($tokenList);

                    $hints[] = new TableLevelHint($type, $queryBlock, $tables);
                    break;
                case OptimizerHintType::GROUP_INDEX:
                case OptimizerHintType::NO_GROUP_INDEX:
                case OptimizerHintType::INDEX:
                case OptimizerHintType::NO_INDEX:
                case OptimizerHintType::INDEX_MERGE:
                case OptimizerHintType::NO_INDEX_MERGE:
                case OptimizerHintType::JOIN_INDEX:
                case OptimizerHintType::NO_JOIN_INDEX:
                case OptimizerHintType::MULTI_RANGE_READ:
                case OptimizerHintType::NO_MULTI_RANGE_READ:
                case OptimizerHintType::NO_INDEX_CONDITION_PUSHDOWN:
                case OptimizerHintType::NO_RANGE_OPTIMIZATION:
                case OptimizerHintType::ORDER_INDEX:
                case OptimizerHintType::NO_ORDER_INDEX:
                case OptimizerHintType::SKIP_SCAN:
                case OptimizerHintType::NO_SKIP_SCAN:
                    $queryBlock = $this->parseQueryBlockUsageName($tokenList);
                    $table = $this->parseTableName($tokenList);

                    $indexes = null;
                    $index = $tokenList->getName(EntityType::INDEX);
                    if ($index !== null) {
                        $indexes = [$index];
                        while ($tokenList->hasSymbol(',')) {
                            $indexes[] = $tokenList->expectName(EntityType::INDEX);
                        }
                    }

                    $hints[] = new IndexLevelHint($type, $queryBlock, $table, $indexes);
                    break;
                case OptimizerHintType::SEMIJOIN:
                case OptimizerHintType::NO_SEMIJOIN:
                    $queryBlock = $this->parseQueryBlockUsageName($tokenList);

                    $strategies = null;
                    $token = $tokenList->getNameEnum(SemijoinHintStrategy::class);
                    /** @var 'DUPSWEEDOUT'|'FIRSTMATCH'|'LOOSESCAN'|'MATERIALIZATION'|null $strategy */
                    $strategy = $token !== null ? $token->getValue() : null;
                    if ($strategy !== null) {
                        $strategies = [$strategy];
                        while ($tokenList->hasSymbol(',')) {
                            /** @var 'DUPSWEEDOUT'|'FIRSTMATCH'|'LOOSESCAN'|'MATERIALIZATION' $strategy */
                            $strategy = $tokenList->expectNameEnum(SemijoinHintStrategy::class)->getValue();
                            $strategies[] = $strategy;
                        }
                    }

                    $hints[] = new SemijoinHint($type, $queryBlock, $strategies);
                    break;
                case OptimizerHintType::SUBQUERY:
                    $queryBlock = $this->parseQueryBlockUsageName($tokenList);
                    /** @var 'INTOEXISTS'|'MATERIALIZATION' $strategy */
                    $strategy = $tokenList->expectNameEnum(SubqueryHintStrategy::class)->getValue();
                    $hints[] = new SubqueryHint($queryBlock, $strategy);
                    break;
                case OptimizerHintType::MAX_EXECUTION_TIME:
                    $limit = (int) $tokenList->expectUnsignedInt();
                    $hints[] = new MaxExecutionTimeHint($limit);
                    break;
                case OptimizerHintType::SET_VAR:
                    $variableName = $tokenList->expectNonReservedName(EntityType::SYSTEM_VARIABLE);
                    $variable = $this->expressionParser->createSystemVariable($tokenList, $variableName);
                    if (!MysqlVariable::supportsSetVar($variable->getName())) {
                        throw new ParserException("Variable {$variableName} cannot be used in SET_VAR optimizer hint.", $tokenList);
                    }
                    $tokenList->expectOperator(Operator::EQUAL);
                    $type = MysqlVariable::getType($variableName);
                    if ($type === BaseType::ENUM || $type === BaseType::SET) {
                        $value = $tokenList->getVariableEnumValue(...MysqlVariable::getValues($variableName));
                        if ($value !== null) {
                            $value = new EnumValueLiteral((string) $value);
                        } else {
                            $value = $this->expressionParser->parseLiteral($tokenList);
                        }
                    } elseif ($type === StorageEngine::class) {
                        $value = $tokenList->expectStorageEngineName();
                    } else {
                        $value = $this->expressionParser->parseLiteral($tokenList);
                    }
                    $hints[] = new SetVarHint($variable, $value);
                    break;
                case OptimizerHintType::RESOURCE_GROUP:
                    $group = $tokenList->expectName(EntityType::RESOURCE_GROUP);
                    $hints[] = new ResourceGroupHint($group);
                    break;
                case OptimizerHintType::QUERY_BLOCK_NAME:
                    $queryBlock = $tokenList->expectName(EntityType::QUERY_BLOCK);
                    $hints[] = new QueryBlockNameHint($queryBlock);
                    break;
                default:
                    throw new LogicException("Unknown optimizer hint type {$type}.");
            }
            if ($open) {
                $tokenList->expectSymbol(')');
            }
        } while (!$tokenList->has(TokenType::OPTIMIZER_HINT_END));

        return $hints;
    }

    private function parseQueryBlockUsageName(TokenList $tokenList): ?string
    {
        $queryBlock = $tokenList->get(TokenType::AT_VARIABLE);

        if ($queryBlock === null) {
            return null;
        }

        $queryBlock = substr($queryBlock->value, 1);
        $tokenList->validateName(EntityType::QUERY_BLOCK, $queryBlock);

        return $queryBlock;
    }

    /**
     * @return non-empty-list<HintTableIdentifier>|null
     */
    private function parseTableNamesOrNull(TokenList $tokenList): ?array
    {
        if ($tokenList->hasSymbol(')')) {
            $tokenList->rewind(-1);

            return null;
        }

        $tables = [];
        do {
            $tables[] = $this->parseTableName($tokenList);
        } while ($tokenList->hasSymbol(','));

        return $tables;
    }

    private function parseTableName(TokenList $tokenList): HintTableIdentifier
    {
        $name1 = $tokenList->expectName(EntityType::TABLE);
        if ($tokenList->hasSymbol('.')) {
            $name2 = $tokenList->expectName(EntityType::TABLE);
            $table = new QualifiedName($name2, $name1);
        } else {
            $table = new SimpleName($name1);
        }

        $queryBlock = $tokenList->get(TokenType::AT_VARIABLE);
        if ($queryBlock !== null) {
            $queryBlock = substr($queryBlock->value, 1);
            $tokenList->validateName(EntityType::QUERY_BLOCK, $queryBlock);

            return new NameWithQueryBlock($table, $queryBlock);
        } else {
            return $table;
        }
    }

}
