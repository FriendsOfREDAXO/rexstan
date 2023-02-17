<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Session;

use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Resolver\ExpressionHelper;
use SqlFtw\Resolver\ExpressionResolver;
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Collation;
use SqlFtw\Sql\Command;
use SqlFtw\Sql\Dal\Set\SetCharacterSetCommand;
use SqlFtw\Sql\Dal\Set\SetCommand;
use SqlFtw\Sql\Dal\Set\SetNamesCommand;
use SqlFtw\Sql\Ddl\Event\CreateEventCommand;
use SqlFtw\Sql\Ddl\Routine\CreateRoutineCommand;
use SqlFtw\Sql\Dml\Query\SelectCommand;
use SqlFtw\Sql\Dml\Query\SelectIntoVariables;
use SqlFtw\Sql\Expression\DefaultLiteral;
use SqlFtw\Sql\Expression\NoneLiteral;
use SqlFtw\Sql\Expression\Scope;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\Expression\Subquery;
use SqlFtw\Sql\Expression\SystemVariable;
use SqlFtw\Sql\Expression\UnresolvedExpression;
use SqlFtw\Sql\Expression\UserVariable;
use SqlFtw\Sql\Expression\Value;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\MysqlVariable;
use SqlFtw\Sql\Routine\DeclareVariablesStatement;
use SqlFtw\Sql\SqlMode;
use SqlFtw\Sql\Statement;
use function count;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function trim;

/**
 * Analyzes commands which may affect parser behavior and updates global parser state
 */
class SessionUpdater
{

    private Session $session;

    private ExpressionResolver $resolver;

    public function __construct(Session $session, ExpressionResolver $resolver)
    {
        $this->session = $session;
        $this->resolver = $resolver;
    }

    public function processCommand(Command $command, TokenList $tokenList): void
    {
        if ($command instanceof SetCommand) {
            $this->processSet($command, $tokenList);
        } elseif ($command instanceof SelectCommand && $this->resolver->isSimpleSelect($command) && $command->getInto() instanceof SelectIntoVariables) {
            $this->processSelect($command, $tokenList);
        } elseif ($command instanceof CreateRoutineCommand || $command instanceof CreateEventCommand) {
            $this->session->resetLocalVariables();
        }
    }

    public function processStatement(Statement $statement): void
    {
        if ($statement instanceof DeclareVariablesStatement) {
            foreach ($statement->getVariables() as $name) {
                $this->session->setLocalVariable($name, null);
            }
        }
    }

    public function processSet(SetCommand $command, TokenList $tokenList): void
    {
        // SET innodb_strict_mode = ON
        // SET sql_require_primary_key = true
        // SET sql_safe_updates = ON
        // autocommit

        if ($command instanceof SetNamesCommand) {
            // todo: SET NAMES
        } elseif ($command instanceof SetCharacterSetCommand) {
            // todo: SET CHARSET
        }

        foreach ($command->getAssignments() as $assignment) {
            $expression = $assignment->getExpression();
            $variable = $assignment->getVariable();
            $name = $variable->getName();
            $value = $this->resolver->resolve($expression);

            // array to scalar
            if ($expression instanceof Subquery && is_array($value) && count($value) === 1) {
                $value = $value[0];
            }
            // special values to scalar
            if ($value instanceof SimpleName) {
                $value = $value->getName();
            } elseif ($value instanceof NoneLiteral) {
                $value = 'NONE';
            } elseif ($value instanceof DefaultLiteral) {
                $value = MysqlVariable::getDefault($name);
            } elseif (!ExpressionHelper::isValue($value)) {
                $value = new UnresolvedExpression($value);
            }
            /** @var UnresolvedExpression|Value|scalar|null $value */
            $value = $value;

            if ($variable instanceof SystemVariable) {
                $scope = $variable->getScope();
                switch ($name) {
                    case MysqlVariable::SQL_MODE:
                        $value = $this->processSqlMode($value, $tokenList);
                        break;
                    case MysqlVariable::CHARACTER_SET_CLIENT:
                    case MysqlVariable::CHARACTER_SET_FILESYSTEM:
                    case MysqlVariable::CHARACTER_SET_RESULTS:
                        $value = $this->processCharset($value, $scope, null);
                        break;
                    case MysqlVariable::CHARACTER_SET_CONNECTION:
                        $value = $this->processCharset($value, $scope, MysqlVariable::COLLATION_CONNECTION);
                        break;
                    case MysqlVariable::CHARACTER_SET_DATABASE:
                        $value = $this->processCharset($value, $scope, MysqlVariable::COLLATION_DATABASE);
                        break;
                    case MysqlVariable::CHARACTER_SET_SERVER:
                        $value = $this->processCharset($value, $scope, MysqlVariable::COLLATION_SERVER);
                        break;
                    case MysqlVariable::COLLATION_CONNECTION:
                        $value = $this->processCollation($value, $scope, MysqlVariable::CHARACTER_SET_CONNECTION);
                        break;
                    case MysqlVariable::COLLATION_DATABASE:
                        $value = $this->processCollation($value, $scope, MysqlVariable::CHARACTER_SET_DATABASE);
                        break;
                    case MysqlVariable::COLLATION_SERVER:
                        $value = $this->processCollation($value, $scope, MysqlVariable::CHARACTER_SET_SERVER);
                        break;
                    case MysqlVariable::DEFAULT_COLLATION_FOR_UTF8MB4:
                        $value = $this->processCollation($value, $scope, null);
                        break;
                }

                if ($scope === null || $scope->equalsValue(Scope::SESSION)) {
                    if (!$tokenList->inDefinition()) {
                        ///rl("write session.$name at row " . $tokenList->getLast()->row, null, 'r');
                        ///rd($value);
                        $this->session->setSessionVariable($name, $value);
                    }
                } elseif (!$scope->equalsValue(Scope::PERSIST_ONLY)) {
                    if (!$tokenList->inDefinition()) {
                        ///rl("write global.$name at row " . $tokenList->getLast()->row, null, 'r');
                        ///rd($value);
                        $this->session->setGlobalVariable($name, $value);
                    }
                }
            } elseif ($variable instanceof UserVariable) {
                if (!$tokenList->inDefinition()) {
                    ///rl("write $name at row " . $tokenList->getLast()->row, null, 'r');
                    ///rd($value);
                    $this->session->setUserVariable($name, $value);
                }
            }
        }
    }

