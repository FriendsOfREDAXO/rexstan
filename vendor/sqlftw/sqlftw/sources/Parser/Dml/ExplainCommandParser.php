<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dml;

use LogicException;
use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Command;
use SqlFtw\Sql\Dml\Utility\DescribeTableCommand;
use SqlFtw\Sql\Dml\Utility\ExplainForConnectionCommand;
use SqlFtw\Sql\Dml\Utility\ExplainStatementCommand;
use SqlFtw\Sql\Dml\Utility\ExplainType;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Keyword;
use function strtoupper;

class ExplainCommandParser
{

    private QueryParser $queryParser;

    private InsertCommandParser $insertCommandParser;

    private UpdateCommandParser $updateCommandParser;

    private DeleteCommandParser $deleteCommandParser;

    public function __construct(
        QueryParser $queryParser,
        InsertCommandParser $insertCommandParser,
        UpdateCommandParser $updateCommandParser,
        DeleteCommandParser $deleteCommandParser
    )
    {
        $this->queryParser = $queryParser;
        $this->insertCommandParser = $insertCommandParser;
        $this->updateCommandParser = $updateCommandParser;
        $this->deleteCommandParser = $deleteCommandParser;
    }

    /**
     * {EXPLAIN | DESCRIBE | DESC}
     *     tbl_name [col_name | wild]
     *
     * {EXPLAIN | DESCRIBE | DESC}
     *     [explain_type]
     *     {explainable_stmt | FOR CONNECTION connection_id}
     *
     * {EXPLAIN | DESCRIBE | DESC} ANALYZE [FORMAT = TREE] explainable_statement
     *
     * explain_type: {
     *     EXTENDED
     *   | PARTITIONS
     *   | FORMAT = format_name
     * }
     *
     * format_name: {
     *     TRADITIONAL
     *   | JSON
     *   | TREE
     * }
     *
     * explainable_stmt: {
     *     SELECT statement
     *   | DELETE statement
     *   | INSERT statement
     *   | REPLACE statement
     *   | UPDATE statement
     * }
     *
     * @return ExplainStatementCommand|ExplainForConnectionCommand|DescribeTableCommand
     */
    public function parseExplain(TokenList $tokenList): Command
    {
        $tokenList->expectAnyKeyword(Keyword::EXPLAIN, Keyword::DESCRIBE, Keyword::DESC);

        $type = $tokenList->getAnyKeyword(Keyword::EXTENDED, Keyword::PARTITIONS, Keyword::FORMAT, Keyword::ANALYZE);
        if ($type !== null) {
            if ($type === Keyword::ANALYZE) {
                if ($tokenList->hasKeyword(Keyword::FORMAT)) {
                    $tokenList->expectOperator(Operator::EQUAL);
                    $tokenList->expectAnyName('TREE');
                }
                $type = new ExplainType(ExplainType::ANALYZE);
            } elseif ($type === Keyword::FORMAT) {
                $tokenList->expectOperator(Operator::EQUAL);
                $format = strtoupper($tokenList->expectAnyName(Keyword::TRADITIONAL, Keyword::JSON, 'TREE'));
                $type = new ExplainType($type . '=' . $format);
            } else {
                $type = new ExplainType($type);
            }
        }

        $position = $tokenList->getPosition();
        $keywords = [Keyword::SELECT, Keyword::WITH, Keyword::TABLE, Keyword::INSERT, Keyword::UPDATE, Keyword::DELETE, Keyword::REPLACE, Keyword::FOR];
        if ($type === null || $type->equalsValue(ExplainType::ANALYZE)) {
            $keywords[] = Keyword::FOR;
        }
        $what = $tokenList->getAnyKeyword(...$keywords);
        if ($what === null) {
            $what = $tokenList->hasSymbol('(') ? '(' : null;
        }
        switch ($what) {
            case Keyword::WITH:
                $statement = $this->queryParser->parseWith($tokenList->rewind($position));
                break;
            case Keyword::SELECT:
            case '(':
                $statement = $this->queryParser->parseQuery($tokenList->rewind($position));
                break;
            case Keyword::TABLE:
                $statement = $this->queryParser->parseTable($tokenList->rewind($position));
                break;
            case Keyword::INSERT:
                $statement = $this->insertCommandParser->parseInsert($tokenList->rewind($position));
                break;
            case Keyword::UPDATE:
                $statement = $this->updateCommandParser->parseUpdate($tokenList->rewind($position));
                break;
            case Keyword::DELETE:
                $statement = $this->deleteCommandParser->parseDelete($tokenList->rewind($position));
                break;
            case Keyword::REPLACE:
                $statement = $this->insertCommandParser->parseReplace($tokenList->rewind($position));
                break;
            case Keyword::FOR:
                $tokenList->expectKeyword(Keyword::CONNECTION);
                $connectionId = (int) $tokenList->expectUnsignedInt();
                if ($type !== null && $type->equalsValue(ExplainType::ANALYZE)) {
                    throw new ParserException('EXPLAIN ANALYZE FOR CONNECTION is not supported.', $tokenList);
                }

                return new ExplainForConnectionCommand($connectionId, $type);
            case null:
                // DESCRIBE
                $table = $tokenList->expectObjectIdentifier();
                $column = $tokenList->getName(EntityType::COLUMN);
                if ($column === null) {
                    $column = $tokenList->getString();
                }

                return new DescribeTableCommand($table, $column);
            default:
                throw new LogicException('Unknown EXPLAIN command.');
        }

        return new ExplainStatementCommand($statement, $type);
    }

}
