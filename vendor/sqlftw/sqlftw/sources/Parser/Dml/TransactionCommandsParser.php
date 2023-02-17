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
use SqlFtw\Sql\Dml\Transaction\CommitCommand;
use SqlFtw\Sql\Dml\Transaction\LockInstanceCommand;
use SqlFtw\Sql\Dml\Transaction\LockTablesCommand;
use SqlFtw\Sql\Dml\Transaction\LockTablesItem;
use SqlFtw\Sql\Dml\Transaction\LockTableType;
use SqlFtw\Sql\Dml\Transaction\ReleaseSavepointCommand;
use SqlFtw\Sql\Dml\Transaction\RollbackCommand;
use SqlFtw\Sql\Dml\Transaction\RollbackToSavepointCommand;
use SqlFtw\Sql\Dml\Transaction\SavepointCommand;
use SqlFtw\Sql\Dml\Transaction\SetTransactionCommand;
use SqlFtw\Sql\Dml\Transaction\StartTransactionCommand;
use SqlFtw\Sql\Dml\Transaction\TransactionIsolationLevel;
use SqlFtw\Sql\Dml\Transaction\UnlockInstanceCommand;
use SqlFtw\Sql\Dml\Transaction\UnlockTablesCommand;
use SqlFtw\Sql\Expression\Scope;
use SqlFtw\Sql\Keyword;

class TransactionCommandsParser
{

    private ExpressionParser $expressionParser;

    public function __construct(ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }

    /**
     * COMMIT [WORK] [AND [NO] CHAIN] [[NO] RELEASE]
     */
    public function parseCommit(TokenList $tokenList): CommitCommand
    {
        $tokenList->expectKeyword(Keyword::COMMIT);
        $tokenList->passKeyword(Keyword::WORK);

        $chain = null;
        if ($tokenList->hasKeyword(Keyword::AND)) {
            $chain = !$tokenList->hasKeyword(Keyword::NO);
            $tokenList->expectKeyword(Keyword::CHAIN);
        }

        $release = null;
        if ($tokenList->hasKeyword(Keyword::NO)) {
            $release = false;
            $tokenList->expectKeyword(Keyword::RELEASE);
        } elseif ($tokenList->hasKeyword(Keyword::RELEASE)) {
            $release = true;
        }

        if ($chain === true && $release === true) {
            throw new ParserException('CHAIN and RELEASE cannot be both specified.', $tokenList);
        }

        return new CommitCommand($chain, $release);
    }

    /**
     * LOCK TABLES
     *     tbl_name [[AS] alias] lock_type
     *     [, tbl_name [[AS] alias] lock_type] ...
     *
     * lock_type:
     *     READ [LOCAL]
     *   | [LOW_PRIORITY] WRITE
     */
    public function parseLockTables(TokenList $tokenList): LockTablesCommand
    {
        $tokenList->expectKeyword(Keyword::LOCK);
        $tokenList->expectAnyKeyword(Keyword::TABLES, Keyword::TABLE);
        $items = [];
        do {
            $table = $tokenList->expectObjectIdentifier();
            $alias = $this->expressionParser->parseAlias($tokenList);
            $lock = $tokenList->expectMultiKeywordsEnum(LockTableType::class);

            $items[] = new LockTablesItem($table, $lock, $alias);
        } while ($tokenList->hasSymbol(','));

        return new LockTablesCommand($items);
    }

    /**
     * RELEASE SAVEPOINT identifier
     */
    public function parseReleaseSavepoint(TokenList $tokenList): ReleaseSavepointCommand
    {
        $tokenList->expectKeywords(Keyword::RELEASE, Keyword::SAVEPOINT);
        $name = $tokenList->expectName(null);

        return new ReleaseSavepointCommand($name);
    }

    /**
     * ROLLBACK [WORK] [AND [NO] CHAIN] [[NO] RELEASE]
     */
    public function parseRollback(TokenList $tokenList): RollbackCommand
    {
        $tokenList->expectKeyword(Keyword::ROLLBACK);
        $tokenList->passKeyword(Keyword::WORK);

        $chain = null;
        if ($tokenList->hasKeyword(Keyword::AND)) {
            $chain = !$tokenList->hasKeyword(Keyword::NO);
            $tokenList->expectKeyword(Keyword::CHAIN);
        }

        $release = null;
        if ($tokenList->hasKeyword(Keyword::NO)) {
            $release = false;
            $tokenList->expectKeyword(Keyword::RELEASE);
        } elseif ($tokenList->hasKeyword(Keyword::RELEASE)) {
            $release = true;
        }

        if ($chain === true && $release === true) {
            throw new ParserException('CHAIN and RELEASE cannot be both specified.', $tokenList);
        }

        return new RollbackCommand($chain, $release);
    }

