<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable PSR2.Methods.FunctionCallSignature.MultipleArguments

namespace SqlFtw\Parser;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Parser\Dml\QueryParser;
use SqlFtw\Session\SessionUpdater;
use SqlFtw\Sql\Command;
use SqlFtw\Sql\Dal\Flush\FlushCommand;
use SqlFtw\Sql\Dal\Flush\FlushTablesCommand;
use SqlFtw\Sql\Dal\Replication\ReplicationCommand;
use SqlFtw\Sql\Dal\Replication\ResetMasterCommand;
use SqlFtw\Sql\Dal\Replication\ResetSlaveCommand;
use SqlFtw\Sql\Dal\Show\ShowErrorsCommand;
use SqlFtw\Sql\Dal\Show\ShowWarningsCommand;
use SqlFtw\Sql\Ddl\Event\AlterEventCommand;
use SqlFtw\Sql\Ddl\Event\CreateEventCommand;
use SqlFtw\Sql\Ddl\Event\EventCommand;
use SqlFtw\Sql\Ddl\Instance\AlterInstanceCommand;
use SqlFtw\Sql\Ddl\LogfileGroup\LogfileGroupCommand;
use SqlFtw\Sql\Ddl\Schema\SchemaCommand;
use SqlFtw\Sql\Ddl\Server\ServerCommand;
use SqlFtw\Sql\Ddl\Tablespace\TablespaceCommand;
use SqlFtw\Sql\Ddl\Trigger\DropTriggerCommand;
use SqlFtw\Sql\Ddl\View\AlterViewCommand;
use SqlFtw\Sql\Dml\Error\GetDiagnosticsCommand;
use SqlFtw\Sql\Dml\Error\ResignalCommand;
use SqlFtw\Sql\Dml\Error\SignalCommand;
use SqlFtw\Sql\Dml\Error\SqlStateCategory;
use SqlFtw\Sql\Dml\Load\LoadDataCommand;
use SqlFtw\Sql\Dml\Load\LoadXmlCommand;
use SqlFtw\Sql\Dml\Prepared\PreparedStatementCommand;
use SqlFtw\Sql\Dml\Query\Query;
use SqlFtw\Sql\Dml\Transaction\LockTablesCommand;
use SqlFtw\Sql\Dml\Transaction\TransactionCommand;
use SqlFtw\Sql\Dml\Transaction\UnlockTablesCommand;
use SqlFtw\Sql\Dml\Utility\ExplainForConnectionCommand;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\Routine\CaseStatement;
use SqlFtw\Sql\Routine\CloseCursorStatement;
use SqlFtw\Sql\Routine\CompoundStatement;
use SqlFtw\Sql\Routine\Condition;
use SqlFtw\Sql\Routine\ConditionType;
use SqlFtw\Sql\Routine\DeclareConditionStatement;
use SqlFtw\Sql\Routine\DeclareCursorStatement;
use SqlFtw\Sql\Routine\DeclareHandlerStatement;
use SqlFtw\Sql\Routine\DeclareVariablesStatement;
use SqlFtw\Sql\Routine\FetchStatement;
use SqlFtw\Sql\Routine\HandlerAction;
use SqlFtw\Sql\Routine\IfStatement;
use SqlFtw\Sql\Routine\IterateStatement;
use SqlFtw\Sql\Routine\LeaveStatement;
use SqlFtw\Sql\Routine\LoopStatement;
use SqlFtw\Sql\Routine\OpenCursorStatement;
use SqlFtw\Sql\Routine\RepeatStatement;
use SqlFtw\Sql\Routine\ReturnStatement;
use SqlFtw\Sql\Routine\RoutineType;
use SqlFtw\Sql\Routine\WhileStatement;
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\SubqueryType;
use function array_values;
use function get_class;
use function in_array;

class RoutineBodyParser
{

    private Parser $parser;

    private ExpressionParser $expressionParser;

    private QueryParser $queryParser;

    private SessionUpdater $sessionUpdater;

    public function __construct(
        Parser $parser,
        ExpressionParser $expressionParser,
        QueryParser $queryParser,
        SessionUpdater $sessionUpdater
    ) {
        $this->parser = $parser;
        $this->expressionParser = $expressionParser;
        $this->queryParser = $queryParser;
        $this->sessionUpdater = $sessionUpdater;
    }

