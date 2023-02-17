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
use SqlFtw\Parser\TokenList;
use SqlFtw\Parser\TokenType;
use SqlFtw\Sql\Dml\Delete\DeleteCommand;
use SqlFtw\Sql\Dml\WithClause;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\QualifiedName;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\Keyword;

class DeleteCommandParser
{

    private ExpressionParser $expressionParser;

    private TableReferenceParser $tableReferenceParser;

    private OptimizerHintParser $optimizerHintParser;

    public function __construct(
        ExpressionParser $expressionParser,
        TableReferenceParser $tableReferenceParser,
        OptimizerHintParser $optimizerHintParser
    )
    {
        $this->expressionParser = $expressionParser;
        $this->tableReferenceParser = $tableReferenceParser;
        $this->optimizerHintParser = $optimizerHintParser;
    }

    /**
     * DELETE [LOW_PRIORITY] [QUICK] [IGNORE]
     *    FROM tbl_name
     *    [PARTITION (partition_name, ...)]
     *    [WHERE where_condition]
     *    [ORDER BY ...]
     *    [LIMIT row_count]
     *
     * DELETE [LOW_PRIORITY] [QUICK] [IGNORE]
     *     tbl_name[.*] [, tbl_name[.*]] ...
     *     FROM table_references
     *     [WHERE where_condition]
     *
     * DELETE [LOW_PRIORITY] [QUICK] [IGNORE]
     *     FROM tbl_name[.*] [, tbl_name[.*]] ...
     *     USING table_references
     *     [WHERE where_condition]
     */
    public function parseDelete(TokenList $tokenList, ?WithClause $with = null): DeleteCommand
    {
        $tokenList->expectKeyword(Keyword::DELETE);

        $optimizerHints = null;
        if ($tokenList->has(TokenType::OPTIMIZER_HINT_START)) {
            $optimizerHints = $this->optimizerHintParser->parseHints($tokenList->rewind(-1));
        }

        $lowPriority = $tokenList->hasKeywords(Keyword::LOW_PRIORITY);
        $quick = $tokenList->hasKeyword(Keyword::QUICK);
        $ignore = $tokenList->hasKeyword(Keyword::IGNORE);

        $references = $partitions = null;
        if ($tokenList->hasKeyword(Keyword::FROM)) {
            $tables = $this->parseTablesList($tokenList);
            if ($tokenList->hasKeyword(Keyword::USING)) {
                $references = $this->tableReferenceParser->parseTableReferences($tokenList);
            } elseif ($tokenList->hasKeyword(Keyword::PARTITION)) {
                $tokenList->expectSymbol('(');
                $partitions = [];
                do {
                    $partitions[] = $tokenList->expectName(EntityType::PARTITION);
                } while ($tokenList->hasSymbol(','));
                $tokenList->expectSymbol(')');
            }
        } else {
            $tables = $this->parseTablesList($tokenList);
            $tokenList->expectKeyword(Keyword::FROM);
            $references = $this->tableReferenceParser->parseTableReferences($tokenList);
        }

        $where = null;
        if ($tokenList->hasKeyword(Keyword::WHERE)) {
            $where = $this->expressionParser->parseAssignExpression($tokenList);
        }

        $orderBy = $limit = null;
        if ($references === null) {
            if ($tokenList->hasKeywords(Keyword::ORDER, Keyword::BY)) {
                $orderBy = $this->expressionParser->parseOrderBy($tokenList);
            }
            if ($tokenList->hasKeyword(Keyword::LIMIT)) {
                $limit = $this->expressionParser->parseLimitOrOffsetValue($tokenList);
            }
        }

        return new DeleteCommand($tables, $where, $with, $orderBy, $limit, $references, $partitions, $lowPriority, $quick, $ignore, $optimizerHints);
    }

    /**
     * @return non-empty-list<array{ObjectIdentifier, string|null}>
     */
    private function parseTablesList(TokenList $tokenList): array
    {
        $tables = [];
        do {
            $table = $tokenList->expectObjectIdentifier();
            // ignore .*
            if ($tokenList->hasSymbol('.')) {
                $tokenList->expectOperator(Operator::MULTIPLY);
            }
            if ($table instanceof QualifiedName && $table->getName() === '*') {
                $table = new SimpleName($table->getSchema());
            }
            $alias = $this->expressionParser->parseAlias($tokenList);

            $tables[] = [$table, $alias];
        } while ($tokenList->hasSymbol(','));

        return $tables;
    }

}
