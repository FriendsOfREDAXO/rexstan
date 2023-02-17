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
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Dml\DuplicateOption;
use SqlFtw\Sql\Dml\Load\LoadDataCommand;
use SqlFtw\Sql\Dml\Load\LoadPriority;
use SqlFtw\Sql\Dml\Load\LoadXmlCommand;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\Keyword;

class LoadCommandsParser
{

    private ExpressionParser $expressionParser;

    public function __construct(ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }

    /**
     * LOAD DATA [LOW_PRIORITY | CONCURRENT] [LOCAL] INFILE 'file_name'
     *     [REPLACE | IGNORE]
     *     INTO TABLE tbl_name
     *     [PARTITION (partition_name, ...)]
     *     [CHARACTER SET charset_name]
     *     [{FIELDS | COLUMNS}
     *         [TERMINATED BY 'string']
     *         [[OPTIONALLY] ENCLOSED BY 'char']
     *         [ESCAPED BY 'char']
     *     ]
     *     [LINES
     *         [STARTING BY 'string']
     *         [TERMINATED BY 'string']
     *     ]
     *     [IGNORE number {LINES | ROWS}]
     *     [(col_name_or_user_var, ...)]
     *     [SET col_name = expr, ...]
     */
    public function parseLoadData(TokenList $tokenList): LoadDataCommand
    {
        $tokenList->expectKeywords(Keyword::LOAD, Keyword::DATA);

        [$priority, $local, $file, $duplicateOption, $table, $partitions, $charset] = $this->parseOptions($tokenList, true);

        $format = $this->expressionParser->parseFileFormat($tokenList);

        [$ignoreRows, $fields, $setters] = $this->parseRowsAndFields($tokenList);

        return new LoadDataCommand($file, $table, $format, $charset, $fields, $setters, $ignoreRows, $priority, $local, $duplicateOption, $partitions);
    }

    /**
     * LOAD XML [LOW_PRIORITY | CONCURRENT] [LOCAL] INFILE 'file_name'
     *     [REPLACE | IGNORE]
     *     INTO TABLE [db_name.]tbl_name
     *     [CHARACTER SET charset_name]
     *     [ROWS IDENTIFIED BY '<tagname>']
     *     [IGNORE number {LINES | ROWS}]
     *     [(field_name_or_user_var, ...)]
     *     [SET col_name = expr, ...]
     */
    public function parseLoadXml(TokenList $tokenList): LoadXmlCommand
    {
        $tokenList->expectKeywords(Keyword::LOAD, Keyword::XML);

        [$priority, $local, $file, $duplicateOption, $table, , $charset] = $this->parseOptions($tokenList, false);

        $rowsTag = null;
        if ($tokenList->hasKeywords(Keyword::ROWS, Keyword::IDENTIFIED, Keyword::BY)) {
            $rowsTag = $tokenList->expectString();
        }

        [$ignoreRows, $fields, $setters] = $this->parseRowsAndFields($tokenList);

        return new LoadXmlCommand($file, $table, $rowsTag, $charset, $fields, $setters, $ignoreRows, $priority, $local, $duplicateOption);
    }

    /**
     * @return array{LoadPriority|null, bool, string, DuplicateOption|null, ObjectIdentifier, non-empty-list<string>|null, Charset|null}
     */
    private function parseOptions(TokenList $tokenList, bool $parsePartitions): array
    {
        $priority = $tokenList->getKeywordEnum(LoadPriority::class);
        $local = $tokenList->hasKeyword(Keyword::LOCAL);

        $tokenList->expectKeyword(Keyword::INFILE);
        $file = $tokenList->expectString();

        $duplicateOption = $tokenList->getKeywordEnum(DuplicateOption::class);

        $tokenList->expectKeywords(Keyword::INTO, Keyword::TABLE);
        $table = $tokenList->expectObjectIdentifier();

        $partitions = null;
        if ($parsePartitions && $tokenList->hasKeyword(Keyword::PARTITION)) {
            $tokenList->expectSymbol('(');
            $partitions = [];
            do {
                $partitions[] = $tokenList->expectName(EntityType::PARTITION);
            } while ($tokenList->hasSymbol(','));
            $tokenList->expectSymbol(')');
        }

        $charset = null;
        if ($tokenList->hasKeywords(Keyword::CHARACTER, Keyword::SET) || $tokenList->hasKeyword(Keyword::CHARSET)) {
            $charset = $tokenList->expectCharsetName();
        }

        return [$priority, $local, $file, $duplicateOption, $table, $partitions, $charset];
    }

    /**
     * @return array{int|null, non-empty-list<string>|null, non-empty-array<string, RootNode>|null}
     */
    private function parseRowsAndFields(TokenList $tokenList): array
    {
        $ignoreRows = null;
        if ($tokenList->hasKeyword(Keyword::IGNORE)) {
            $ignoreRows = (int) $tokenList->expectUnsignedInt();
            $tokenList->expectAnyKeyword(Keyword::LINES, Keyword::ROWS);
        }

        $fields = null;
        if ($tokenList->hasSymbol('(')) {
            /** @var non-empty-list<string> $fields */
            $fields = [];
            if (!$tokenList->hasSymbol(')')) {
                do {
                    $fields[] = $tokenList->expectName(EntityType::COLUMN);
                } while ($tokenList->hasSymbol(','));
                $tokenList->expectSymbol(')');
            }
        }

        $setters = null;
        if ($tokenList->hasKeyword(Keyword::SET)) {
            $setters = [];
            do {
                $field = $tokenList->expectName(EntityType::COLUMN);
                $tokenList->expectAnyOperator(Operator::EQUAL, Operator::ASSIGN);
                $expression = $this->expressionParser->parseExpression($tokenList);
                $setters[$field] = $expression;
            } while ($tokenList->hasSymbol(','));
        }

        return [$ignoreRows, $fields, $setters];
    }

}