    /**
     * routine_body:
     *     RETURN ...
     *   | statement
     *   | compound_statement
     *
     * compound_statement:
     *   [begin_label:] BEGIN
     *     [statement_list]
     *   END [end_label]
     *
     * @param string&RoutineType::* $routine
     */
    public function parseBody(TokenList $tokenList, string $routine): Statement
    {
        if ($routine === RoutineType::FUNCTION && $tokenList->hasKeyword(Keyword::RETURN)) {
            return new ReturnStatement($this->expressionParser->parseExpression($tokenList));
        }

        $position = $tokenList->getPosition();
        $label = $tokenList->getNonKeywordName(EntityType::LABEL);
        if ($label !== null) {
            $tokenList->expectSymbol(':');
        }

        $previous = $tokenList->inEmbedded();
        $tokenList->startEmbedded();
        $tokenList->startRoutine($routine);

        if ($tokenList->hasAnyKeyword(Keyword::BEGIN, Keyword::LOOP, Keyword::REPEAT, Keyword::WHILE, Keyword::CASE, Keyword::IF)) {
            $statement = $this->parseStatement($tokenList->rewind($position));
        } else {
            $statement = $this->parseCommand($tokenList->rewind($position), true);
        }

        $previous ? $tokenList->startEmbedded() : $tokenList->endEmbedded();
        $tokenList->endRoutine();

        if ($statement instanceof EmptyCommand) {
            throw new ParserException('Routine body can not be empty.', $tokenList);
        }

        return $statement;
    }

    /**
     * RETURN expr
     *
     * LEAVE label
     *
     * ITERATE label
     *
     * OPEN cursor_name
     *
     * CLOSE cursor_name
     *
     * ...
     */
    private function parseStatement(TokenList $tokenList): Statement
    {
        $position = $tokenList->getPosition();
        $label = $tokenList->getNonReservedName(EntityType::LABEL);
        if (!$tokenList->hasSymbol(':')) {
            $label = null;
            $tokenList->rewind($position);
        }

        $in = $tokenList->inRoutine();

        if ($label !== null) {
            $keyword = $tokenList->expectAnyKeyword(Keyword::BEGIN, Keyword::LOOP, Keyword::REPEAT, Keyword::WHILE);
        } else {
            $keywords = [
                Keyword::BEGIN, Keyword::LOOP, Keyword::REPEAT, Keyword::WHILE, Keyword::CASE, Keyword::IF,
                Keyword::DECLARE, Keyword::OPEN, Keyword::FETCH, Keyword::CLOSE, Keyword::LEAVE, Keyword::ITERATE,
            ];
            if ($in === RoutineType::FUNCTION) {
                $keywords[] = Keyword::RETURN;
            }
            $keyword = $tokenList->getAnyKeyword(...$keywords);
        }
        switch ($keyword) {
            case Keyword::LOOP:
                $statement = $this->parseLoop($tokenList, $label);
                break;
            case Keyword::REPEAT:
                $statement = $this->parseRepeat($tokenList, $label);
                break;
            case Keyword::WHILE:
                $statement = $this->parseWhile($tokenList, $label);
                break;
            case Keyword::CASE:
                $statement = $this->parseCase($tokenList);
                break;
            case Keyword::IF:
                $statement = $this->parseIf($tokenList);
                break;
            case Keyword::DECLARE:
                $statement = $this->parseDeclare($tokenList);
                break;
            case Keyword::OPEN:
                $statement = new OpenCursorStatement($tokenList->expectName(null));
                break;
            case Keyword::FETCH:
                $statement = $this->parseFetch($tokenList);
                break;
            case Keyword::CLOSE:
                $statement = new CloseCursorStatement($tokenList->expectName(null));
                break;
            case Keyword::RETURN:
                $statement = new ReturnStatement($this->expressionParser->parseExpression($tokenList));
                break;
            case Keyword::LEAVE:
                $statement = new LeaveStatement($tokenList->expectName(EntityType::LABEL));
                break;
            case Keyword::ITERATE:
                $statement = new IterateStatement($tokenList->expectName(EntityType::LABEL));
                break;
            case Keyword::BEGIN:
                $statement = $this->parseCompoundStatement($tokenList, $label);
                break;
            default:
                $previous = $tokenList->inEmbedded();
                // do not check delimiter in Parser, because it will be checked here
                $tokenList->startEmbedded();
                $statement = $this->parseCommand($tokenList, $previous);
                $previous ? $tokenList->startEmbedded() : $tokenList->endEmbedded();

                break;
        }

        if (!$statement instanceof Command) {
            $this->sessionUpdater->processStatement($statement);
        }

        // ensures that the statement was parsed completely
        if (!$tokenList->inEmbedded() && !$tokenList->isFinished()) {
            if ($statement instanceof CompoundStatement
                || ($statement instanceof DeclareHandlerStatement && $statement->getStatement() instanceof CompoundStatement)
            ) {
                // ; not mandatory after `end`
                $delimiter = $tokenList->get(TokenType::DELIMITER);
                if ($delimiter !== null) {
                    $tokenList->appendTrailingDelimiter($delimiter->value);
                } else {
                    if ($tokenList->hasSymbol(';')) {
                        $tokenList->appendTrailingDelimiter(';');
                    }
                }
            } else {
                $delimiter = $tokenList->get(TokenType::DELIMITER);
                if ($delimiter !== null) {
                    $tokenList->appendTrailingDelimiter($delimiter->value);
                } else {
                    $tokenList->expectSymbol(';');
                    $tokenList->appendTrailingDelimiter(';');
                }
            }
        }

        return $statement;
    }

