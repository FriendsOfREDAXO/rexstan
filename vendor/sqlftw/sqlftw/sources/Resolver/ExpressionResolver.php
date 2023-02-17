<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver;

use LogicException;
use SqlFtw\Resolver\Functions\Functions;
use SqlFtw\Session\Session;
use SqlFtw\Sql\Dml\Query\ParenthesizedQueryExpression;
use SqlFtw\Sql\Dml\Query\QueryExpression;
use SqlFtw\Sql\Dml\Query\SelectCommand;
use SqlFtw\Sql\Dml\Query\TableCommand;
use SqlFtw\Sql\Dml\Query\ValuesCommand;
use SqlFtw\Sql\Dml\TableReference\TableReferenceTable;
use SqlFtw\Sql\Expression\ArgumentValue;
use SqlFtw\Sql\Expression\AssignOperator;
use SqlFtw\Sql\Expression\Asterisk;
use SqlFtw\Sql\Expression\BinaryOperator;
use SqlFtw\Sql\Expression\BoolLiteral;
use SqlFtw\Sql\Expression\BuiltInFunction;
use SqlFtw\Sql\Expression\CaseExpression;
use SqlFtw\Sql\Expression\CollateExpression;
use SqlFtw\Sql\Expression\ComparisonOperator;
use SqlFtw\Sql\Expression\CurlyExpression;
use SqlFtw\Sql\Expression\EnumValueLiteral;
use SqlFtw\Sql\Expression\ExistsExpression;
use SqlFtw\Sql\Expression\ExpressionNode;
use SqlFtw\Sql\Expression\FunctionCall;
use SqlFtw\Sql\Expression\Identifier;
use SqlFtw\Sql\Expression\ListExpression;
use SqlFtw\Sql\Expression\Literal;
use SqlFtw\Sql\Expression\MatchExpression;
use SqlFtw\Sql\Expression\NullLiteral;
use SqlFtw\Sql\Expression\NumericLiteral;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\OperatorExpression;
use SqlFtw\Sql\Expression\Parentheses;
use SqlFtw\Sql\Expression\QualifiedName;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\Expression\RowExpression;
use SqlFtw\Sql\Expression\Scope;
use SqlFtw\Sql\Expression\StringLiteral;
use SqlFtw\Sql\Expression\Subquery;
use SqlFtw\Sql\Expression\SystemVariable;
use SqlFtw\Sql\Expression\TernaryOperator;
use SqlFtw\Sql\Expression\TimeInterval;
use SqlFtw\Sql\Expression\TimeIntervalExpression;
use SqlFtw\Sql\Expression\TimeIntervalLiteral;
use SqlFtw\Sql\Expression\UnaryOperator;
use SqlFtw\Sql\Expression\UnresolvedExpression;
use SqlFtw\Sql\Expression\UserVariable;
use SqlFtw\Sql\Expression\Value;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlMode;
use function call_user_func_array;
use function count;
use function get_class;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function method_exists;
use function round;
use function strtoupper;

/**
 * Simplifies or resolves SQL expressions
 *
 * Limitations:
 * - All strings are handled like UTF-8 (ignoring charsets and collations)
 * - Binary and text strings are not distinguished between (always treated as both)
 * - No unsigned integers (possible overflow on values > 63 bits)
 * - Decimal numbers calculated using float (not exact)
 */
class ExpressionResolver
{

    private Session $session;

    private Cast $cast;

