<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable SlevomatCodingStandard.ControlStructures.AssignmentInCondition

namespace SqlFtw\Parser\Dal;

use SqlFtw\Parser\ExpressionParser;
use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Parser\TokenType;
use SqlFtw\Sql\Dal\Set\ResetPersistCommand;
use SqlFtw\Sql\Dal\Set\SetAssignment;
use SqlFtw\Sql\Dal\Set\SetCharacterSetCommand;
use SqlFtw\Sql\Dal\Set\SetNamesCommand;
use SqlFtw\Sql\Dal\Set\SetVariablesCommand;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\DefaultLiteral;
use SqlFtw\Sql\Expression\EnumValueLiteral;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\QualifiedName;
use SqlFtw\Sql\Expression\Scope;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\Expression\SystemVariable;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\MysqlVariable;

class SetCommandsParser
{

    private ExpressionParser $expressionParser;

    public function __construct(ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }

    /**
     * SET variable_assignment [, variable_assignment] ...
     *
     * variable_assignment:
     *     user_var_name = expr
     *   | param_name = expr
     *   | local_var_name = expr
     *   | {GLOBAL | @@GLOBAL.} system_var_name = expr
     *   | {PERSIST | @@PERSIST.} system_var_name = expr
     *   | {PERSIST_ONLY | @@PERSIST_ONLY.} system_var_name = expr
     *   | [SESSION | @@SESSION. | @@] system_var_name = expr
     *   | [LOCAL | @@LOCAL | @@] system_var_name = expr -- alias for SESSION
     */
    public function parseSet(TokenList $tokenList): SetVariablesCommand
    {
        $tokenList->expectKeyword(Keyword::SET);

        $assignments = $this->parseAssignments($tokenList);

        return new SetVariablesCommand($assignments);
    }

    /**
     * SET {CHARACTER SET | CHARSET}
     *     {'charset_name' | DEFAULT}
     */
    public function parseSetCharacterSet(TokenList $tokenList): SetCharacterSetCommand
    {
        $tokenList->expectKeyword(Keyword::SET);
        $keyword = $tokenList->expectAnyKeyword(Keyword::CHARACTER, Keyword::CHARSET);
        if ($keyword === Keyword::CHARACTER) {
            $tokenList->expectKeyword(Keyword::SET);
        }

        if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
            $charset = null;
        } else {
            $charset = $tokenList->expectCharsetName();
        }

        $assignments = [];
        if ($tokenList->hasSymbol(',')) {
            $assignments = $this->parseAssignments($tokenList);
        }

        return new SetCharacterSetCommand($charset, $assignments);
    }

    /**
     * SET NAMES {'charset_name' [COLLATE 'collation_name'] | DEFAULT}
     *     [, variable_name [=] value]
     */
    public function parseSetNames(TokenList $tokenList): SetNamesCommand
    {
        $tokenList->expectKeywords(Keyword::SET, Keyword::NAMES);

        $collation = null;
        if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
            $charset = new DefaultLiteral();
        } else {
            $charset = $tokenList->expectCharsetName();

            if ($tokenList->hasKeyword(Keyword::COLLATE)) {
                $collation = $tokenList->expectCollationName();
            }
        }

        $assignments = [];
        if ($tokenList->hasSymbol(',')) {
            $assignments = $this->parseAssignments($tokenList);
        }

        return new SetNamesCommand($charset, $collation, $assignments);
    }

    /**
     * RESET PERSIST [[IF EXISTS] system_var_name]
     */
    public function parseResetPersist(TokenList $tokenList): ResetPersistCommand
    {
        $tokenList->expectKeywords(Keyword::RESET, Keyword::PERSIST);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);
        if ($ifExists) {
            $variable = $tokenList->expectName(null);
        } else {
            $variable = $tokenList->getName(null);
        }
        if ($variable !== null) {
            $variable = new MysqlVariable($variable);
        }

        return new ResetPersistCommand($variable, $ifExists);
    }

    /**
     * @return non-empty-list<SetAssignment>
     */
    private function parseAssignments(TokenList $tokenList): array
    {
        $session = $tokenList->getSession();
        $assignments = [];
        // scope used by last assignment stays valid for other assignments, until changed or overruled by "@@foo." syntax
        $lastKeywordScope = null;
        $current = false;
        do {
            if ($tokenList->hasKeyword(Keyword::LOCAL)) {
                $lastKeywordScope = new Scope(Scope::SESSION);
                $current = true;
            } else {
                $s = $tokenList->getKeywordEnum(Scope::class);
                if ($s !== null) {
                    $lastKeywordScope = $s;
                    $current = true;
                }
            }

            if ($lastKeywordScope !== null && $current === true) {
                // GLOBAL foo
                $name = $tokenList->expectNonReservedNameOrString();
                if ($tokenList->hasSymbol('.')) {
                    $name .= '.' . $tokenList->expectName(null);
                }
                $variable = $this->expressionParser->createSystemVariable($tokenList, $name, $lastKeywordScope, true);
            } elseif (($token = $tokenList->get(TokenType::AT_VARIABLE)) !== null) {
                // @foo, @@foo...
                $variable = $this->expressionParser->parseAtVariable($tokenList, $token->value, true);
            } else {
                $name = $tokenList->expectName(null);
                if ($tokenList->hasSymbol('.')) {
                    $name2 = $tokenList->expectName(EntityType::COLUMN);
                    $fullName = $name . '.' . $name2;
                    if (MysqlVariable::validateValue($fullName)) {
                        // plugin system variable without explicit scope
                        $scope = $lastKeywordScope ?? new Scope(Scope::SESSION);
                        $variable = $this->expressionParser->createSystemVariable($tokenList, $fullName, $scope, true);
                    } else {
                        // NEW.foo etc.
                        $variable = new QualifiedName($name2, $name);
                    }
                } elseif (!$session->isLocalVariable($name) && MysqlVariable::validateValue($name)) {
                    // system variable without explicit scope
                    $scope = $lastKeywordScope ?? new Scope(Scope::SESSION);
                    $variable = $this->expressionParser->createSystemVariable($tokenList, $name, $scope, true);
                } elseif ($tokenList->inRoutine() !== null) {
                    // local variable
                    $variable = new SimpleName($name);
                } else {
                    // throws
                    $this->expressionParser->createSystemVariable($tokenList, $name, new Scope(Scope::SESSION), true);
                    exit;
                }
            }

            $operator = $tokenList->expectAnyOperator(Operator::EQUAL, Operator::ASSIGN);

            if ($variable instanceof SystemVariable) {
                $variableName = $variable->getName();
                $type = MysqlVariable::getType($variableName);
                if ($type === BaseType::ENUM || $type === BaseType::SET) {
                    $value = $tokenList->getVariableEnumValue(...MysqlVariable::getValues($variableName));
                    if ($value !== null) {
                        $expression = new EnumValueLiteral((string) $value);
                    } else {
                        $expression = $this->expressionParser->parseAssignExpression($tokenList);
                    }
                } else {
                    $expression = $this->expressionParser->parseAssignExpression($tokenList);
                }
            } else {
                $expression = $this->expressionParser->parseAssignExpression($tokenList);
                if (($variable instanceof SimpleName || $variable instanceof QualifiedName) && $expression instanceof DefaultLiteral) {
                    throw new ParserException('Local variables cannot be set to DEFAULT.', $tokenList);
                }
            }

            $assignments[] = new SetAssignment($variable, $expression, $operator);
            $current = false;
        } while ($tokenList->hasSymbol(','));

        return $assignments;
    }

}