    /**
     * @return Command&Statement
     */
    private function parseCommand(TokenList $tokenList, bool $topLevel): Command
    {
        $in = $tokenList->inRoutine();
        $statement = $this->parser->parseTokenList($tokenList);

        if ($statement instanceof InvalidCommand) {
            throw $statement->getException();
        } elseif ($statement instanceof ExplainForConnectionCommand) {
            throw new ParserException('Cannot use EXPLAIN FOR CONNECTION inside a routine.', $tokenList);
        } elseif ($statement instanceof LockTablesCommand || $statement instanceof UnlockTablesCommand) {
            throw new ParserException('Cannot use LOCK TABLES or UNLOCK TABLES inside a routine.', $tokenList);
        } elseif ($statement instanceof AlterEventCommand && $topLevel && $in === RoutineType::EVENT) {
            throw new ParserException('Cannot use ALTER EVENT inside ALTER EVENT directly. Use BEGIN/END block.', $tokenList);
        } elseif ($statement instanceof CreateEventCommand) {
            throw new ParserException('Cannot use CREATE EVENT inside an procedure.', $tokenList);
        } elseif ($statement instanceof AlterViewCommand) {
            throw new ParserException('Cannot use ALTER VIEW inside a routine.', $tokenList);
        } elseif ($statement instanceof LoadDataCommand || $statement instanceof LoadXmlCommand) {
            throw new ParserException('Cannot use LOAD DATA or LOAD XML inside a routine.', $tokenList);
        } elseif ($in !== RoutineType::PROCEDURE && $in !== RoutineType::EVENT && $statement instanceof PreparedStatementCommand) {
            throw new ParserException('Cannot use prepared statements inside a function or trigger.', $tokenList);
        } elseif ($in !== RoutineType::PROCEDURE && ($statement instanceof ResetMasterCommand || $statement instanceof ResetSlaveCommand)) {
            throw new ParserException('Cannot use RESET MASTER/SLAVE inside a function, trigger or event.', $tokenList);
        } elseif ($in !== RoutineType::PROCEDURE && ($statement instanceof FlushCommand || $statement instanceof FlushTablesCommand)) {
            throw new ParserException('Cannot use FLUSH inside a function, trigger or event.', $tokenList);
        } elseif ($statement instanceof Query
            || $statement instanceof SignalCommand || $statement instanceof ResignalCommand
            || $statement instanceof GetDiagnosticsCommand || $statement instanceof ShowWarningsCommand
            || $statement instanceof ShowErrorsCommand || $statement instanceof PreparedStatementCommand
            || $statement instanceof TransactionCommand || $statement instanceof EventCommand
            || $statement instanceof DropTriggerCommand || $statement instanceof SchemaCommand
            || $statement instanceof TablespaceCommand || $statement instanceof ServerCommand
            || $statement instanceof ReplicationCommand || $statement instanceof AlterInstanceCommand
            || $statement instanceof LogfileGroupCommand
        ) {
            // ok
        } else {
            $class = get_class($statement);
            if (!in_array($class, $tokenList->getSession()->getPlatform()->getPreparableCommands(), true)) {
                throw new ParserException('Non-preparable statement in routine body: ' . $class, $tokenList);
            }
        }

        return $statement;
    }

