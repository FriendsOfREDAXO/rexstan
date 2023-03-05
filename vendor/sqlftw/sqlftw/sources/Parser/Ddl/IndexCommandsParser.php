<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Ddl;

use Dogma\Re;
use SqlFtw\Parser\ExpressionParser;
use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Ddl\Index\CreateIndexCommand;
use SqlFtw\Sql\Ddl\Index\DropIndexCommand;
use SqlFtw\Sql\Ddl\Table\Alter\AlterTableAlgorithm;
use SqlFtw\Sql\Ddl\Table\Alter\AlterTableLock;
use SqlFtw\Sql\Ddl\Table\Index\IndexAlgorithm;
use SqlFtw\Sql\Ddl\Table\Index\IndexDefinition;
use SqlFtw\Sql\Ddl\Table\Index\IndexOptions;
use SqlFtw\Sql\Ddl\Table\Index\IndexPart;
use SqlFtw\Sql\Ddl\Table\Index\IndexType;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\Order;
use SqlFtw\Sql\SqlMode;
use function count;
use function strlen;
use function strtoupper;

class IndexCommandsParser
{

    private ExpressionParser $expressionParser;

    public function __construct(ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }

    /**
     * https://dev.mysql.com/doc/refman/8.0/en/create-index.html
     * CREATE [UNIQUE|FULLTEXT|SPATIAL] INDEX index_name
     *     [index_type]
     *     ON tbl_name (key_part, ...)
     *     [index_option]
     *     [algorithm_option | lock_option] ...
     *
     * key_part: {
     *     col_name [(length)]
     *   | (expr) -- 8.0.13
     * } [ASC | DESC]
     *
     * index_option:
     *     KEY_BLOCK_SIZE [=] value
     *   | index_type
     *   | WITH PARSER parser_name
     *   | COMMENT 'string'
     *   | {VISIBLE | INVISIBLE}
     *   | ENGINE_ATTRIBUTE [=] 'string' -- 8.0.21
     *   | SECONDARY_ENGINE_ATTRIBUTE [=] 'string' -- 8.0.21
     *
     * index_type:
     *     {USING | TYPE} {BTREE | RTREE | HASH}
     *
     * algorithm_option:
     *     ALGORITHM [=] {DEFAULT|INPLACE|COPY}
     *
     * lock_option:
     *     LOCK [=] {DEFAULT|NONE|SHARED|EXCLUSIVE}
     */
    public function parseCreateIndex(TokenList $tokenList): CreateIndexCommand
    {
        $tokenList->expectKeyword(Keyword::CREATE);

        $index = $this->parseIndexDefinition($tokenList);

        $alterAlgorithm = $alterLock = null;
        while ($keyword = $tokenList->getAnyKeyword(Keyword::ALGORITHM, Keyword::LOCK)) {
            if ($keyword === Keyword::ALGORITHM) {
                $tokenList->passSymbol('=');
                if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                    $alterAlgorithm = new AlterTableAlgorithm(AlterTableAlgorithm::DEFAULT);
                } else {
                    $alterAlgorithm = $tokenList->expectNameOrStringEnum(AlterTableAlgorithm::class);
                }
            } elseif ($keyword === Keyword::LOCK) {
                $tokenList->passSymbol('=');
                if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                    $alterLock = new AlterTableLock(AlterTableLock::DEFAULT);
                } else {
                    $alterLock = $tokenList->expectNameOrStringEnum(AlterTableLock::class);
                }
            }
        }

        return new CreateIndexCommand($index, $alterAlgorithm, $alterLock);
    }

    public function parseIndexDefinition(TokenList $tokenList, bool $inTable = false): IndexDefinition
    {
        $keyword = $tokenList->getAnyKeyword(Keyword::UNIQUE, Keyword::FULLTEXT, Keyword::SPATIAL);
        if ($keyword === Keyword::UNIQUE) {
            $type = new IndexType($keyword . ' INDEX');
        } elseif ($keyword !== null) {
            $type = new IndexType($keyword . ' INDEX');
        } else {
            $type = new IndexType(IndexType::INDEX);
        }
        $isFulltext = $type->equalsValue(IndexType::FULLTEXT);
        $isSpatial = $type->equalsValue(IndexType::SPATIAL);

        if ($inTable) {
            $tokenList->getAnyKeyword(Keyword::INDEX, Keyword::KEY);
            $name = $tokenList->getNonReservedName(EntityType::INDEX);
        } else {
            $tokenList->expectAnyKeyword(Keyword::INDEX, Keyword::KEY);
            $name = $tokenList->expectName(EntityType::INDEX);
        }

        if ($name !== null && strtoupper($name) === Keyword::PRIMARY) {
            throw new ParserException('Invalid index name.', $tokenList);
        }

        $algorithm = null;
        $canChangeAlgorithm = !$isFulltext && !$isSpatial;
        if ($canChangeAlgorithm) {
            if ($tokenList->hasAnyKeyword(Keyword::USING, Keyword::TYPE)) {
                $algorithm = $tokenList->expectKeywordEnum(IndexAlgorithm::class);
            }
        }

        $table = null;
        if (!$inTable) {
            $tokenList->expectKeyword(Keyword::ON);
            $table = $tokenList->expectObjectIdentifier();
        }

        $parts = $this->parseIndexParts($tokenList);
        if ($isSpatial && count($parts) > 1) {
            throw new ParserException('Spatial index can only have one part.', $tokenList);
        } elseif (count($parts) > 16) {
            throw new ParserException('Index cannot have more than 16 parts.', $tokenList);
        }

        $keyBlockSize = $withParser = $mergeThreshold = $comment = $visible = $engineAttribute = $secondaryEngineAttribute = null;
        $keywords = [
            Keyword::KEY_BLOCK_SIZE, Keyword::WITH, Keyword::COMMENT, Keyword::VISIBLE,
            Keyword::INVISIBLE, Keyword::ENGINE_ATTRIBUTE, Keyword::SECONDARY_ENGINE_ATTRIBUTE,
        ];
        if ($canChangeAlgorithm) {
            $keywords[] = Keyword::USING;
            $keywords[] = Keyword::TYPE;
        }
        while ($keyword = $tokenList->getAnyKeyword(...$keywords)) {
            if ($keyword === Keyword::USING || $keyword === Keyword::TYPE) {
                $algorithm = $tokenList->expectKeywordEnum(IndexAlgorithm::class);
            } elseif ($keyword === Keyword::KEY_BLOCK_SIZE) {
                $tokenList->passSymbol('=');
                $keyBlockSize = (int) $tokenList->expectUnsignedInt();
            } elseif ($keyword === Keyword::WITH) {
                $tokenList->expectKeyword(Keyword::PARSER);
                $withParser = $tokenList->expectName(null);
            } elseif ($keyword === Keyword::COMMENT) {
                $commentString = $tokenList->expectString();
                $limit = $tokenList->getSession()->getPlatform()->getMaxLengths()[EntityType::INDEX_COMMENT];
                if (strlen($commentString) > $limit && $tokenList->getSession()->getMode()->containsAny(SqlMode::STRICT_ALL_TABLES)) {
                    throw new ParserException("Index comment length exceeds limit of {$limit} bytes.", $tokenList);
                }
                // parse "COMMENT 'MERGE_THRESHOLD=40';"
                $match = Re::match($commentString, '/^MERGE_THRESHOLD=([0-9]+)$/');
                if ($match !== null) {
                    $mergeThreshold = (int) $match[1];
                } else {
                    $comment = $commentString;
                }
            } elseif ($keyword === Keyword::VISIBLE) {
                $visible = true;
            } elseif ($keyword === Keyword::INVISIBLE) {
                $visible = false;
            } elseif ($keyword === Keyword::ENGINE_ATTRIBUTE) {
                $tokenList->check(Keyword::ENGINE_ATTRIBUTE, 80021);
                $tokenList->passSymbol('=');
                $engineAttribute = $tokenList->expectString();
            } elseif ($keyword === Keyword::SECONDARY_ENGINE_ATTRIBUTE) {
                $tokenList->check(Keyword::SECONDARY_ENGINE_ATTRIBUTE, 80021);
                $tokenList->passSymbol('=');
                $secondaryEngineAttribute = $tokenList->expectString();
            }
        }

        $options = null;
        if ($keyBlockSize !== null
            || $withParser !== null
            || $mergeThreshold !== null
            || $comment !== null
            || $visible !== null
            || $engineAttribute !== null
            || $secondaryEngineAttribute !== null
        ) {
            $options = new IndexOptions($keyBlockSize, $withParser, $mergeThreshold, $comment, $visible, $engineAttribute, $secondaryEngineAttribute);
        }

        return new IndexDefinition($name, $type, $parts, $algorithm, $options, $table);
    }

    /**
     * @return non-empty-list<IndexPart>
     */
    private function parseIndexParts(TokenList $tokenList): array
    {
        $tokenList->expectSymbol('(');
        $parts = [];
        do {
            if ($tokenList->hasSymbol('(')) {
                $tokenList->check('functional indexes', 80013);
                $expression = $this->expressionParser->parseExpression($tokenList);
                $tokenList->expectSymbol(')');

                $order = $tokenList->getKeywordEnum(Order::class);

                $parts[] = new IndexPart($expression, null, $order);
            } else {
                $part = $tokenList->expectName(EntityType::INDEX);
                $length = null;
                if ($tokenList->hasSymbol('(')) {
                    $length = (int) $tokenList->expectUnsignedInt();
                    if ($length === 0) {
                        throw new ParserException('Index prefix length must be positive.', $tokenList);
                    }
                    $tokenList->expectSymbol(')');
                }

                $order = $tokenList->getKeywordEnum(Order::class);
                $parts[] = new IndexPart($part, $length, $order);
            }
        } while ($tokenList->hasSymbol(','));
        $tokenList->expectSymbol(')');

        return $parts;
    }

    /**
     * DROP INDEX index_name ON tbl_name
     *     [algorithm_option | lock_option] ...
     *
     * algorithm_option:
     *     ALGORITHM [=] {DEFAULT|INPLACE|COPY}
     *
     * lock_option:
     *     LOCK [=] {DEFAULT|NONE|SHARED|EXCLUSIVE}
     */
    public function parseDropIndex(TokenList $tokenList): DropIndexCommand
    {
        $tokenList->expectKeywords(Keyword::DROP, Keyword::INDEX);
        $name = $tokenList->expectName(EntityType::INDEX);
        $tokenList->expectKeyword(Keyword::ON);
        $table = $tokenList->expectObjectIdentifier();
        $algorithm = null;
        if ($tokenList->hasKeyword(Keyword::ALGORITHM)) {
            $tokenList->passSymbol('=');
            if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                $algorithm = new AlterTableAlgorithm(AlterTableAlgorithm::DEFAULT);
            } else {
                $algorithm = $tokenList->expectNameOrStringEnum(AlterTableAlgorithm::class);
            }
        }
        $lock = null;
        if ($tokenList->hasKeyword(Keyword::LOCK)) {
            $tokenList->passSymbol('=');
            if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                $lock = new AlterTableLock(AlterTableLock::DEFAULT);
            } else {
                $lock = $tokenList->expectNameOrStringEnum(AlterTableLock::class);
            }
        }

        return new DropIndexCommand($name, $table, $algorithm, $lock);
    }

}