    private function processSelect(SelectCommand $command, TokenList $tokenList): void
    {
        /** @var SelectIntoVariables $into */
        $into = $command->getInto();

        $values = $this->resolver->processSelect($command, true);
        if ($values instanceof SelectCommand) {
            return;
        }

        $variables = $into->getVariables();
        if (count($variables) !== count($values)) {
            throw new ParserException('Count of values does not match count of variables.', $tokenList);
        }

        foreach ($variables as $i => $variable) {
            if ($variable instanceof UserVariable) {
                $this->session->setUserVariable($variable->getName(), $values[$i]);
            }
        }
    }

    /**
     * @param UnresolvedExpression|Value|scalar|null $value
     */
    private function processSqlMode($value, TokenList $tokenList): string
    {
        if ($value instanceof Value) {
            $value = $this->resolver->cast()->toIntOrString($value);
        }
        if (is_bool($value) || is_float($value)) {
            $value = (int) $value;
        }

        try {
            if (is_int($value) || (is_string($value) && $value === (string) (int) $value)) {
                $mode = SqlMode::getFromInt((int) $value);
            } elseif (is_string($value)) {
                $mode = SqlMode::getFromString(trim($value));
            } else {
                throw new ParserException('Cannot detect SQL_MODE change. Cannot continue parsing, as the syntax may have been changed.', $tokenList);
            }
        } catch (InvalidDefinitionException $e) {
            throw new ParserException($e->getMessage(), $tokenList, $e);
        }

        if (!$tokenList->inDefinition()) {
            $this->session->setMode($mode);
        }

        return $mode->getValue();
    }

    /**
     * @param UnresolvedExpression|Value|scalar|null $value
     * @return UnresolvedExpression|scalar|null
     */
    public function processCharset($value, ?Scope $scope, ?string $collationVariable)
    {
        if ($value instanceof UnresolvedExpression) {
            return $value;
        }
        if ($value instanceof Value) {
            $value = $this->resolver->cast()->toIntOrString($value);
        }
        if ($value === null) {
            return null;
        }
        if (is_bool($value) || is_float($value)) {
            $value = (int) $value;
        }
        try {
            if (is_int($value)) {
                $charset = Charset::getById($value);
            } else {
                $charset = new Charset($value);
            }

            if ($collationVariable !== null) {
                // todo: theoretically should ask for DEFAULT_COLLATION_FOR_UTF8MB4, but it is not important for parser
                if ($scope === null || $scope->equalsValue(Scope::SESSION)) {
                    $this->session->setSessionVariable($collationVariable, $charset->getDefaultCollationName());
                } elseif (!$scope->equalsValue(Scope::PERSIST_ONLY)) {
                    $this->session->setGlobalVariable($collationVariable, $charset->getDefaultCollationName());
                }
            }

            return $charset->getValue();
        } catch (InvalidDefinitionException $e) {
            return $value;
        }
    }

    /**
     * @param UnresolvedExpression|Value|scalar|null $value
     * @return UnresolvedExpression|scalar|null
     */
    public function processCollation($value, ?Scope $scope, ?string $charsetVariable)
    {
        if ($value instanceof UnresolvedExpression) {
            return $value;
        }
        if ($value instanceof Value) {
            $value = $this->resolver->cast()->toIntOrString($value);
        }
        if ($value === null) {
            return null;
        }
        if (is_bool($value) || is_float($value)) {
            $value = (int) $value;
        }
        try {
            if (is_int($value)) {
                $collation = Collation::getById($value);
            } else {
                $collation = new Collation($value);
            }

            if ($charsetVariable !== null) {
                if ($scope === null || $scope->equalsValue(Scope::SESSION)) {
                    $this->session->setSessionVariable($charsetVariable, $collation->getCharsetName());
                } elseif (!$scope->equalsValue(Scope::PERSIST_ONLY)) {
                    $this->session->setGlobalVariable($charsetVariable, $collation->getCharsetName());
                }
            }

            return $collation->getValue();
        } catch (InvalidDefinitionException $e) {
            return $value;
        }
    }

}
