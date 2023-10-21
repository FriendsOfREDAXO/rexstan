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

use Generator;
use SqlFtw\Analyzer\Rules\Charset\CharsetAndCollationCompatibilityRule;
use SqlFtw\Analyzer\Rules\Variables\SystemVariablesTypeRule;
use SqlFtw\Analyzer\SimpleAnalyzer;
use SqlFtw\Analyzer\SimpleContext;
use SqlFtw\Resolver\ExpressionResolver;
use SqlFtw\Session\Session;
use SqlFtw\Session\SessionUpdater;
use SqlFtw\Sql\Command;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\Statement;
use Throwable;
use function count;
use function strtoupper;

/**
 * SQL parser - takes an SQL string and generates `Command` objects
 */
class Parser
{

    private const STARTING_KEYWORDS = [
        Keyword::ALTER, Keyword::ANALYZE, Keyword::BEGIN, Keyword::BINLOG, Keyword::CACHE, Keyword::CALL, Keyword::CHANGE,
        Keyword::CHECK, Keyword::CHECKSUM, Keyword::COMMIT, Keyword::CREATE, Keyword::DEALLOCATE, Keyword::DELETE,
        Keyword::DELIMITER, Keyword::DESC, Keyword::DESCRIBE, Keyword::DO, Keyword::DROP, Keyword::EXECUTE, Keyword::EXPLAIN,
        Keyword::FLUSH, Keyword::GRANT, Keyword::HANDLER, Keyword::HELP, Keyword::INSERT, Keyword::INSTALL, Keyword::KILL,
        Keyword::LOCK, Keyword::LOAD, Keyword::OPTIMIZE, Keyword::PREPARE, Keyword::PURGE, Keyword::RELEASE, Keyword::RENAME,
        Keyword::REPAIR, Keyword::RELEASE, Keyword::RESET, Keyword::RESIGNAL, Keyword::RESTART, Keyword::REVOKE,
        Keyword::ROLLBACK, Keyword::SAVEPOINT, Keyword::SELECT, Keyword::SET, Keyword::SHOW, Keyword::SHUTDOWN,
        Keyword::SIGNAL, Keyword::START, Keyword::STOP, Keyword::TRUNCATE, Keyword::UNINSTALL, Keyword::UNLOCK,
        Keyword::UPDATE, Keyword::USE, Keyword::WITH, Keyword::XA,
    ];

    private Session $session;

    private SessionUpdater $sessionUpdater;

    private SimpleAnalyzer $analyzer;

    private Lexer $lexer;

    private ParserFactory $factory;

    private Generator $tokenListGenerator; // @phpstan-ignore-line "uninitialized property" may only fail in @internal getNextTokenList()

