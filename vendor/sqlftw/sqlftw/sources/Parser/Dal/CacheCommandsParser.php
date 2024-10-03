<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dal;

use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Dal\Cache\CacheIndexCommand;
use SqlFtw\Sql\Dal\Cache\LoadIndexIntoCacheCommand;
use SqlFtw\Sql\Dal\Cache\TableIndexList;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Keyword;

/**
 * MySQL MyISAM tables only
 */
class CacheCommandsParser
{

    /**
     * CACHE INDEX
     *     tbl_index_list [, tbl_index_list] ...
     *     [PARTITION (partition_list | ALL)]
     *     IN key_cache_name
     *
     * tbl_index_list:
     *     tbl_name [[INDEX|KEY] (index_name[, index_name] ...)]
     *
     * partition_list:
     *     partition_name[, partition_name][, ...]
     */
    public function parseCacheIndex(TokenList $tokenList): CacheIndexCommand
    {
        $tokenList->expectKeywords(Keyword::CACHE, Keyword::INDEX);

        $tableIndexLists = [];
        do {
            $table = $tokenList->expectObjectIdentifier();
            $indexes = $this->parseIndexes($tokenList);

            $tableIndexLists[] = new TableIndexList($table, $indexes);
        } while ($tokenList->hasSymbol(','));

        $partitions = $this->parsePartitions($tokenList);

        $tokenList->expectKeyword(Keyword::IN);
        $keyCache = $tokenList->expectName(EntityType::INDEX_CACHE);

        return new CacheIndexCommand($keyCache, $tableIndexLists, $partitions);
    }

    /**
     * LOAD INDEX INTO CACHE
     *     tbl_index_list [, tbl_index_list] ...
     *
     * tbl_index_list:
     *     tbl_name
     *     [PARTITION (partition_list | ALL)]
     *     [[INDEX|KEY] (index_name[, index_name] ...)]
     *     [IGNORE LEAVES]
     *
     * partition_list:
     *     partition_name[, partition_name][, ...]
     */
    public function parseLoadIndexIntoCache(TokenList $tokenList): LoadIndexIntoCacheCommand
    {
        $tokenList->expectKeywords(Keyword::LOAD, Keyword::INDEX, Keyword::INTO, Keyword::CACHE);

        $tableIndexLists = [];
        do {
            $table = $tokenList->expectObjectIdentifier();
            $partitions = $this->parsePartitions($tokenList);
            $indexes = $this->parseIndexes($tokenList);
            $ignoreLeaves = $tokenList->hasKeywords(Keyword::IGNORE, Keyword::LEAVES);

            $tableIndexLists[] = new TableIndexList($table, $indexes, $partitions, $ignoreLeaves);
        } while ($tokenList->hasSymbol(','));

        return new LoadIndexIntoCacheCommand($tableIndexLists);
    }

    /**
     * @return non-empty-list<string>|null
     */
    private function parseIndexes(TokenList $tokenList): ?array
    {
        $indexes = null;
        if ($tokenList->hasAnyKeyword(Keyword::INDEX, Keyword::KEY)) {
            $tokenList->expectSymbol('(');
            $indexes = [];
            do {
                $indexes[] = $tokenList->expectName(EntityType::INDEX);
            } while ($tokenList->hasSymbol(','));
            $tokenList->expectSymbol(')');
        }

        return $indexes;
    }

    /**
     * @return non-empty-list<string>|true|null
     */
    private function parsePartitions(TokenList $tokenList)
    {
        if (!$tokenList->hasKeyword(Keyword::PARTITION)) {
            return null;
        }

        $tokenList->expectSymbol('(');
        if ($tokenList->hasKeyword(Keyword::ALL)) {
            $partitions = true;
        } else {
            $partitions = [];
            do {
                $partitions[] = $tokenList->expectName(EntityType::PARTITION);
            } while ($tokenList->hasSymbol(','));
        }
        $tokenList->expectSymbol(')');

        return $partitions;
    }

}
