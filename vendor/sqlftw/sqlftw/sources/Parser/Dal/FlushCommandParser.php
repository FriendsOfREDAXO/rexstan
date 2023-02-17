<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dal;

use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Dal\Flush\FlushCommand;
use SqlFtw\Sql\Dal\Flush\FlushOption;
use SqlFtw\Sql\Dal\Flush\FlushTablesCommand;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Keyword;

class FlushCommandParser
{

    /**
     * FLUSH [NO_WRITE_TO_BINLOG | LOCAL]
     *     flush_option [, flush_option] ...
     *
     * flush_option: {
     *     BINARY LOGS
     *   | ENGINE LOGS
     *   | ERROR LOGS
     *   | GENERAL LOGS
     *   | HOSTS
     *   | LOGS
     *   | PRIVILEGES
     *   | OPTIMIZER_COSTS
     *   | RELAY LOGS [FOR CHANNEL channel]
     *   | SLOW LOGS
     *   | STATUS
     *   | USER_RESOURCES
     * }
     */
    public function parseFlush(TokenList $tokenList): FlushCommand
    {
        $tokenList->expectKeyword(Keyword::FLUSH);
        $local = $tokenList->hasAnyKeyword(Keyword::NO_WRITE_TO_BINLOG, Keyword::LOCAL);
        $options = [];
        $channel = null;
        do {
            $options[] = $option = $tokenList->expectMultiKeywordsEnum(FlushOption::class);
            if ($option->equalsValue(FlushOption::RELAY_LOGS) && $tokenList->hasKeywords(Keyword::FOR, Keyword::CHANNEL)) {
                $channel = $tokenList->expectNonReservedNameOrString();
            }
        } while ($tokenList->hasSymbol(','));

        return new FlushCommand($options, $channel, $local);
    }

    /**
     * FLUSH [NO_WRITE_TO_BINLOG | LOCAL]
     *   TABLES [tbl_name [, tbl_name] ...] [WITH READ LOCK | FOR EXPORT]
     */
    public function parseFlushTables(TokenList $tokenList): FlushTablesCommand
    {
        $tokenList->expectKeyword(Keyword::FLUSH);
        $local = $tokenList->hasAnyKeyword(Keyword::NO_WRITE_TO_BINLOG, Keyword::LOCAL);
        $tokenList->expectAnyKeyword(Keyword::TABLES, Keyword::TABLE);

        $tables = null;
        $table = $tokenList->getObjectIdentifier();
        if ($table !== null) {
            /** @var non-empty-list<ObjectIdentifier> $tables */
            $tables = [$table];
            while ($tokenList->hasSymbol(',')) {
                $tables[] = $tokenList->expectObjectIdentifier();
            }
        }
        $keyword = $tokenList->getAnyKeyword(Keyword::WITH, Keyword::FOR);
        $withReadLock = $forExport = false;
        if ($keyword === Keyword::WITH) {
            $tokenList->expectKeywords(Keyword::READ, Keyword::LOCK);
            $withReadLock = true;
        } elseif ($keyword === Keyword::FOR) {
            if ($tables === null) {
                throw new ParserException('Tables have to be defined for FLUSH TABLES FOR EXPORT.', $tokenList);
            }
            $tokenList->expectKeyword(Keyword::EXPORT);
            $forExport = true;
        }

        return new FlushTablesCommand($tables, $withReadLock, $forExport, $local);
    }

}
