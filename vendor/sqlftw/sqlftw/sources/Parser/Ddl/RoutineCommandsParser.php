<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Ddl;

use SqlFtw\Parser\ExpressionParser;
use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\RoutineBodyParser;
use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Collation;
use SqlFtw\Sql\Ddl\Routine\AlterFunctionCommand;
use SqlFtw\Sql\Ddl\Routine\AlterProcedureCommand;
use SqlFtw\Sql\Ddl\Routine\CreateFunctionCommand;
use SqlFtw\Sql\Ddl\Routine\CreateProcedureCommand;
use SqlFtw\Sql\Ddl\Routine\DropFunctionCommand;
use SqlFtw\Sql\Ddl\Routine\DropProcedureCommand;
use SqlFtw\Sql\Ddl\Routine\InOutParamFlag;
use SqlFtw\Sql\Ddl\Routine\ProcedureParam;
use SqlFtw\Sql\Ddl\Routine\RoutineSideEffects;
use SqlFtw\Sql\Ddl\SqlSecurity;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\Routine\RoutineType;

class RoutineCommandsParser
{

    private ExpressionParser $expressionParser;

    private RoutineBodyParser $routineBodyParser;

    public function __construct(ExpressionParser $expressionParser, RoutineBodyParser $routineBodyParser)
    {
        $this->expressionParser = $expressionParser;
        $this->routineBodyParser = $routineBodyParser;
    }

    /**
     * ALTER FUNCTION func_name [characteristic ...]
     *
     * characteristic:
     *     COMMENT 'string'
     *   | LANGUAGE SQL
     *   | { CONTAINS SQL | NO SQL | READS SQL DATA | MODIFIES SQL DATA }
     *   | SQL SECURITY { DEFINER | INVOKER }
     */
    public function parseAlterFunction(TokenList $tokenList): AlterFunctionCommand
    {
        $tokenList->expectKeywords(Keyword::ALTER, Keyword::FUNCTION);
        $name = $tokenList->expectObjectIdentifier();

        [$comment, $language, $sideEffects, $sqlSecurity] = $this->parseRoutineCharacteristics($tokenList, false);

        return new AlterFunctionCommand($name, $sqlSecurity, $sideEffects, $comment, $language);
    }

    /**
     * ALTER PROCEDURE proc_name [characteristic ...]
     *
     * characteristic:
     *     COMMENT 'string'
     *   | LANGUAGE SQL
     *   | { CONTAINS SQL | NO SQL | READS SQL DATA | MODIFIES SQL DATA }
     *   | SQL SECURITY { DEFINER | INVOKER }
     */
    public function parseAlterProcedure(TokenList $tokenList): AlterProcedureCommand
    {
        $tokenList->expectKeywords(Keyword::ALTER, Keyword::PROCEDURE);
        $name = $tokenList->expectObjectIdentifier();

        [$comment, $language, $sideEffects, $sqlSecurity] = $this->parseRoutineCharacteristics($tokenList, false);

        return new AlterProcedureCommand($name, $sqlSecurity, $sideEffects, $comment, $language);
    }

