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
use SqlFtw\Sql\Dml\Error\ConditionInformationItem;
use SqlFtw\Sql\Dml\Error\DiagnosticsArea;
use SqlFtw\Sql\Dml\Error\DiagnosticsItem;
use SqlFtw\Sql\Dml\Error\GetDiagnosticsCommand;
use SqlFtw\Sql\Dml\Error\ResignalCommand;
use SqlFtw\Sql\Dml\Error\SignalCommand;
use SqlFtw\Sql\Dml\Error\SqlStateCategory;
use SqlFtw\Sql\Dml\Error\StatementInformationItem;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\Identifier;
use SqlFtw\Sql\Expression\IntLiteral;
use SqlFtw\Sql\Expression\NullLiteral;
use SqlFtw\Sql\Expression\NumericLiteral;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\Expression\StringLiteral;
use SqlFtw\Sql\Expression\UintLiteral;
use SqlFtw\Sql\Expression\UserVariable;
use SqlFtw\Sql\Keyword;

class ErrorCommandsParser
{

    private ExpressionParser $expressionParser;

    public function __construct(ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }

    /**
     * GET [CURRENT | STACKED] DIAGNOSTICS
     * {
     *     statement_information_item
     *  [, statement_information_item] ...
     *   | CONDITION condition_number
     *     condition_information_item
     *  [, condition_information_item] ...
     * }
     *
     * statement_information_item:
     *     target = statement_information_item_name
     *
     * condition_information_item:
     *     target = condition_information_item_name
     *
     * statement_information_item_name:
     *     NUMBER
     *   | ROW_COUNT
     *
     * condition_information_item_name:
     *     CLASS_ORIGIN
     *   | SUBCLASS_ORIGIN
     *   | RETURNED_SQLSTATE
     *   | MESSAGE_TEXT
     *   | MYSQL_ERRNO
     *   | CONSTRAINT_CATALOG
     *   | CONSTRAINT_SCHEMA
     *   | CONSTRAINT_NAME
     *   | CATALOG_NAME
     *   | SCHEMA_NAME
     *   | TABLE_NAME
     *   | COLUMN_NAME
     *   | CURSOR_NAME
     *
     * condition_number, target:
     *     (see following discussion)
     */
    public function parseGetDiagnostics(TokenList $tokenList): GetDiagnosticsCommand
    {
        $tokenList->expectKeyword(Keyword::GET);

        $area = $tokenList->getKeywordEnum(DiagnosticsArea::class);
        $tokenList->expectKeyword(Keyword::DIAGNOSTICS);

        $statementItems = $conditionItems = $condition = null;
        if ($tokenList->hasKeyword(Keyword::CONDITION)) {
            $condition = $this->expressionParser->parseExpression($tokenList);
            if (($condition instanceof IntLiteral && !$condition instanceof UintLiteral) || (
                    !$condition instanceof StringLiteral
                    && !$condition instanceof NumericLiteral
                    && !$condition instanceof SimpleName
                    && !$condition instanceof UserVariable
                    && !$condition instanceof NullLiteral
                )
            ) {
                throw new ParserException('Only unsigned int, null or variable names is allowed as condition number.', $tokenList);
            }
            $conditionItems = [];
            do {
                $target = $this->parseTarget($tokenList);
                $tokenList->expectOperator(Operator::EQUAL);
                $item = $tokenList->expectKeywordEnum(ConditionInformationItem::class);
                $conditionItems[] = new DiagnosticsItem($target, $item);
            } while ($tokenList->hasSymbol(','));
        } else {
            $statementItems = [];
            do {
                $target = $this->parseTarget($tokenList);
                $tokenList->expectOperator(Operator::EQUAL);
                $item = $tokenList->expectKeywordEnum(StatementInformationItem::class);
                $statementItems[] = new DiagnosticsItem($target, $item);
            } while ($tokenList->hasSymbol(','));
        }

        return new GetDiagnosticsCommand($area, $statementItems, $condition, $conditionItems);
    }

