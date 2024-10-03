<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dml;

use SqlFtw\Parser\InvalidCommand;
use SqlFtw\Parser\Parser;
use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Parser\TokenType;
use SqlFtw\Platform\Platform;
use SqlFtw\Sql\Ddl\Routine\StoredProcedureCommand;
use SqlFtw\Sql\Dml\Prepared\DeallocatePrepareCommand;
use SqlFtw\Sql\Dml\Prepared\ExecuteCommand;
use SqlFtw\Sql\Dml\Prepared\PrepareCommand;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\UserVariable;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\Routine\RoutineType;
use function count;
use function get_class;
use function in_array;
use function iterator_to_array;

class PreparedCommandsParser
{

    private Platform $platform;

    private Parser $parser;

    public function __construct(Platform $platform, Parser $parser)
    {
        $this->platform = $platform;
        $this->parser = $parser;
    }

    /**
     * {DEALLOCATE | DROP} PREPARE stmt_name
     */
    public function parseDeallocatePrepare(TokenList $tokenList): DeallocatePrepareCommand
    {
        $tokenList->expectAnyKeyword(Keyword::DEALLOCATE, Keyword::DROP);
        $tokenList->expectKeyword(Keyword::PREPARE);
        $name = $tokenList->expectName(EntityType::PREPARED_STATEMENT);

        return new DeallocatePrepareCommand($name);
    }

    /**
     * EXECUTE stmt_name
     *     [USING @var_name [, @var_name] ...]
     */
    public function parseExecute(TokenList $tokenList): ExecuteCommand
    {
        $tokenList->expectKeyword(Keyword::EXECUTE);
        $name = $tokenList->expectName(EntityType::PREPARED_STATEMENT);
        $variables = null;
        if ($tokenList->hasKeyword(Keyword::USING)) {
            $variables = [];
            do {
                $variable = $tokenList->expect(TokenType::AT_VARIABLE)->value;
                $variables[] = $variable;
            } while ($tokenList->hasSymbol(','));
        }

        return new ExecuteCommand($name, $variables);
    }

    /**
     * PREPARE stmt_name FROM preparable_stmt
     */
    public function parsePrepare(TokenList $tokenList): PrepareCommand
    {
        $tokenList->expectKeyword(Keyword::PREPARE);
        $name = $tokenList->expectName(EntityType::PREPARED_STATEMENT);
        $tokenList->expectKeyword(Keyword::FROM);

        $variable = $tokenList->get(TokenType::AT_VARIABLE);
        if ($variable !== null) {
            $statement = new UserVariable($variable->value);
        } else {
            $sql = $tokenList->expectString();
            $statements = iterator_to_array($this->parser->parse($sql, true));
            if (count($statements) > 1) {
                throw new ParserException('Multiple statements in PREPARE.', $tokenList);
            }
            $statement = $statements[0];
            if ($statement instanceof InvalidCommand) {
                throw new ParserException('Invalid statement in PREPARE.', $tokenList, $statement->getException());
            }
            $class = get_class($statement);
            if ($statement instanceof StoredProcedureCommand && $tokenList->inRoutine() === RoutineType::PROCEDURE) {
                // ok
            } elseif (!in_array($class, $this->platform->getPreparableCommands(), true)) {
                throw new ParserException('Non-preparable statement in PREPARE: ' . $class, $tokenList);
            }
        }

        return new PrepareCommand($name, $statement);
    }

}