    /**
     * @return array{string|null, string|null, RoutineSideEffects|null, SqlSecurity|null, bool|null}
     */
    private function parseRoutineCharacteristics(TokenList $tokenList, bool $procedure): array
    {
        $comment = $language = $sideEffects = $sqlSecurity = $deterministic = null;

        $keywords = [Keyword::COMMENT, Keyword::LANGUAGE, Keyword::CONTAINS, Keyword::NO, Keyword::READS, Keyword::MODIFIES, Keyword::SQL];
        if ($procedure) {
            $keywords[] = Keyword::NOT;
            $keywords[] = Keyword::DETERMINISTIC;
        }

        while ($keyword = $tokenList->getAnyKeyword(...$keywords)) {
            if ($keyword === Keyword::COMMENT) {
                $comment = $tokenList->expectString();
            } elseif ($keyword === Keyword::LANGUAGE) {
                $tokenList->expectKeyword(Keyword::SQL);
                $language = Keyword::SQL;
            } elseif ($keyword === Keyword::CONTAINS) {
                $tokenList->expectKeyword(Keyword::SQL);
                $sideEffects = new RoutineSideEffects(RoutineSideEffects::CONTAINS_SQL);
            } elseif ($keyword === Keyword::NO) {
                $tokenList->expectKeyword(Keyword::SQL);
                $sideEffects = new RoutineSideEffects(RoutineSideEffects::NO_SQL);
            } elseif ($keyword === Keyword::READS) {
                $tokenList->expectKeywords(Keyword::SQL, Keyword::DATA);
                $sideEffects = new RoutineSideEffects(RoutineSideEffects::READS_SQL_DATA);
            } elseif ($keyword === Keyword::MODIFIES) {
                $tokenList->expectKeywords(Keyword::SQL, Keyword::DATA);
                $sideEffects = new RoutineSideEffects(RoutineSideEffects::MODIFIES_SQL_DATA);
            } elseif ($keyword === Keyword::SQL) {
                $tokenList->expectKeyword(Keyword::SECURITY);
                $sqlSecurity = $tokenList->expectKeywordEnum(SqlSecurity::class);
            } elseif ($keyword === Keyword::NOT) {
                $tokenList->expectKeyword(Keyword::DETERMINISTIC);
                $deterministic = false;
            } elseif ($keyword === Keyword::DETERMINISTIC) {
                $deterministic = true;
            }
        }

        return [$comment, $language, $sideEffects, $sqlSecurity, $deterministic];
    }

    /**
     * CREATE
     *   [DEFINER = { user | CURRENT_USER }]
     *   FUNCTION [IF NOT EXISTS] sp_name ([func_parameter[, ...]])
     *   RETURNS type
     *   [characteristic ...] routine_body
     *
     * func_parameter:
     *   param_name type
     *
     * type:
     *   Any valid MySQL data type
     *
     * characteristic:
     *     COMMENT 'string'
     *   | LANGUAGE SQL
     *   | [NOT] DETERMINISTIC
     *   | { CONTAINS SQL | NO SQL | READS SQL DATA | MODIFIES SQL DATA }
     *   | SQL SECURITY { DEFINER | INVOKER }
     *
     * routine_body:
     *   Valid SQL routine statement
     */
    public function parseCreateFunction(TokenList $tokenList): CreateFunctionCommand
    {
        $tokenList->expectKeyword(Keyword::CREATE);
        $definer = null;
        if ($tokenList->hasKeyword(Keyword::DEFINER)) {
            $tokenList->expectOperator(Operator::EQUAL);
            $definer = $this->expressionParser->parseUserExpression($tokenList);
        }
        $tokenList->expectKeyword(Keyword::FUNCTION);

        $ifNotExists = $tokenList->using(null, 80000) && $tokenList->hasKeywords(Keyword::IF, Keyword::NOT, Keyword::EXISTS);

        $name = $tokenList->expectObjectIdentifier();

        $params = [];
        $tokenList->expectSymbol('(');
        if (!$tokenList->hasSymbol(')')) {
            do {
                $param = $tokenList->expectName(EntityType::PARAMETER);
                if (isset($params[$param])) {
                    throw new ParserException('Duplicate parameter name.', $tokenList);
                }
                $type = $this->expressionParser->parseColumnType($tokenList);
                $charset = $type->getCharset();
                $collation = $type->getCollation();
                if ($charset === null && ($collation !== null && !$collation->equalsValue(Collation::BINARY))) {
                    throw new ParserException('Character set is required for IN parameter with collation.', $tokenList);
                }
                $params[$param] = $type;
            } while ($tokenList->hasSymbol(','));
            $tokenList->expectSymbol(')');
        }

        $tokenList->expectKeyword(Keyword::RETURNS);
        $returnType = $this->expressionParser->parseColumnType($tokenList);
        $charset = $returnType->getCharset();
        $collation = $returnType->getCollation();
        if ($charset === null && $collation !== null) {
            throw new ParserException('Character set is required for return type with collation.', $tokenList);
        }

        [$comment, $language, $sideEffects, $sqlSecurity, $deterministic] = $this->parseRoutineCharacteristics($tokenList, true);

        $body = $this->routineBodyParser->parseBody($tokenList, RoutineType::FUNCTION);

        return new CreateFunctionCommand($name, $body, $params, $returnType, $definer, $deterministic, $sqlSecurity, $sideEffects, $comment, $language, $ifNotExists);
    }