    private function parseCompoundStatement(TokenList $tokenList, ?string $label): CompoundStatement
    {
        $statements = $this->parseStatementList($tokenList);
        $tokenList->expectKeyword(Keyword::END);

        $endLabel = null;
        if ($label !== null) {
            $endLabel = $tokenList->getName(EntityType::LABEL);
            if ($endLabel !== null && $endLabel !== $label) {
                $tokenList->missing($label);
            }
        }

        return new CompoundStatement($statements, $label, $endLabel !== null);
    }

    /**
     * @return list<Statement>
     */
    private function parseStatementList(TokenList $tokenList): array
    {
        $statements = [];

        // for empty lists
        if ($tokenList->hasAnyKeyword(Keyword::END, Keyword::UNTIL, Keyword::WHEN, Keyword::ELSE, Keyword::ELSEIF)) {
            $tokenList->rewind(-1);
            return $statements;
        }

        $previous = $tokenList->inEmbedded();
        $tokenList->endEmbedded();
        do {
            $statements[] = $this->parseStatement($tokenList);

            if ($tokenList->isFinished()) {
                $nextTokenList = $this->parser->getNextTokenList();
                if ($nextTokenList !== null) {
                    $tokenList->append($nextTokenList);
                }
            }

            // termination condition
            if ($tokenList->hasAnyKeyword(Keyword::END, Keyword::UNTIL, Keyword::WHEN, Keyword::ELSE, Keyword::ELSEIF)) {
                $tokenList->rewind(-1);
                break;
            }
        } while (!$tokenList->isFinished());
        $previous ? $tokenList->startEmbedded() : $tokenList->endEmbedded();

        return $statements;
    }

    /**
     * [begin_label:] LOOP
     *     statement_list
     * END LOOP [end_label]
     */
    private function parseLoop(TokenList $tokenList, ?string $label): LoopStatement
    {
        $statements = $this->parseStatementList($tokenList);
        $tokenList->expectKeywords(Keyword::END, Keyword::LOOP);

        $endLabel = null;
        if ($label !== null) {
            $endLabel = $tokenList->getName(EntityType::LABEL);
            if ($endLabel !== null && $endLabel !== $label) {
                $tokenList->missing($label);
            }
        }

        return new LoopStatement($statements, $label, $endLabel !== null);
    }

    /**
     * [begin_label:] REPEAT
     *     statement_list
     *     UNTIL search_condition
     * END REPEAT [end_label]
     */
    private function parseRepeat(TokenList $tokenList, ?string $label): RepeatStatement
    {
        $statements = $this->parseStatementList($tokenList);
        $tokenList->expectKeyword(Keyword::UNTIL);
        $condition = $this->expressionParser->parseExpression($tokenList);
        $tokenList->expectKeywords(Keyword::END, Keyword::REPEAT);

        $endLabel = null;
        if ($label !== null) {
            $endLabel = $tokenList->getName(EntityType::LABEL);
            if ($endLabel !== null && $endLabel !== $label) {
                $tokenList->missing($label);
            }
        }

        return new RepeatStatement($statements, $condition, $label, $endLabel !== null);
    }

    /**
     * [begin_label:] WHILE search_condition DO
     *     statement_list
     * END WHILE [end_label]
     */
    private function parseWhile(TokenList $tokenList, ?string $label): WhileStatement
    {
        $condition = $this->expressionParser->parseExpression($tokenList);
        $tokenList->expectKeyword(Keyword::DO);
        $statements = $this->parseStatementList($tokenList);
        $tokenList->expectKeywords(Keyword::END, Keyword::WHILE);

        $endLabel = null;
        if ($label !== null) {
            $endLabel = $tokenList->getName(EntityType::LABEL);
            if ($endLabel !== null && $endLabel !== $label) {
                $tokenList->missing($label);
            }
        }

        return new WhileStatement($statements, $condition, $label, $endLabel !== null);
    }