    /**
     * ROLLBACK [WORK] TO [SAVEPOINT] identifier
     */
    public function parseRollbackToSavepoint(TokenList $tokenList): RollbackToSavepointCommand
    {
        $tokenList->expectKeyword(Keyword::ROLLBACK);
        $tokenList->passKeyword(Keyword::WORK);
        $tokenList->expectKeyword(Keyword::TO);
        $tokenList->passKeyword(Keyword::SAVEPOINT);

        $name = $tokenList->expectName(null);

        return new RollbackToSavepointCommand($name);
    }

    /**
     * SAVEPOINT identifier
     */
    public function parseSavepoint(TokenList $tokenList): SavepointCommand
    {
        $tokenList->expectKeyword(Keyword::SAVEPOINT);
        $name = $tokenList->expectName(null);

        return new SavepointCommand($name);
    }

    /**
     * SET [GLOBAL | SESSION | LOCAL] TRANSACTION
     *     transaction_characteristic [, transaction_characteristic] ...
     *
     * transaction_characteristic:
     *     ISOLATION LEVEL level
     *   | READ WRITE
     *   | READ ONLY
     *
     * level:
     *     REPEATABLE READ
     *   | READ COMMITTED
     *   | READ UNCOMMITTED
     *   | SERIALIZABLE
     */
    public function parseSetTransaction(TokenList $tokenList): SetTransactionCommand
    {
        $tokenList->expectKeyword(Keyword::SET);

        if ($tokenList->hasKeyword(Keyword::LOCAL)) {
            $scope = new Scope(Scope::SESSION);
        } else {
            $scope = $tokenList->getKeywordEnum(Scope::class);
        }

        $tokenList->expectKeyword(Keyword::TRANSACTION);

        $isolationLevel = $write = null;
        do {
            if ($tokenList->hasKeywords(Keyword::ISOLATION, Keyword::LEVEL)) {
                $isolationLevel = $tokenList->expectMultiKeywordsEnum(TransactionIsolationLevel::class);
            } elseif ($tokenList->hasKeyword(Keyword::READ)) {
                if ($write !== null) {
                    throw new ParserException('READ/WRITE defined twice.', $tokenList);
                }
                if ($tokenList->hasKeyword(Keyword::WRITE)) {
                    $write = true;
                } else {
                    $tokenList->expectKeyword(Keyword::ONLY);
                    $write = false;
                }
            }
        } while ($tokenList->hasSymbol(','));

        return new SetTransactionCommand($scope, $isolationLevel, $write);
    }

    /**
     * START TRANSACTION
     *     [transaction_characteristic [, transaction_characteristic] ...]
     *
     * transaction_characteristic:
     *     WITH CONSISTENT SNAPSHOT
     *   | READ WRITE
     *   | READ ONLY
     *
     * BEGIN [WORK]
     */
    public function parseStartTransaction(TokenList $tokenList): StartTransactionCommand
    {
        if ($tokenList->hasKeyword(Keyword::BEGIN)) {
            $tokenList->passKeyword(Keyword::WORK);

            return new StartTransactionCommand();
        }

        $tokenList->expectKeywords(Keyword::START, Keyword::TRANSACTION);

        $consistent = $write = null;
        do {
            if ($tokenList->hasKeyword(Keyword::WITH)) {
                $tokenList->expectKeywords(Keyword::CONSISTENT, Keyword::SNAPSHOT);
                $consistent = true;
            } elseif ($tokenList->hasKeyword(Keyword::READ)) {
                if ($write !== null) {
                    throw new ParserException('READ/WRITE defined twice.', $tokenList);
                }
                if ($tokenList->hasKeyword(Keyword::WRITE)) {
                    $write = true;
                } else {
                    $tokenList->expectKeyword(Keyword::ONLY);
                    $write = false;
                }
            } else {
                break;
            }
        } while ($tokenList->hasSymbol(','));

        return new StartTransactionCommand($consistent, $write);
    }

    /**
     * UNLOCK TABLES
     */
    public function parseUnlockTables(TokenList $tokenList): UnlockTablesCommand
    {
        $tokenList->expectKeyword(Keyword::UNLOCK);
        $tokenList->expectAnyKeyword(Keyword::TABLES, Keyword::TABLE);

        return new UnlockTablesCommand();
    }

    public function parseLockInstance(TokenList $tokenList): LockInstanceCommand
    {
        $tokenList->expectKeywords(Keyword::LOCK, Keyword::INSTANCE, Keyword::FOR, Keyword::BACKUP);

        return new LockInstanceCommand();
    }

    public function parseUnlockInstance(TokenList $tokenList): UnlockInstanceCommand
    {
        $tokenList->expectKeywords(Keyword::UNLOCK, Keyword::INSTANCE);

        return new UnlockInstanceCommand();
    }

}
