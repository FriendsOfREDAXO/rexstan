<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Ddl;

use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Ddl\Schema\AlterSchemaCommand;
use SqlFtw\Sql\Ddl\Schema\CreateSchemaCommand;
use SqlFtw\Sql\Ddl\Schema\DropSchemaCommand;
use SqlFtw\Sql\Ddl\Schema\SchemaOptions;
use SqlFtw\Sql\Ddl\Table\Option\ThreeStateValue;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Keyword;
use function trim;

class SchemaCommandsParser
{

    /**
     * ALTER {DATABASE | SCHEMA} [db_name]
     *     alter_option ...
     *
     * alter_option:
     *     [DEFAULT] CHARACTER SET [=] charset_name
     *   | [DEFAULT] COLLATE [=] collation_name
     *   | [DEFAULT] ENCRYPTION [=] {'Y' | 'N'}
     *   | READ ONLY [=] {DEFAULT | 0 | 1}
     * }
     */
    public function parseAlterSchema(TokenList $tokenList): AlterSchemaCommand
    {
        $tokenList->expectKeyword(Keyword::ALTER);
        $tokenList->expectAnyKeyword(Keyword::DATABASE, Keyword::SCHEMA);
        $name = $tokenList->getNonReservedName(EntityType::SCHEMA);
        if ($name === '' || ($name !== null && trim($name) !== $name)) {
            throw new ParserException('Invalid schema name.', $tokenList);
        }

        $options = $this->parseOptions($tokenList);
        if ($options === null) {
            $tokenList->missingAnyKeyword(Keyword::DEFAULT, Keyword::CHARACTER, Keyword::CHAR, Keyword::CHARSET, Keyword::COLLATE, Keyword::ENCRYPTION, Keyword::READ);
        }

        return new AlterSchemaCommand($name, $options);
    }

    /**
     * CREATE {DATABASE | SCHEMA} [IF NOT EXISTS] db_name
     *     [create_option] ...
     *
     * create_option: {
     *     [DEFAULT] CHARACTER SET [=] charset_name
     *   | [DEFAULT] COLLATE [=] collation_name
     *   | [DEFAULT] ENCRYPTION [=] {'Y' | 'N'}
     *   | READ ONLY [=] {DEFAULT | 0 | 1}
     * }
     */
    public function parseCreateSchema(TokenList $tokenList): CreateSchemaCommand
    {
        $tokenList->expectKeyword(Keyword::CREATE);
        $tokenList->expectAnyKeyword(Keyword::DATABASE, Keyword::SCHEMA);
        $ifNotExists = $tokenList->hasKeywords(Keyword::IF, Keyword::NOT, Keyword::EXISTS);
        $name = $tokenList->expectName(EntityType::SCHEMA);
        if ($name === '' || trim($name) !== $name) {
            throw new ParserException('Invalid schema name.', $tokenList);
        }

        $options = $this->parseOptions($tokenList);

        return new CreateSchemaCommand($name, $options, $ifNotExists);
    }

    private function parseOptions(TokenList $tokenList): ?SchemaOptions
    {
        $charset = $collation = $encryption = $readOnly = null;
        $n = 0;
        while ($n < 2) {
            if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                $keyword = $tokenList->expectAnyKeyword(Keyword::CHARACTER, Keyword::CHAR, Keyword::CHARSET, Keyword::COLLATE, Keyword::ENCRYPTION);
            } else {
                $keyword = $tokenList->getAnyKeyword(Keyword::CHARACTER, Keyword::CHAR, Keyword::CHARSET, Keyword::COLLATE, Keyword::ENCRYPTION, Keyword::READ);
            }
            if ($keyword === null) {
                break;
            } elseif ($keyword === Keyword::CHARACTER || $keyword === Keyword::CHARSET || $keyword === Keyword::CHAR) {
                if ($keyword !== Keyword::CHARSET) {
                    $tokenList->expectKeyword(Keyword::SET);
                }
                $tokenList->passSymbol('=');
                if ($charset !== null) {
                    // charset can be specified twice (not represented in model)
                    $charset2 = $tokenList->expectCharsetName();
                    if ($charset !== $charset2) {
                        throw new ParserException('Charset declaration conflict.', $tokenList);
                    }
                } else {
                    $charset = $tokenList->expectCharsetName();
                }
            } elseif ($keyword === Keyword::COLLATE) {
                $tokenList->passSymbol('=');
                $collation = $tokenList->expectCollationName();
            } elseif ($keyword === Keyword::ENCRYPTION) {
                $tokenList->check('schema encryption', 80016);
                $tokenList->passSymbol('=');
                $encryption = $tokenList->expectBool();
            } else {
                $tokenList->check('schema read only', 80022);
                $tokenList->expectKeyword(Keyword::ONLY);
                $tokenList->passSymbol('=');
                if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                    $readOnly = new ThreeStateValue(ThreeStateValue::DEFAULT);
                } else {
                    $readOnly = new ThreeStateValue((string) (int) $tokenList->expectBool());
                }
            }
            $n++;
        }

        if ($charset !== null || $collation !== null || $encryption !== null || $readOnly !== null) {
            return new SchemaOptions($charset, $collation, $encryption, $readOnly);
        } else {
            return null;
        }
    }

    /**
     * DROP {DATABASE | SCHEMA} [IF EXISTS] db_name
     */
    public function parseDropSchema(TokenList $tokenList): DropSchemaCommand
    {
        $tokenList->expectKeyword(Keyword::DROP);
        $tokenList->expectAnyKeyword(Keyword::DATABASE, Keyword::SCHEMA);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);
        $schema = $tokenList->expectName(EntityType::SCHEMA);

        return new DropSchemaCommand($schema, $ifExists);
    }

}