    /**
     * CASE case_value
     *     WHEN when_value THEN statement_list
     *     [WHEN when_value THEN statement_list] ...
     *     [ELSE statement_list]
     * END CASE
     *
     * CASE
     *     WHEN search_condition THEN statement_list
     *     [WHEN search_condition THEN statement_list] ...
     *     [ELSE statement_list]
     * END CASE
     */
    private function parseCase(TokenList $tokenList): CaseStatement
    {
        $condition = null;
        if (!$tokenList->hasKeyword(Keyword::WHEN)) {
            $condition = $this->expressionParser->parseExpression($tokenList);
            $tokenList->expectKeyword(Keyword::WHEN);
        }
        $formatter = new Formatter($tokenList->getSession());
        $values = [];
        /** @var non-empty-list<list<Statement>> $statementLists */
        $statementLists = [];
        do {
            $expression = $this->expressionParser->parseExpression($tokenList);
            $key = $expression->serialize($formatter);
            if (isset($values[$key])) {
                throw new ParserException('Duplicit CASE value.', $tokenList);
            }
            $values[$key] = $expression;
            $tokenList->expectKeyword(Keyword::THEN);
            $statementLists[] = $this->parseStatementList($tokenList);
        } while ($tokenList->hasKeyword(Keyword::WHEN));

        if ($tokenList->hasKeyword(Keyword::ELSE)) {
            $statementLists[] = $this->parseStatementList($tokenList);
        }
        $tokenList->expectKeywords(Keyword::END, Keyword::CASE);

        return new CaseStatement($condition, array_values($values), $statementLists);
    }

    /**
     * IF search_condition THEN statement_list
     *     [ELSEIF search_condition THEN statement_list] ...
     *     [ELSE statement_list]
     * END IF
     */
    private function parseIf(TokenList $tokenList): IfStatement
    {
        $conditions = [];
        /** @var non-empty-list<list<Statement>> $statementLists */
        $statementLists = [];
        $conditions[] = $this->expressionParser->parseExpression($tokenList);
        $tokenList->expectKeyword(Keyword::THEN);
        $statementLists[] = $this->parseStatementList($tokenList);

        while ($tokenList->hasKeyword(Keyword::ELSEIF)) {
            $conditions[] = $this->expressionParser->parseExpression($tokenList);
            $tokenList->expectKeyword(Keyword::THEN);
            $statementLists[] = $this->parseStatementList($tokenList);
        }
        if ($tokenList->hasKeyword(Keyword::ELSE)) {
            $statementLists[] = $this->parseStatementList($tokenList);
        }
        $tokenList->expectKeywords(Keyword::END, Keyword::IF);

        return new IfStatement($conditions, $statementLists);
    }