    public function __construct(Session $session, ?Lexer $lexer = null)
    {
        $resolver = new ExpressionResolver($session);

        $this->session = $session;
        $this->sessionUpdater = new SessionUpdater($session, $resolver);
        $this->lexer = $lexer ?? new Lexer($session);
        $this->factory = new ParserFactory($this, $session, $this->sessionUpdater);

        $context = new SimpleContext($session, $resolver);
        // always executed rules (errors not as obvious as syntax error, but preventing command execution anyway)
        $this->analyzer = new SimpleAnalyzer($context, [
            new SystemVariablesTypeRule(),
            //new CharsetAndCollationCompatibilityRule(),
        ]);
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getParserFactory(): ParserFactory
    {
        return $this->factory;
    }

    /**
     * @internal to be used by RoutineBodyParser only
     */
    public function getNextTokenList(): ?TokenList
    {
        $this->tokenListGenerator->next();
        if (!$this->tokenListGenerator->valid()) {
            return null;
        }

        /** @var TokenList $tokenList */
        $tokenList = $this->tokenListGenerator->current();

        return $tokenList;
    }

    /**
     * @return Generator<int, array{Command&Statement, TokenList}>
     */
    public function parse(string $sql, bool $prepared = false): Generator
    {
        $this->tokenListGenerator = $this->lexer->tokenizeLists($sql);
        $first = true;

        // next() cannot be called after current(), because lexing depends on result of parsing current()
        while (($first || $this->tokenListGenerator->next() === null) && $this->tokenListGenerator->valid()) { // @phpstan-ignore-line comparing void and null
            $first = false;
            /** @var TokenList $tokenList */
            $tokenList = $this->tokenListGenerator->current();
            if ($prepared) {
                $tokenList->startPrepared();
            }
            $start = $tokenList->getPosition();
            $command = $this->parseTokenList($tokenList);
            $end = $tokenList->getPosition() - 1;
            if ($end <= $start) {
                $end = $start + 1;
            }

            if ($command instanceof EmptyCommand && $command->getCommentsBefore() === []) {
                continue;
            }

            $results = $this->analyzer->process($command);
            if (count($results) > 0) {
                $exception = new AnalyzerException($results, $command, $tokenList);
                $command = new InvalidCommand($command->getCommentsBefore(), $exception, $command);

                yield [$command, $tokenList->slice($start, $end)];

                continue;
            }

            if ($tokenList->inRoutine() === null && !$tokenList->inPrepared()) {
                try {
                    $this->sessionUpdater->processCommand($command, $tokenList);
                } catch (ParsingException $e) {
                    $command = new InvalidCommand($command->getCommentsBefore(), $e, $command);

                    yield [$command, $tokenList->slice($start, $end)];

                    continue;
                }
            }

            yield [$command, $tokenList->slice($start, $end)];
        }
    }

    /**
     * @return Command&Statement
     * @internal
     */
    public function parseTokenList(TokenList $tokenList): Command
    {
        // collecting comments and checking first applicable token
        $autoSkip = $tokenList->getAutoSkip();
        $tokenList->setAutoSkip($autoSkip & TokenType::WHITESPACE);
        $comments = [];
        $first = $tokenList->get();
        do {
            if ($first === null) {
                return new EmptyCommand($comments);
            } elseif (($first->type & TokenType::DELIMITER) !== 0) {
                return new EmptyCommand($comments);
            } elseif (($first->type & TokenType::COMMENT) !== 0) {
                $comments[] = $first->value;
            } elseif (($first->type & TokenType::KEYWORD) !== 0) {
                break;
            } elseif (($first->type & TokenType::SYMBOL) !== 0) {
                if ($first->value === '(') {
                    break;
                }
                $exception = InvalidTokenException::tokens(TokenType::KEYWORD, 0, self::STARTING_KEYWORDS, $first, $tokenList);
                $tokenList->finish();

                return new InvalidCommand($comments, $exception);
            } else {
                $exception = InvalidTokenException::tokens(TokenType::KEYWORD, 0, self::STARTING_KEYWORDS, $first, $tokenList);
                $tokenList->finish();

                return new InvalidCommand($comments, $exception);
            }
            $first = $tokenList->get();
        } while ($first === null || ($first->type & (TokenType::COMMENT | TokenType::DELIMITER)) !== 0);
        $tokenList->setAutoSkip($autoSkip);

        // list with invalid tokens
        if ($tokenList->invalid()) {
            $exception = null;
            foreach ($tokenList->getTokens() as $token) {
                if (($token->type & TokenType::INVALID) !== 0) {
                    $exception = $token->exception;
                    break;
                }
            }

            /** @var LexerException $exception PHPStan */
            $exception = $exception;
            $tokenList->finish();

            return new InvalidCommand($comments, $exception);
        }

        // parse!
        try {
            $command = $this->parseCommand($tokenList, $first);

            if ($comments !== []) {
                $command->setCommentsBefore($comments);
            }

            // ensures that the command was parsed completely
            if (!$tokenList->inEmbedded() && !$tokenList->isFinished()) {
                $trailingDelimiter = $tokenList->getTrailingDelimiter();
                if ($tokenList->has(TokenType::DELIMITER_DEFINITION)) {
                    $tokenList->pass(TokenType::DELIMITER);
                } else {
                    $delimiter = $tokenList->get(TokenType::DELIMITER);
                    if ($delimiter !== null) {
                        $command->setDelimiter($trailingDelimiter . $delimiter->value);
                    } {
                        $delimiter = $tokenList->expectSymbol(';');
                        $command->setDelimiter($trailingDelimiter . $delimiter->value);
                    }
                }
            } else {
                $trailingDelimiter = $tokenList->getTrailingDelimiter();
                if ($trailingDelimiter !== '') {
                    $command->setDelimiter($trailingDelimiter);
                }
            }

            return $command;
        } catch (ParsingException | Throwable $e) { // todo: remove Throwable
            // Throwable should not be here
            $tokenList->finish();

            return new InvalidCommand($comments, $e);
        }
    }

    /**
     * @return Command&Statement
     */
    private function parseCommand(TokenList $tokenList, Token $first): Command
    {
        $start = $tokenList->getPosition() - 1;

        switch (strtoupper($first->value)) {
            case '(':
                // ({SELECT|TABLE|VALUES} ...) ...
                return $this->factory->getQueryParser()->parseQuery($tokenList->rewind($start));
            case Keyword::ALTER:
                $second = strtoupper($tokenList->expect(TokenType::KEYWORD)->value);
                switch ($second) {
                    case Keyword::DATABASE:
                    case Keyword::SCHEMA:
                        // ALTER {DATABASE|SCHEMA}
                        return $this->factory->getSchemaCommandsParser()->parseAlterSchema($tokenList->rewind($start));
                    case Keyword::FUNCTION:
                        // ALTER FUNCTION
                        return $this->factory->getRoutineCommandsParser()->parseAlterFunction($tokenList->rewind($start));
                    case Keyword::INSTANCE:
                        // ALTER INSTANCE
                        return $this->factory->getInstanceCommandParser()->parseAlterInstance($tokenList->rewind($start));
                    case Keyword::LOGFILE:
                        // ALTER LOGFILE GROUP
                        return $this->factory->getLogfileGroupCommandsParser()->parseAlterLogfileGroup($tokenList->rewind($start));
                    case Keyword::PROCEDURE:
                        // ALTER PROCEDURE
                        return $this->factory->getRoutineCommandsParser()->parseAlterProcedure($tokenList->rewind($start));
                    case Keyword::RESOURCE:
                        // ALTER RESOURCE GROUP
                        return $this->factory->getResourceCommandsParser()->parseAlterResourceGroup($tokenList->rewind($start));
                    case Keyword::SERVER:
                        // ALTER SERVER
                        return $this->factory->getServerCommandsParser()->parseAlterServer($tokenList->rewind($start));
                    case Keyword::TABLE:
                    case Keyword::ONLINE:
                        // ALTER [ONLINE] TABLE
                        return $this->factory->getTableCommandsParser()->parseAlterTable($tokenList->rewind($start));
                    case Keyword::TABLESPACE:
                    case Keyword::UNDO:
                        // ALTER [UNDO] TABLESPACE
                        return $this->factory->getTablespaceCommandsParser()->parseAlterTablespace($tokenList->rewind($start));
                    case Keyword::USER:
                        // ALTER USER
                        return $this->factory->getUserCommandsParser()->parseAlterUser($tokenList->rewind($start));
                    case Keyword::EVENT:
                        // ALTER [DEFINER = { user | CURRENT_USER }] EVENT
                        return $this->factory->getEventCommandsParser()->parseAlterEvent($tokenList->rewind($start));
                    case Keyword::VIEW:
                    case Keyword::ALGORITHM:
                    case Keyword::SQL:
                        // ALTER [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}] [DEFINER = { user | CURRENT_USER }] [SQL SECURITY { DEFINER | INVOKER }] VIEW
                        return $this->factory->getViewCommandsParser()->parseAlterView($tokenList->rewind($start));
                    default:
                        if ($tokenList->seekKeyword(Keyword::EVENT, 8)) {
                            // ALTER [DEFINER = { user | CURRENT_USER }] EVENT event_name
                            return $this->factory->getEventCommandsParser()->parseAlterEvent($tokenList->rewind($start));
                        } elseif ($tokenList->seekKeyword(Keyword::VIEW, 15)) {
                            // ALTER [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}] [DEFINER = { user | CURRENT_USER }] [SQL SECURITY { DEFINER | INVOKER }] VIEW ...
                            return $this->factory->getViewCommandsParser()->parseAlterView($tokenList->rewind($start));
                        }
                        $tokenList->missingAnyKeyword(
                            Keyword::DATABASE, Keyword::SCHEMA, Keyword::FUNCTION, Keyword::INSTANCE, Keyword::LOGFILE,
                            Keyword::SERVER, Keyword::TABLE, Keyword::TABLESPACE, Keyword::USER, Keyword::EVENT, Keyword::VIEW,
                            Keyword::DEFINER, Keyword::ALGORITHM, Keyword::SQL
                        );
                }
            case Keyword::ANALYZE:
                // ANALYZE
                return $this->factory->getTableMaintenanceCommandsParser()->parseAnalyzeTable($tokenList->rewind($start));
            case Keyword::BEGIN:
                // BEGIN
                return $this->factory->getTransactionCommandsParser()->parseStartTransaction($tokenList->rewind($start));
            case Keyword::BINLOG:
                // BINLOG
                return $this->factory->getBinlogCommandParser()->parseBinlog($tokenList->rewind($start));
            case Keyword::CACHE:
                // CACHE INDEX
                return $this->factory->getCacheCommandsParser()->parseCacheIndex($tokenList->rewind($start));
            case Keyword::CALL:
                // CALL
                return $this->factory->getCallCommandParser()->parseCall($tokenList->rewind($start));
            case Keyword::CHANGE:
                $second = $tokenList->expectAnyKeyword(Keyword::MASTER, Keyword::REPLICATION);
                if ($second === Keyword::MASTER) {
                    // CHANGE MASTER TO
                    return $this->factory->getReplicationCommandsParser()->parseChangeMasterTo($tokenList->rewind($start));
                } elseif ($second === Keyword::REPLICATION) {
                    $third = $tokenList->expectAnyKeyword(Keyword::SOURCE, Keyword::FILTER);
                    if ($third === Keyword::SOURCE) {
                        // CHANGE REPLICATION SOURCE
                        return $this->factory->getReplicationCommandsParser()->parseChangeReplicationSourceTo($tokenList->rewind($start));
                    } elseif ($third === Keyword::FILTER) {
                        // CHANGE REPLICATION FILTER
                        return $this->factory->getReplicationCommandsParser()->parseChangeReplicationFilter($tokenList->rewind($start));
                    }
                }
            case Keyword::CHECK:
                // CHECK TABLE
                return $this->factory->getTableMaintenanceCommandsParser()->parseCheckTable($tokenList->rewind($start));
            case Keyword::CHECKSUM:
                // CHECKSUM TABLE
                return $this->factory->getTableMaintenanceCommandsParser()->parseChecksumTable($tokenList->rewind($start));
            case Keyword::COMMIT:
                // COMMIT
                return $this->factory->getTransactionCommandsParser()->parseCommit($tokenList->rewind($start));
            case Keyword::CREATE:
                $second = $tokenList->expectKeyword();
                switch ($second) {
                    case Keyword::DATABASE:
                    case Keyword::SCHEMA:
                        // CREATE {DATABASE | SCHEMA}
                        return $this->factory->getSchemaCommandsParser()->parseCreateSchema($tokenList->rewind($start));
                    case Keyword::LOGFILE:
                        // CREATE LOGFILE GROUP
                        return $this->factory->getLogfileGroupCommandsParser()->parseCreateLogfileGroup($tokenList->rewind($start));
                    case Keyword::RESOURCE:
                        // CREATE RESOURCE GROUP
                        return $this->factory->getResourceCommandsParser()->parseCreateResourceGroup($tokenList->rewind($start));
                    case Keyword::ROLE:
                        // CREATE ROLE
                        return $this->factory->getUserCommandsParser()->parseCreateRole($tokenList->rewind($start));
                    case Keyword::SERVER:
                        // CREATE SERVER
                        return $this->factory->getServerCommandsParser()->parseCreateServer($tokenList->rewind($start));
                    case Keyword::TABLESPACE:
                    case Keyword::UNDO:
                        // CREATE [UNDO] TABLESPACE
                        return $this->factory->getTablespaceCommandsParser()->parseCreateTablespace($tokenList->rewind($start));
                    case Keyword::USER:
                        // CREATE USER
                        return $this->factory->getUserCommandsParser()->parseCreateUser($tokenList->rewind($start));
                    case Keyword::TEMPORARY:
                    case Keyword::TABLE:
                        // CREATE [TEMPORARY] TABLE
                        return $this->factory->getTableCommandsParser()->parseCreateTable($tokenList->rewind($start));
                    case Keyword::UNIQUE:
                    case Keyword::FULLTEXT:
                    case Keyword::INDEX:
                        // CREATE [UNIQUE|FULLTEXT|SPATIAL] INDEX
                        return $this->factory->getIndexCommandsParser()->parseCreateIndex($tokenList->rewind($start));
                    case Keyword::SPATIAL:
                        $third = $tokenList->expectAnyKeyword(Keyword::INDEX, Keyword::REFERENCE);
                        if ($third === Keyword::INDEX) {
                            // CREATE [UNIQUE|FULLTEXT|SPATIAL] INDEX
                            return $this->factory->getIndexCommandsParser()->parseCreateIndex($tokenList->rewind($start));
                        } else {
                            // CREATE SPATIAL REFERENCE SYSTEM
                            return $this->factory->getSpatialCommandsParser()->parseCreateSpatialReferenceSystem($tokenList->rewind($start));
                        }
                }

                $tokenList->rewind(-1);

                if ($tokenList->hasKeywords(Keyword::OR, Keyword::REPLACE, Keyword::SPATIAL)) {
                    // CREATE OR REPLACE SPATIAL REFERENCE SYSTEM
                    return $this->factory->getSpatialCommandsParser()->parseCreateSpatialReferenceSystem($tokenList->rewind($start));
                }

                // deciding between CREATE FUNCTION and CREATE FUNCTION SONAME
                if ($tokenList->hasKeyword(Keyword::AGGREGATE)) {
                    // CREATE [AGGREGATE] FUNCTION function_name RETURNS {STRING|INTEGER|REAL|DECIMAL} SONAME
                    return $this->factory->getCreateFunctionCommandParser()->parseCreateFunction($tokenList->rewind($start));
                }
                if ($tokenList->hasKeyword(Keyword::FUNCTION)) {
                    if ($tokenList->hasKeyword(Keyword::IF)) {
                        // CREATE ... FUNCTION [IF NOT EXISTS] sp_name ([func_parameter[, ...]]) RETURNS type
                        return $this->factory->getRoutineCommandsParser()->parseCreateFunction($tokenList->rewind($start));
                    }
                    $tokenList->expectObjectIdentifier();
                    if ($tokenList->hasSymbol('(')) {
                        // CREATE ... FUNCTION ... sp_name ([func_parameter[, ...]]) RETURNS type
                        return $this->factory->getRoutineCommandsParser()->parseCreateFunction($tokenList->rewind($start));
                    }
                    // CREATE ... FUNCTION function_name RETURNS {STRING|INTEGER|REAL|DECIMAL} SONAME
                    return $this->factory->getCreateFunctionCommandParser()->parseCreateFunction($tokenList->rewind($start));
                }

                // eliminating unique prefixes for CREATE VIEW
                if ($tokenList->hasKeywords(Keyword::OR, Keyword::REPLACE)) {
                    // CREATE [OR REPLACE] [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}] [DEFINER = { user | CURRENT_USER }] [SQL SECURITY { DEFINER | INVOKER }] VIEW
                    return $this->factory->getViewCommandsParser()->parseCreateView($tokenList->rewind($start));
                } elseif ($tokenList->hasKeyword(Keyword::ALGORITHM)) {
                    // CREATE ... [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}] [DEFINER = { user | CURRENT_USER }] [SQL SECURITY { DEFINER | INVOKER }] VIEW
                    return $this->factory->getViewCommandsParser()->parseCreateView($tokenList->rewind($start));
                }

                // get DEFINER out of the way
                if ($tokenList->hasKeyword(Keyword::DEFINER)) {
                    $tokenList->passSymbol('=');
                    if ($tokenList->hasKeyword(Keyword::CURRENT_USER)) {
                        if ($tokenList->hasSymbol('(')) {
                            $tokenList->passSymbol(')');
                        }
                    } else {
                        $tokenList->expectUserName();
                    }
                }
                if ($tokenList->hasKeyword(Keyword::EVENT)) {
                    // CREATE ... EVENT
                    return $this->factory->getEventCommandsParser()->parseCreateEvent($tokenList->rewind($start));
                } elseif ($tokenList->hasKeyword(Keyword::FUNCTION)) {
                    // CREATE ... FUNCTION
                    return $this->factory->getRoutineCommandsParser()->parseCreateFunction($tokenList->rewind($start));
                } elseif ($tokenList->hasKeyword(Keyword::PROCEDURE)) {
                    // CREATE ... PROCEDURE
                    return $this->factory->getRoutineCommandsParser()->parseCreateProcedure($tokenList->rewind($start));
                } elseif ($tokenList->hasKeyword(Keyword::TRIGGER)) {
                    // CREATE ... TRIGGER
                    return $this->factory->getTriggerCommandsParser()->parseCreateTrigger($tokenList->rewind($start));
                } elseif ($tokenList->seekKeyword(Keyword::VIEW, 5)) {
                    // CREATE ... [SQL SECURITY { DEFINER | INVOKER }] VIEW
                    return $this->factory->getViewCommandsParser()->parseCreateView($tokenList->rewind($start));
                }

                $tokenList->rewind($start)->missingAnyKeyword(
                    Keyword::DATABASE, Keyword::SCHEMA, Keyword::LOGFILE, Keyword::ROLE, Keyword::SERVER,
                    Keyword::TABLESPACE, Keyword::TABLE, Keyword::USER, Keyword::EVENT, Keyword::FUNCTION,
                    Keyword::INDEX, Keyword::PROCEDURE, Keyword::TABLE, Keyword::TRIGGER, Keyword::VIEW, Keyword::DEFINER
                );
            case Keyword::DEALLOCATE:
                // {DEALLOCATE | DROP} PREPARE
                return $this->factory->getPreparedCommandsParser()->parseDeallocatePrepare($tokenList->rewind($start));
            case Keyword::DELETE:
                // DELETE
                return $this->factory->getDeleteCommandParser()->parseDelete($tokenList->rewind($start));
            case Keyword::DELIMITER:
                // DELIMITER
                return $this->factory->getDelimiterCommandParser()->parseDelimiter($tokenList->rewind($start));
            case Keyword::DESC:
                // DESC
                return $this->factory->getExplainCommandParser()->parseExplain($tokenList->rewind($start));
            case Keyword::DESCRIBE:
                // DESCRIBE
                return $this->factory->getExplainCommandParser()->parseExplain($tokenList->rewind($start));
            case Keyword::DO:
                // DO
                return $this->factory->getDoCommandParser()->parseDo($tokenList->rewind($start));
            case Keyword::DROP:
                $second = $tokenList->expectAnyKeyword(
                    Keyword::DATABASE, Keyword::SCHEMA, Keyword::EVENT, Keyword::FUNCTION, Keyword::INDEX,
                    Keyword::LOGFILE, Keyword::PREPARE, Keyword::PROCEDURE, Keyword::ROLE, Keyword::RESOURCE,
                    Keyword::SERVER, Keyword::SPATIAL, Keyword::TABLE, Keyword::TABLES, Keyword::TEMPORARY,
                    Keyword::TABLESPACE, Keyword::TRIGGER, Keyword::UNDO, Keyword::USER, Keyword::VIEW
                );
                switch ($second) {
                    case Keyword::DATABASE:
                    case Keyword::SCHEMA:
                        // DROP {DATABASE | SCHEMA}
                        return $this->factory->getSchemaCommandsParser()->parseDropSchema($tokenList->rewind($start));
                    case Keyword::EVENT:
                        // DROP EVENT
                        return $this->factory->getEventCommandsParser()->parseDropEvent($tokenList->rewind($start));
                    case Keyword::FUNCTION:
                        // DROP {PROCEDURE | FUNCTION}
                        return $this->factory->getRoutineCommandsParser()->parseDropFunction($tokenList->rewind($start));
                    case Keyword::INDEX:
                        // DROP INDEX
                        return $this->factory->getIndexCommandsParser()->parseDropIndex($tokenList->rewind($start));
                    case Keyword::LOGFILE:
                        // DROP LOGFILE GROUP
                        return $this->factory->getLogfileGroupCommandsParser()->parseDropLogfileGroup($tokenList->rewind($start));
                    case Keyword::PREPARE:
                        // {DEALLOCATE | DROP} PREPARE
                        return $this->factory->getPreparedCommandsParser()->parseDeallocatePrepare($tokenList->rewind($start));
                    case Keyword::PROCEDURE:
                        // DROP {PROCEDURE | FUNCTION}
                        return $this->factory->getRoutineCommandsParser()->parseDropProcedure($tokenList->rewind($start));
                    case Keyword::RESOURCE:
                        // DROP RESOURCE GROUP
                        return $this->factory->getResourceCommandsParser()->parseDropResourceGroup($tokenList->rewind($start));
                    case Keyword::ROLE:
                        // DROP ROLE
                        return $this->factory->getUserCommandsParser()->parseDropRole($tokenList->rewind($start));
                    case Keyword::SERVER:
                        // DROP SERVER
                        return $this->factory->getServerCommandsParser()->parseDropServer($tokenList->rewind($start));
                    case Keyword::SPATIAL:
                        // DROP SPATIAL REFERENCE SYSTEM
                        return $this->factory->getSpatialCommandsParser()->parseDropSpatialReferenceSystem($tokenList->rewind($start));
                    case Keyword::TABLE:
                    case Keyword::TABLES:
                    case Keyword::TEMPORARY:
                        // DROP [TEMPORARY] TABLE
                        return $this->factory->getTableCommandsParser()->parseDropTable($tokenList->rewind($start));
                    case Keyword::TABLESPACE:
                    case Keyword::UNDO:
                        // DROP [UNDO] TABLESPACE
                        return $this->factory->getTablespaceCommandsParser()->parseDropTablespace($tokenList->rewind($start));
                    case Keyword::TRIGGER:
                        // DROP TRIGGER
                        return $this->factory->getTriggerCommandsParser()->parseDropTrigger($tokenList->rewind($start));
                    case Keyword::USER:
                        // DROP USER
                        return $this->factory->getUserCommandsParser()->parseDropUser($tokenList->rewind($start));
                    case Keyword::VIEW:
                        // DROP VIEW
                        return $this->factory->getViewCommandsParser()->parseDropView($tokenList->rewind($start));
                }
            case Keyword::EXECUTE:
                // EXECUTE
                return $this->factory->getPreparedCommandsParser()->parseExecute($tokenList->rewind($start));
            case Keyword::EXPLAIN:
                // EXPLAIN
                return $this->factory->getExplainCommandParser()->parseExplain($tokenList->rewind($start));
            case Keyword::FLUSH:
                if ($tokenList->hasAnyKeyword(Keyword::TABLES, Keyword::TABLE)
                    || $tokenList->hasKeywords(Keyword::LOCAL, Keyword::TABLES)
                    || $tokenList->hasKeywords(Keyword::LOCAL, Keyword::TABLE)
                    || $tokenList->hasKeywords(Keyword::NO_WRITE_TO_BINLOG, Keyword::TABLES)
                    || $tokenList->hasKeywords(Keyword::NO_WRITE_TO_BINLOG, Keyword::TABLE)
                ) {
                    // FLUSH TABLES
                    return $this->factory->getFlushCommandParser()->parseFlushTables($tokenList->rewind($start));
                } else {
                    // FLUSH
                    return $this->factory->getFlushCommandParser()->parseFlush($tokenList->rewind($start));
                }
            case Keyword::GET:
                // GET DIAGNOSTICS
                return $this->factory->getErrorCommandsParser()->parseGetDiagnostics($tokenList->rewind($start));
            case Keyword::GRANT:
                // GRANT
                return $this->factory->getUserCommandsParser()->parseGrant($tokenList->rewind($start));
            case Keyword::HANDLER:
                // HANDLER
                $tokenList->expectObjectIdentifier();
                $keyword = $tokenList->expectAnyKeyword(Keyword::OPEN, Keyword::READ, Keyword::CLOSE);
                if ($keyword === Keyword::OPEN) {
                    return $this->factory->getHandlerCommandParser()->parseHandlerOpen($tokenList->rewind($start));
                } elseif ($keyword === Keyword::READ) {
                    return $this->factory->getHandlerCommandParser()->parseHandlerRead($tokenList->rewind($start));
                } else {
                    return $this->factory->getHandlerCommandParser()->parseHandlerClose($tokenList->rewind($start));
                }
            case Keyword::HELP:
                // HELP
                return $this->factory->getHelpCommandParser()->parseHelp($tokenList->rewind($start));
            case Keyword::IMPORT:
                // IMPORT
                return $this->factory->getImportCommandParser()->parseImport($tokenList->rewind($start));
            case Keyword::INSERT:
                // INSERT
                return $this->factory->getInsertCommandParser()->parseInsert($tokenList->rewind($start));
            case Keyword::INSTALL:
                $secondInstall = $tokenList->expectAnyKeyword(Keyword::COMPONENT, Keyword::PLUGIN);
                if ($secondInstall === Keyword::COMPONENT) {
                    // INSTALL COMPONENT
                    return $this->factory->getComponentCommandsParser()->parseInstallComponent($tokenList->rewind($start));
                } else {
                    // INSTALL PLUGIN
                    return $this->factory->getPluginCommandsParser()->parseInstallPlugin($tokenList->rewind($start));
                }
            case Keyword::KILL:
                // KILL
                return $this->factory->getKillCommandParser()->parseKill($tokenList->rewind($start));
            case Keyword::LOCK:
                $secondLock = $tokenList->expectAnyKeyword(Keyword::TABLE, Keyword::TABLES, Keyword::INSTANCE);
                if ($secondLock === Keyword::INSTANCE) {
                    // LOCK INSTANCE
                    return $this->factory->getTransactionCommandsParser()->parseLockInstance($tokenList->rewind($start));
                } else {
                    // LOCK TABLES
                    return $this->factory->getTransactionCommandsParser()->parseLockTables($tokenList->rewind($start));
                }
            case Keyword::LOAD:
                $secondLoad = $tokenList->expectAnyKeyword(Keyword::DATA, Keyword::INDEX, Keyword::XML);
                if ($secondLoad === Keyword::DATA) {
                    // LOAD DATA
                    return $this->factory->getLoadCommandsParser()->parseLoadData($tokenList->rewind($start));
                } elseif ($secondLoad === Keyword::INDEX) {
                    // LOAD INDEX INTO CACHE
                    return $this->factory->getCacheCommandsParser()->parseLoadIndexIntoCache($tokenList->rewind($start));
                } else {
                    // LOAD XML
                    return $this->factory->getLoadCommandsParser()->parseLoadXml($tokenList->rewind($start));
                }
            case Keyword::OPTIMIZE:
                // OPTIMIZE TABLE
                return $this->factory->getTableMaintenanceCommandsParser()->parseOptimizeTable($tokenList->rewind($start));
            case Keyword::PREPARE:
                // PREPARE
                return $this->factory->getPreparedCommandsParser()->parsePrepare($tokenList->rewind($start));
            case Keyword::PURGE:
                // PURGE { BINARY | MASTER } LOGS
                return $this->factory->getReplicationCommandsParser()->parsePurgeBinaryLogs($tokenList->rewind($start));
            case Keyword::RELEASE:
                // RELEASE SAVEPOINT
                return $this->factory->getTransactionCommandsParser()->parseReleaseSavepoint($tokenList->rewind($start));
            case Keyword::RENAME:
                $second = $tokenList->expectAnyKeyword(Keyword::TABLE, Keyword::TABLES, Keyword::USER);
                if ($second === Keyword::TABLE || $second === Keyword::TABLES) {
                    // RENAME TABLE
                    return $this->factory->getTableCommandsParser()->parseRenameTable($tokenList->rewind($start));
                } else {
                    // RENAME USER
                    return $this->factory->getUserCommandsParser()->parseRenameUser($tokenList->rewind($start));
                }
            case Keyword::REPAIR:
                // REPAIR TABLE
                return $this->factory->getTableMaintenanceCommandsParser()->parseRepairTable($tokenList->rewind($start));
            case Keyword::REPLACE:
                // REPLACE
                return $this->factory->getInsertCommandParser()->parseReplace($tokenList->rewind($start));
            case Keyword::RESET:
                if ($tokenList->hasKeyword(Keyword::PERSIST)) {
                    // RESET PERSIST
                    return $this->factory->getSetCommandsParser()->parseResetPersist($tokenList->rewind($start));
                }
                $keyword = $tokenList->expectAnyKeyword(Keyword::MASTER, Keyword::REPLICA, Keyword::SLAVE, Keyword::QUERY);
                if ($keyword === Keyword::MASTER) {
                    if ($tokenList->hasSymbol(',')) {
                        // RESET MASTER, REPLICA, SLAVE, QUERY CACHE
                        return $this->factory->getResetCommandParser()->parseReset($tokenList->rewind($start));
                    }

                    // RESET MASTER
                    return $this->factory->getReplicationCommandsParser()->parseResetMaster($tokenList->rewind($start));
                } elseif ($keyword === Keyword::REPLICA) {
                    if ($tokenList->hasSymbol(',')) {
                        // RESET MASTER, REPLICA, SLAVE, QUERY CACHE
                        return $this->factory->getResetCommandParser()->parseReset($tokenList->rewind($start));
                    }

                    // RESET REPLICA
                    return $this->factory->getReplicationCommandsParser()->parseResetReplica($tokenList->rewind($start));
                } elseif ($keyword === Keyword::SLAVE) {
                    if ($tokenList->hasSymbol(',')) {
                        // RESET MASTER, REPLICA, SLAVE, QUERY CACHE
                        return $this->factory->getResetCommandParser()->parseReset($tokenList->rewind($start));
                    }

                    // RESET SLAVE
                    return $this->factory->getReplicationCommandsParser()->parseResetSlave($tokenList->rewind($start));
                } else {
                    // RESET MASTER, REPLICA, SLAVE, QUERY CACHE
                    return $this->factory->getResetCommandParser()->parseReset($tokenList->rewind($start));
                }
            case Keyword::RESIGNAL:
                // RESIGNAL
                return $this->factory->getErrorCommandsParser()->parseSignalResignal($tokenList->rewind($start));
            case Keyword::RESTART:
                // RESTART
                return $this->factory->getRestartCommandParser()->parseRestart($tokenList->rewind($start));
            case Keyword::REVOKE:
                // REVOKE
                return $this->factory->getUserCommandsParser()->parseRevoke($tokenList->rewind($start));
            case Keyword::ROLLBACK:
                // ROLLBACK
                if ($tokenList->seekKeyword(Keyword::TO, 3)) {
                    return $this->factory->getTransactionCommandsParser()->parseRollbackToSavepoint($tokenList->rewind($start));
                } else {
                    return $this->factory->getTransactionCommandsParser()->parseRollback($tokenList->rewind($start));
                }
            case Keyword::SAVEPOINT:
                // SAVEPOINT
                return $this->factory->getTransactionCommandsParser()->parseSavepoint($tokenList->rewind($start));
            case Keyword::SELECT:
                // SELECT
                return $this->factory->getQueryParser()->parseQuery($tokenList->rewind($start));
            case Keyword::SET:
                $second = $tokenList->getKeyword();
                switch ($second) {
                    case Keyword::CHARACTER:
                    case Keyword::CHAR:
                    case Keyword::CHARSET:
                        // SET {CHARACTER SET | CHAR SET | CHARSET}
                        return $this->factory->getSetCommandsParser()->parseSetCharacterSet($tokenList->rewind($start));
                    case Keyword::DEFAULT:
                        // SET DEFAULT ROLE
                        return $this->factory->getUserCommandsParser()->parseSetDefaultRole($tokenList->rewind($start));
                    case Keyword::NAMES:
                        // SET NAMES
                        return $this->factory->getSetCommandsParser()->parseSetNames($tokenList->rewind($start));
                    case Keyword::PASSWORD:
                        // SET PASSWORD
                        return $this->factory->getUserCommandsParser()->parseSetPassword($tokenList->rewind($start));
                    case Keyword::RESOURCE:
                        // SET RESOURCE GROUP
                        return $this->factory->getResourceCommandsParser()->parseSetResourceGroup($tokenList->rewind($start));
                    case Keyword::ROLE:
                        // SET ROLE
                        return $this->factory->getUserCommandsParser()->parseSetRole($tokenList->rewind($start));
                    case Keyword::GLOBAL:
                    case Keyword::SESSION:
                    case Keyword::LOCAL:
                    case Keyword::TRANSACTION:
                        if ($second === Keyword::TRANSACTION || $tokenList->hasKeyword(Keyword::TRANSACTION)) {
                            // SET [GLOBAL | SESSION | LOCAL] TRANSACTION
                            return $this->factory->getTransactionCommandsParser()->parseSetTransaction($tokenList->rewind($start));
                        } else {
                            // SET
                            return $this->factory->getSetCommandsParser()->parseSet($tokenList->rewind($start));
                        }
                    default:
                        // SET
                        return $this->factory->getSetCommandsParser()->parseSet($tokenList->rewind($start));
                }
            case Keyword::SHOW:
                // SHOW
                return $this->factory->getShowCommandsParser()->parseShow($tokenList->rewind($start));
            case Keyword::SHUTDOWN:
                // SHUTDOWN
                return $this->factory->getShutdownCommandParser()->parseShutdown($tokenList->rewind($start));
            case Keyword::SIGNAL:
                // SIGNAL
                return $this->factory->getErrorCommandsParser()->parseSignalResignal($tokenList->rewind($start));
            case Keyword::START:
                $secondStart = $tokenList->expectAnyKeyword(Keyword::GROUP_REPLICATION, Keyword::SLAVE, Keyword::REPLICA, Keyword::TRANSACTION);
                if ($secondStart === Keyword::GROUP_REPLICATION) {
                    // START GROUP_REPLICATION
                    return $this->factory->getReplicationCommandsParser()->parseStartGroupReplication($tokenList->rewind($start));
                } elseif ($secondStart === Keyword::SLAVE || $secondStart === Keyword::REPLICA) {
                    // START SLAVE
                    // START REPLICA
                    return $this->factory->getReplicationCommandsParser()->parseStartReplicaOrSlave($tokenList->rewind($start));
                } else {
                    // START TRANSACTION
                    return $this->factory->getTransactionCommandsParser()->parseStartTransaction($tokenList->rewind($start));
                }
            case Keyword::STOP:
                $secondStop = $tokenList->expectAnyKeyword(Keyword::GROUP_REPLICATION, Keyword::SLAVE, Keyword::REPLICA);
                if ($secondStop === Keyword::GROUP_REPLICATION) {
                    // STOP GROUP_REPLICATION
                    return $this->factory->getReplicationCommandsParser()->parseStopGroupReplication($tokenList->rewind($start));
                } elseif ($secondStop === Keyword::SLAVE) {
                    // STOP SLAVE
                    return $this->factory->getReplicationCommandsParser()->parseStopSlave($tokenList->rewind($start));
                } else {
                    // STOP REPLICA
                    return $this->factory->getReplicationCommandsParser()->parseStopReplica($tokenList->rewind($start));
                }
            case Keyword::TABLE:
                // TABLE
                return $this->factory->getQueryParser()->parseQuery($tokenList->rewind($start));
            case Keyword::TRUNCATE:
                // TRUNCATE [TABLE]
                return $this->factory->getTableCommandsParser()->parseTruncateTable($tokenList->rewind($start));
            case Keyword::UNINSTALL:
                $secondUninstall = $tokenList->expectAnyKeyword(Keyword::COMPONENT, Keyword::PLUGIN);
                if ($secondUninstall === Keyword::COMPONENT) {
                    // UNINSTALL COMPONENT
                    return $this->factory->getComponentCommandsParser()->parseUninstallComponent($tokenList->rewind($start));
                } else {
                    // UNINSTALL PLUGIN
                    return $this->factory->getPluginCommandsParser()->parseUninstallPlugin($tokenList->rewind($start));
                }
            case Keyword::UNLOCK:
                $secondUnlock = $tokenList->expectAnyKeyword(Keyword::TABLE, Keyword::TABLES, Keyword::INSTANCE);
                if ($secondUnlock === Keyword::INSTANCE) {
                    // UNLOCK INSTANCE
                    return $this->factory->getTransactionCommandsParser()->parseUnlockInstance($tokenList->rewind($start));
                } else {
                    // UNLOCK TABLES
                    return $this->factory->getTransactionCommandsParser()->parseUnlockTables($tokenList->rewind($start));
                }
            case Keyword::UPDATE:
                // UPDATE
                return $this->factory->getUpdateCommandParser()->parseUpdate($tokenList->rewind($start));
            case Keyword::USE:
                // USE
                return $this->factory->getUseCommandParser()->parseUse($tokenList->rewind($start));
            case Keyword::VALUES:
                // VALUES
                return $this->factory->getQueryParser()->parseQuery($tokenList->rewind($start));
            case Keyword::WITH:
                // WITH ... SELECT|UPDATE|DELETE
                return $this->factory->getQueryParser()->parseWith($tokenList->rewind($start));
            case Keyword::XA:
                // XA {START|BEGIN}
                // XA END
                // XA PREPARE
                // XA COMMIT
                // XA ROLLBACK
                // XA RECOVER
                return $this->factory->getXaTransactionCommandsParser()->parseXa($tokenList->rewind($start));
            default:
                $tokenList->rewind($start)->missingAnyKeyword(...self::STARTING_KEYWORDS);
        }
    }

}