    private Functions $functions;

    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->cast = new Cast();
        $this->functions = new Functions($session, $this->cast);
    }

    public function cast(): Cast
    {
        return $this->cast;
    }

    /**
     * @return scalar|Value|ExpressionNode|list<scalar|Value|ExpressionNode|list<scalar|Value|ExpressionNode|null>|null>|null
     */
    public function resolve(ExpressionNode $expression)
    {
        return $this->process($expression);
    }

    /**
     * @return scalar|Value|ExpressionNode|list<scalar|Value|ExpressionNode|list<scalar|Value|ExpressionNode|null>|null>|null
     */
    public function process(ExpressionNode $expression)
    {
        if ($expression instanceof CaseExpression) {
            // todo
        } elseif ($expression instanceof CollateExpression) {
            $this->session->startCollation($expression->getCollation());
            $result = $this->process($expression->getExpression());
            $this->session->endCollation();

            return $result;
        } elseif ($expression instanceof CurlyExpression) {
            // todo
        } elseif ($expression instanceof ExistsExpression) {
            // todo
        } elseif ($expression instanceof FunctionCall) {
            return $this->processFunctionCall($expression);
        } elseif ($expression instanceof TimeInterval) {
            return $this->processTimeInterval($expression);
        } elseif ($expression instanceof MatchExpression) {
            // todo
        } elseif ($expression instanceof Parentheses) {
            return $this->process($expression->getContents());
        } elseif ($expression instanceof RowExpression) {
            // todo
        } elseif ($expression instanceof Identifier) {
            return $this->processIdentifier($expression);
        } elseif ($expression instanceof Literal) {
            return $this->processLiteral($expression);
        } elseif ($expression instanceof ListExpression) {
            return $this->processList($expression); // @phpstan-ignore-line (dimensionality)
        } elseif ($expression instanceof OperatorExpression) {
            return $this->processOperator($expression);
        } elseif ($expression instanceof Subquery) {
            return $this->processSubquery($expression); // @phpstan-ignore-line (dimensionality)
        } else {
            // todo: throw
        }

        return $expression;
    }

    /**
     * @return scalar|Value|FunctionCall|null
     */
    private function processFunctionCall(FunctionCall $expression)
    {
        $function = $expression->getFunction();
        if ($function instanceof BuiltInFunction && !$function->isSimple()) {
            return $expression;
        }

        if ($function instanceof BuiltInFunction) {
            $method = $function->getValue();
        } elseif ($function instanceof QualifiedName) {
            $method = $function->getSchema() . '__' . $function->getName();
        } else {
            $schema = $this->session->getSchema();
            $method = $schema !== null ? ($schema . '__' . $function->getName()) : $function->getName();
        }

        if (!method_exists($this->functions, $method)) {
            return $expression;
        }

        $arguments = $expression->getArguments();
        $args = [];
        foreach ($arguments as $argument) {
            $argument = $this->process($argument);
            if (!ExpressionHelper::isValueOrArray($argument) && !$argument instanceof ArgumentValue) {
                return $expression;
            }
            $args[] = $argument;
        }

        try {
            /** @var callable $function */
            $function = [$this->functions, $method];
            /** @var scalar|Value|null $result */
            $result = call_user_func_array($function, $args);
        } catch (UnresolvableException $e) {
            return $expression;
        }

        return $result;
    }

    /**
     * @return scalar|Value|Identifier|null
     */
    private function processIdentifier(Identifier $expression)
    {
        if ($expression instanceof UserVariable) {
            $value = $this->session->getUserVariable($expression->getName());
            ///rl('read ' . $expression->getName());
            ///rd($value);
            if ($value instanceof UnresolvedExpression) {
                return $expression;
            } else {
                return $value;
            }
        } elseif ($expression instanceof SystemVariable) {
            $scope = $expression->getScope();
            if ($scope === null || $scope->equalsValue(Scope::SESSION)) {
                $value = $this->session->getSessionVariable($expression->getName());
            } elseif ($scope->equalsValue(Scope::GLOBAL)) {
                $value = $this->session->getGlobalVariable($expression->getName());
            } else {
                throw new ResolveException('Cannot read variables with PERSIST or PERSIST_ONLY scope.');
            }
            ///rl('read ' . ($scope ? $scope->getValue() . '.' : '') . $expression->getName());
            ///rd($value);
            if ($value instanceof UnresolvedExpression) {
                return $expression;
            } else {
                return $value;
            }
        } else {
            return $expression;
        }
    }

    /**
     * @return scalar|Literal|null
     */
    private function processLiteral(Literal $expression)
    {
        if ($expression instanceof StringLiteral || $expression instanceof EnumValueLiteral) {
            return $expression->getValue();
        } elseif ($expression instanceof NumericLiteral) {
            return $expression->asNumber();
        } elseif ($expression instanceof BoolLiteral || $expression instanceof NullLiteral) {
            return $expression->asBool();
        } else {
            return $expression;
        }
    }

    private function processTimeInterval(TimeInterval $expression): TimeInterval
    {
        if (!$expression instanceof TimeIntervalExpression) {
            return $expression;
        }

        $value = $this->process($expression->getExpression());
        $unit = $expression->getUnit();
        if (is_int($value)) {
            return new TimeIntervalLiteral((string) $value, $unit);
        } elseif (is_string($value)) {
            return new TimeIntervalLiteral($value, $unit);
        } elseif (is_float($value)) {
            return new TimeIntervalLiteral((string) round($value), $unit);
        } elseif (is_bool($value)) {
            return new TimeIntervalLiteral((string) (int) $value, $unit);
        } else {
            return $expression;
        }
    }

    /**
     * @return scalar|Value|OperatorExpression|null
     */
    private function processOperator(OperatorExpression $expression)
    {
        if ($expression instanceof AssignOperator) {
            $value = $this->process($expression->getExpression());
            //$variable = $expression->getVariable();

            if (!ExpressionHelper::isValue($value)) {
                /// ???
                //$this->session->setUserVariable($variable->getName(), new UnresolvedExpression($value));

                return $expression;
            } else {
                /// ???
                //$this->session->setUserVariable($variable->getName(), $value);

                return $value; // @phpstan-ignore-line (checked)
            }
        } elseif ($expression instanceof BinaryOperator) {
            /** @var scalar|Value|null $left */
            $left = $this->process($expression->getLeft());
            /** @var scalar|Value|null $right */
            $right = $this->process($expression->getRight());
            if (!ExpressionHelper::isValue($left) || !ExpressionHelper::isValue($right)) {
                return $expression;
            }

            $operator = $expression->getOperator();
            switch ($operator->getValue()) {
                case Operator::AMPERSANDS:
                case Operator::AND:
                    return $this->functions->_and($left, $right);
                case Operator::PIPES:
                    if ($this->session->getMode()->containsAny(SqlMode::PIPES_AS_CONCAT)) {
                        return $this->functions->concat($left, $right);
                    }
                case Operator::OR:
                    return $this->functions->_or($left, $right);
                case Operator::XOR:
                    return $this->functions->_xor($left, $right);
                case Operator::PLUS:
                    return $this->functions->_plus($left, $right);
                case Operator::MINUS:
                    return $this->functions->_minus($left, $right);
                case Operator::MULTIPLY:
                    return $this->functions->_multiply($left, $right);
                case Operator::DIVIDE:
                    return $this->functions->_divide($left, $right);
                case Operator::DIV:
                    return $this->functions->_div($left, $right);
                case Operator::MOD:
                case Operator::MODULO:
                    return $this->functions->mod($left, $right);
                case Operator::BIT_AND:
                    return $this->functions->_bit_and($left, $right);
                case Operator::BIT_OR:
                    return $this->functions->_bit_or($left, $right);
                case Operator::BIT_XOR:
                    return $this->functions->_bit_xor($left, $right);
                case Operator::LEFT_SHIFT:
                    return $this->functions->_left_shift($left, $right);
                case Operator::RIGHT_SHIFT:
                    return $this->functions->_right_shift($left, $right);
                case Operator::IS:
                    if (!$right instanceof BoolLiteral) {
                        return $expression;
                    }
                    return $this->functions->_is($left, $right);
                case Operator::IS_NOT:
                    if (!$right instanceof BoolLiteral) {
                        return $expression;
                    }
                    return $this->functions->_is_not($left, $right);
                case Operator::LIKE:
                    return $this->functions->_like($left, $right);
                case Operator::NOT_LIKE:
                    return $this->functions->_not_like($left, $right);
                case Operator::REGEXP:
                case Operator::RLIKE:
                    return $this->functions->_regexp($left, $right);
                case Operator::NOT_REGEXP:
                case Operator::NOT_RLIKE:
                    return $this->functions->_not_regexp($left, $right);
                case Operator::SOUNDS_LIKE:
                    return $this->functions->_sounds_like($left, $right);
                case Operator::JSON_EXTRACT:
                    return $this->functions->_json_extract($left, $right);
                case Operator::JSON_EXTRACT_UNQUOTE:
                    return $this->functions->_json_extract_unquote($left, $right);
                default:
                    throw new LogicException("Unknown operator: {$operator->getValue()}.");
            }
        } elseif ($expression instanceof ComparisonOperator) {
            $quantifier = $expression->getQuantifier();
            if ($quantifier !== null) {
                // not supported
                return $expression;
            }

            /** @var scalar|Value|null $left */
            $left = $this->process($expression->getLeft());
            /** @var scalar|Value|list<scalar|Value|null>|null $right */
            $right = $this->process($expression->getRight());
            if (!ExpressionHelper::isValueOrArray($left) || !ExpressionHelper::isValueOrArray($right)) {
                return $expression;
            }

            $operator = $expression->getOperator();
            switch ($operator->getValue()) {
                case Operator::EQUAL:
                    return $this->functions->_equal($left, $right);
                case Operator::NOT_EQUAL:
                case Operator::LESS_OR_GREATER:
                    return $this->functions->_not_equal($left, $right);
                case Operator::LESS:
                    return $this->functions->_less($left, $right);
                case Operator::LESS_OR_EQUAL:
                    return $this->functions->_less_or_equal($left, $right);
                case Operator::GREATER:
                    return $this->functions->_greater($left, $right);
                case Operator::GREATER_OR_EQUAL:
                    return $this->functions->_greater_or_equal($left, $right);
                case Operator::SAFE_EQUAL:
                    return $this->functions->_safe_equal($left, $right);
                case Operator::IN:
                    return $this->functions->_in($left, $right); // @phpstan-ignore-line (dimensionality)
                case Operator::NOT_IN:
                    return $this->functions->_not_in($left, $right); // @phpstan-ignore-line (dimensionality)
                case Operator::LIKE:
                    return $this->functions->_like($left, $right); // @phpstan-ignore-line (dimensionality)
                case Operator::NOT_LIKE:
                    return $this->functions->_not_like($left, $right); // @phpstan-ignore-line (dimensionality)
                default:
                    throw new LogicException("Unknown operator {$operator->getValue()}.");
            }
        } elseif ($expression instanceof TernaryOperator) {
            /** @var scalar|Value|null $left */
            $left = $this->process($expression->getLeft());
            /** @var scalar|Value|null $middle */
            $middle = $this->process($expression->getMiddle());
            /** @var scalar|Value|null $right */
            $right = $this->process($expression->getRight());
            if (!ExpressionHelper::isValue($left) || !ExpressionHelper::isValue($middle) || !ExpressionHelper::isValue($right)) {
                return $expression;
            }

            $operator = $expression->getLeftOperator();
            switch ($operator->getValue()) {
                case Operator::BETWEEN:
                    return $this->functions->_between($left, $middle, $right);
                case Operator::NOT_BETWEEN:
                    return $this->functions->_not_between($left, $middle, $right);
                case Operator::LIKE:
                    return $this->functions->_like($left, $middle, $right);
                case Operator::NOT_LIKE:
                    return $this->functions->_not_like($left, $middle, $right);
                default:
                    throw new LogicException("Unknown operator {$operator->getValue()}.");
            }
        } elseif ($expression instanceof UnaryOperator) {
            /** @var scalar|Value|null $right */
            $right = $this->process($expression->getRight());
            if (!ExpressionHelper::isValue($right)) {
                return $expression;
            }

            $operator = $expression->getOperator();
            switch ($operator->getValue()) {
                case Operator::NOT:
                case Operator::EXCLAMATION:
                    return $this->functions->_not($right);
                case Operator::BIT_INVERT:
                    return $this->functions->_bit_inv($right);
                case Operator::PLUS:
                    return $this->functions->_unary_plus($right);
                case Operator::MINUS:
                    return $this->functions->_unary_minus($right);
                default:
                    throw new LogicException("Unknown operator {$operator->getValue()}.");
            }
        } else {
            throw new LogicException("Unknown operator expression type: " . get_class($expression));
        }
    }

    /**
     * @return list<scalar|Value|UnresolvedExpression|null>|Subquery
     */
    private function processSubquery(Subquery $expression)
    {
        $query = $expression->getQuery();
        if ($query instanceof TableCommand) {
            return $expression;
        } elseif ($query instanceof ValuesCommand) {
            if (count($query->getRows()) === 1) {
                $result = $this->processValues($query);
                if (is_array($result)) {
                    return $result;
                }
            }
            // todo: possible with LIMIT 1, but have to resolve ORDER BY
        } elseif ($query instanceof SelectCommand) {
            if ($this->isSimpleSelect($query)) {
                $result = $this->processSelect($query);
                if (is_array($result)) {
                    return $result;
                }
            }
        } elseif ($query instanceof ParenthesizedQueryExpression) {
            // todo: possible with LIMIT 1, but have to resolve ORDER BY
        } elseif ($query instanceof QueryExpression) {
            // todo: possible with LIMIT 1, but have to resolve ORDER BY
        } else {
            throw new LogicException("Unknown subquery type:" . get_class($query));
        }

        return $expression;
    }

    /**
     * "Simple SELECT" is a SELECT statement, that does not read any tables and reliably returns exactly one row
     */
    public function isSimpleSelect(SelectCommand $select): bool
    {
        $from = $select->getFrom();
        while ($from !== null) {
            // FROM DUAL allowed
            if ($from instanceof TableReferenceTable && strtoupper($from->getTable()->getFullName()) === Keyword::DUAL) {
                break;
            }
            // todo: check other allowed states (simple subselect...)
            return false;
        }

        // might prevent returning result
        if ($select->getWhere() !== null || $select->getHaving() !== null) {
            return false;
        }

        return $select->getLimit() !== 0;
    }

    // lists -----------------------------------------------------------------------------------------------------------

    /**
     * @return list<scalar|Value|UnresolvedExpression|null>|SelectCommand
     */
    public function processSelect(SelectCommand $query, bool $allowUnresolved = false)
    {
        $expressions = [];
        foreach ($query->getColumns() as $column) {
            $expressions[] = $column->getExpression();
        }
        $values = $this->processExpressions($expressions, $allowUnresolved);

        return $values ?? $query;
    }

    /**
     * @return list<scalar|Value|UnresolvedExpression|null>|ValuesCommand
     */
    private function processValues(ValuesCommand $query)
    {
        $rows = $query->getRows();
        $expressions = $rows[0]->getValues();
        $values = $this->processExpressions($expressions);

        return $values ?? $query;
    }

    /**
     * @return list<scalar|Value|UnresolvedExpression|null>|ListExpression
     */
    private function processList(ListExpression $list)
    {
        $values = $this->processExpressions($list->getItems());

        return $values ?? $list;
    }

    /**
     * @param list<RootNode|Asterisk> $expressions
     * @return list<scalar|Value|UnresolvedExpression|null>|null
     */
    private function processExpressions(array $expressions, bool $allowUnresolved = false): ?array
    {
        $values = [];
        foreach ($expressions as $expression) {
            $value = $expression instanceof RootNode ? $this->process($expression) : $expression;
            if (!ExpressionHelper::isValue($value)) {
                /** @var RootNode|Asterisk $value */
                $value = $value;
                if (!$allowUnresolved) {
                    return null;
                } elseif (!$value instanceof UnresolvedExpression) {
                    $value = new UnresolvedExpression($value);
                }
            }
            $values[] = $value;
        }

        return $values; // @phpstan-ignore-line (dimensionality)
    }

}