    /**
     * DECLARE var_name [, var_name] ... type [DEFAULT value]
     *
     *
     * DECLARE cursor_name CURSOR FOR select_statement
     *
     *
     * DECLARE condition_name CONDITION FOR condition_value
     *
     * condition_value:
     *     mysql_error_code
     *   | SQLSTATE [VALUE] sqlstate_value
     *
     *
     * DECLARE handler_action HANDLER
     *     FOR condition_value [, condition_value] ...
     *     statement
     *
     * handler_action:
     *     CONTINUE
     *   | EXIT
     *   | UNDO
     *
     * condition_value:
     *     mysql_error_code
     *   | SQLSTATE [VALUE] sqlstate_value
     *   | condition_name
     *   | SQLWARNING
     *   | NOT FOUND
     *   | SQLEXCEPTION
     *
     * @return DeclareVariablesStatement|DeclareCursorStatement|DeclareConditionStatement|DeclareHandlerStatement
     */
    private function parseDeclare(TokenList $tokenList)
    {
        $action = $tokenList->getKeywordEnum(HandlerAction::class);
        if ($action !== null && $action->equalsValue(HandlerAction::UNDO)) {
            throw new ParserException('UNDO handler is not supported.', $tokenList);
        }
        if ($action !== null) {
            $tokenList->expectKeywords(Keyword::HANDLER, Keyword::FOR);

            $conditions = [];
            do {
                $value = null;
                if ($tokenList->hasKeywords(Keyword::NOT, Keyword::FOUND)) {
                    $type = new ConditionType(ConditionType::NOT_FOUND);
                } elseif ($tokenList->hasKeyword(Keyword::SQLEXCEPTION)) {
                    $type = new ConditionType(ConditionType::SQL_EXCEPTION);
                } elseif ($tokenList->hasKeyword(Keyword::SQLWARNING)) {
                    $type = new ConditionType(ConditionType::SQL_WARNING);
                } elseif ($tokenList->hasKeyword(Keyword::SQLSTATE)) {
                    $type = new ConditionType(ConditionType::SQL_STATE);
                    $tokenList->passKeyword(Keyword::VALUE);
                    $value = $tokenList->expectSqlState();
                    if ($value->getCategory()->equalsValue(SqlStateCategory::SUCCESS)) {
                        throw new ParserException('Only non-success SQL states are allowed.', $tokenList);
                    }
                } else {
                    $value = $tokenList->getNonReservedName(null);
                    if ($value !== null) {
                        $type = new ConditionType(ConditionType::CONDITION);
                    } else {
                        $value = (int) $tokenList->expectUnsignedInt();
                        if ($value === 0) {
                            throw new ParserException('Condition value must be greater than 0.', $tokenList);
                        }
                        $type = new ConditionType(ConditionType::ERROR);
                    }
                }
                $conditions[] = new Condition($type, $value);
            } while ($tokenList->hasSymbol(','));

            $previous = $tokenList->inEmbedded();
            $tokenList->startEmbedded();
            $statement = $this->parseStatement($tokenList);
            $previous ? $tokenList->startEmbedded() : $tokenList->endEmbedded();

            return new DeclareHandlerStatement($action, $conditions, $statement);
        }

        $name = $tokenList->expectNonReservedName(null, null, TokenType::AT_VARIABLE);

        if ($tokenList->hasKeyword(Keyword::CURSOR)) {
            $tokenList->expectKeyword(Keyword::FOR);

            $tokenList->startSubquery(SubqueryType::CURSOR);
            $query = $this->queryParser->parseQuery($tokenList);
            $tokenList->endSubquery();

            return new DeclareCursorStatement($name, $query);
        } elseif ($tokenList->hasKeyword(Keyword::CONDITION)) {
            $tokenList->expectKeywords(Keyword::FOR);
            if ($tokenList->hasKeyword(Keyword::SQLSTATE)) {
                $tokenList->passKeyword(Keyword::VALUE);
                $value = $tokenList->expectSqlState();
                if ($value->getCategory()->equalsValue(SqlStateCategory::SUCCESS)) {
                    throw new ParserException('Only non-success SQL states are allowed.', $tokenList);
                }
            } else {
                $value = (int) $tokenList->expectUnsignedInt();
                if ($value === 0) {
                    throw new ParserException('Condition value must be greater than 0.', $tokenList);
                }
            }

            return new DeclareConditionStatement($name, $value);
        }

        /** @var non-empty-list<string> $names */
        $names = [$name];
        while ($tokenList->hasSymbol(',')) {
            $names[] = $tokenList->expectNonReservedName(null, null, TokenType::AT_VARIABLE);
        }
        $type = $this->expressionParser->parseColumnType($tokenList);
        $charset = $type->getCharset();
        $collation = $type->getCollation();
        if ($charset === null && $collation !== null) {
            throw new ParserException('Character set is required for variable with collation.', $tokenList);
        }
        $default = null;
        if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
            $default = $this->expressionParser->parseExpression($tokenList);
        }

        return new DeclareVariablesStatement($names, $type, $default);
    }

    /**
     * FETCH [[NEXT] FROM] cursor_name INTO var_name [, var_name] ...
     */
    private function parseFetch(TokenList $tokenList): FetchStatement
    {
        if ($tokenList->hasKeyword(Keyword::NEXT)) {
            $tokenList->expectKeyword(Keyword::FROM);
        } else {
            $tokenList->passKeyword(Keyword::FROM);
        }
        $cursor = $tokenList->expectName(null);
        $tokenList->expectKeyword(Keyword::INTO);
        $variables = [];
        do {
            $variables[] = $tokenList->expectName(null);
        } while ($tokenList->hasSymbol(','));

        return new FetchStatement($cursor, $variables);
    }

}