    /**
     * CREATE
     *     [DEFINER = { user | CURRENT_USER }]
     *     PROCEDURE [IF NOT EXISTS] sp_name ([proc_parameter[, ...]])
     *     [characteristic ...] routine_body
     *
     * proc_parameter:
     *     [ IN | OUT | INOUT ] param_name type
     *
     * type:
     *     Any valid MySQL data type
     *
     * characteristic:
     *     COMMENT 'string'
     *   | LANGUAGE SQL
     *   | [NOT] DETERMINISTIC
     *   | { CONTAINS SQL | NO SQL | READS SQL DATA | MODIFIES SQL DATA }
     *   | SQL SECURITY { DEFINER | INVOKER }
     *
     * routine_body:
     *     Valid SQL routine statement
     */
    public function parseCreateProcedure(TokenList $tokenList): CreateProcedureCommand
    {
        $tokenList->expectKeyword(Keyword::CREATE);
        $definer = null;
        if ($tokenList->hasKeyword(Keyword::DEFINER)) {
            $tokenList->expectOperator(Operator::EQUAL);
            $definer = $this->expressionParser->parseUserExpression($tokenList);
        }
        $tokenList->expectKeyword(Keyword::PROCEDURE);

        $ifNotExists = $tokenList->using(null, 80000) && $tokenList->hasKeywords(Keyword::IF, Keyword::NOT, Keyword::EXISTS);

        $name = $tokenList->expectObjectIdentifier();

        $params = [];
        $tokenList->expectSymbol('(');
        if (!$tokenList->hasSymbol(')')) {
            do {
                $inOut = $tokenList->getKeywordEnum(InOutParamFlag::class);
                $param = $tokenList->expectName(EntityType::PARAMETER);
                if (isset($params[$param])) {
                    throw new ParserException('Duplicate parameter name.', $tokenList);
                }
                $type = $this->expressionParser->parseColumnType($tokenList);
                $charset = $type->getCharset();
                $collation = $type->getCollation();
                if ($inOut !== null && $inOut->equalsAnyValue(InOutParamFlag::IN, InOutParamFlag::INOUT) && $charset === null && $collation !== null) {
                    throw new ParserException('Character set is required for IN parameter with collation.', $tokenList);
                }
                $params[$param] = new ProcedureParam($param, $type, $inOut);
            } while ($tokenList->hasSymbol(','));
            $tokenList->expectSymbol(')');
        }

        [$comment, $language, $sideEffects, $sqlSecurity, $deterministic] = $this->parseRoutineCharacteristics($tokenList, true);

        $body = $this->routineBodyParser->parseBody($tokenList, RoutineType::PROCEDURE);

        return new CreateProcedureCommand($name, $body, $params, $definer, $deterministic, $sqlSecurity, $sideEffects, $comment, $language, $ifNotExists);
    }

    /**
     * DROP FUNCTION [IF EXISTS] sp_name
     */
    public function parseDropFunction(TokenList $tokenList): DropFunctionCommand
    {
        $tokenList->expectKeywords(Keyword::DROP, Keyword::FUNCTION);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);
        $name = $tokenList->expectObjectIdentifier();

        return new DropFunctionCommand($name, $ifExists);
    }

    /**
     * DROP PROCEDURE [IF EXISTS] sp_name
     */
    public function parseDropProcedure(TokenList $tokenList): DropProcedureCommand
    {
        $tokenList->expectKeywords(Keyword::DROP, Keyword::PROCEDURE);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);
        $name = $tokenList->expectObjectIdentifier();

        return new DropProcedureCommand($name, $ifExists);
    }

}