    private function parseTarget(TokenList $tokenList): Identifier
    {
        $token = $tokenList->get(TokenType::AT_VARIABLE);
        if ($token !== null) {
            $variable = $this->expressionParser->parseAtVariable($tokenList, $token->value);
            if (!$variable instanceof UserVariable) {
                throw new ParserException('User variable or local variable expected.', $tokenList);
            }

            return $variable;
        } else {
            $name = $tokenList->expectName(EntityType::LOCAL_VARIABLE);
            if ($tokenList->inRoutine() !== null) {
                // local variable
                return new SimpleName($name);
            } else {
                throw new ParserException('User variable or local variable expected.', $tokenList);
            }
        }
    }

    /**
     * SIGNAL [condition_value]
     *     [SET signal_information_item
     *     [, signal_information_item] ...]
     *
     * RESIGNAL [condition_value]
     *     [SET signal_information_item
     *     [, signal_information_item] ...]
     *
     * condition_value:
     *     SQLSTATE [VALUE] sqlstate_value
     *   | condition_name
     *
     * signal_information_item:
     *     condition_information_item_name = simple_value_specification
     *
     * condition_information_item_name:
     *     CLASS_ORIGIN
     *   | SUBCLASS_ORIGIN
     *   | MESSAGE_TEXT
     *   | MYSQL_ERRNO
     *   | CONSTRAINT_CATALOG
     *   | CONSTRAINT_SCHEMA
     *   | CONSTRAINT_NAME
     *   | CATALOG_NAME
     *   | SCHEMA_NAME
     *   | TABLE_NAME
     *   | COLUMN_NAME
     *   | CURSOR_NAME
     *
     * condition_name, simple_value_specification:
     *     (see following discussion)
     *
     * Valid simple_value_specification designators can be specified using:
     *  - stored procedure or function parameters,
     *  - stored program local variables declared with DECLARE,
     *  - user-defined variables,
     *  - system variables, or
     *  - literals. A character literal may include a _charset introducer.
     *
     * @return SignalCommand|ResignalCommand
     */
    public function parseSignalResignal(TokenList $tokenList)
    {
        $which = $tokenList->expectAnyKeyword(Keyword::SIGNAL, Keyword::RESIGNAL);

        if ($tokenList->hasKeyword(Keyword::SQLSTATE)) {
            $tokenList->passKeyword(Keyword::VALUE);
            $condition = $tokenList->expectSqlState();
            if ($condition->getCategory()->equalsValue(SqlStateCategory::SUCCESS)) {
                throw new ParserException('Only non-success SQL states are allowed.', $tokenList);
            }
        } else {
            $condition = $tokenList->getNonReservedName(EntityType::CONDITION);
        }
        $items = [];
        if ($tokenList->hasKeyword(Keyword::SET)) {
            do {
                /** @var ConditionInformationItem::* $item */
                $item = $tokenList->expectKeywordEnum(ConditionInformationItem::class)->getValue();
                if (isset($items[$item])) {
                    throw new ParserException("Duplicit condition $item.", $tokenList);
                } elseif ($item === ConditionInformationItem::RETURNED_SQLSTATE) {
                    throw new ParserException("Cannot set condition $item.", $tokenList);
                }
                $tokenList->expectOperator(Operator::EQUAL);
                $value = $this->expressionParser->parseExpression($tokenList);
                if ($item === ConditionInformationItem::MYSQL_ERRNO && $value instanceof IntLiteral && !$value instanceof UintLiteral) {
                    throw new ParserException('Unsigned int expected.', $tokenList);
                }
                $items[$item] = $value;
            } while ($tokenList->hasSymbol(','));
        }

        if ($which === Keyword::SIGNAL) {
            if ($condition === null && $items === []) {
                throw new ParserException("Empty SIGNAL/RESIGNAL statement.", $tokenList);
            }

            return new SignalCommand($condition, $items);
        } else {
            return new ResignalCommand($condition, $items);
        }
    }

}
