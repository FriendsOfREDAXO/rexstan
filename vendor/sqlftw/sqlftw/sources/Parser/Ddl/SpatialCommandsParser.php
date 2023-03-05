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
use SqlFtw\Sql\Ddl\Spatial\CreateSpatialReferenceSystemCommand;
use SqlFtw\Sql\Ddl\Spatial\DropSpatialReferenceSystemCommand;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Keyword;
use function strlen;

class SpatialCommandsParser
{

    /**
     * CREATE OR REPLACE SPATIAL REFERENCE SYSTEM
     *     srid srs_attribute ...
     *
     * CREATE SPATIAL REFERENCE SYSTEM
     *     [IF NOT EXISTS]
     *     srid srs_attribute ...
     *
     * srs_attribute: {
     *     NAME 'srs_name'
     *   | DEFINITION 'definition'
     *   | ORGANIZATION 'org_name' IDENTIFIED BY org_id
     *   | DESCRIPTION 'description'
     * }
     *
     * srid, org_id: 32-bit unsigned integer
     */
    public function parseCreateSpatialReferenceSystem(TokenList $tokenList): CreateSpatialReferenceSystemCommand
    {
        $tokenList->expectKeyword(Keyword::CREATE);
        $orReplace = $tokenList->hasKeywords(Keyword::OR, Keyword::REPLACE);
        $tokenList->expectKeywords(Keyword::SPATIAL, Keyword::REFERENCE, Keyword::SYSTEM);
        $ifNotExists = false;
        if ($orReplace === false) {
            $ifNotExists = $tokenList->hasKeywords(Keyword::IF, Keyword::NOT, Keyword::EXISTS);
        }

        $srid = (int) $tokenList->expectUnsignedInt();

        $name = $definition = $organization = $identifiedBy = $description = null;
        $keywords = [Keyword::NAME, Keyword::DEFINITION, Keyword::ORGANIZATION, Keyword::DESCRIPTION];
        while (($keyword = $tokenList->getAnyKeyword(...$keywords)) !== null) {
            switch ($keyword) {
                case Keyword::NAME:
                    $name = $tokenList->expectString();
                    $tokenList->validateName(EntityType::SRS, $name);
                    break;
                case Keyword::DEFINITION:
                    $definition = $tokenList->expectString();
                    if (strlen($definition) > 4096) {
                        throw new ParserException('SRS definition must be at most 4096 characters long.', $tokenList);
                    }
                    break;
                case Keyword::ORGANIZATION:
                    $organization = $tokenList->expectString();
                    $tokenList->validateName(EntityType::SRS, $organization);
                    if ($tokenList->hasKeywords(Keyword::IDENTIFIED, Keyword::BY)) {
                        $identifiedBy = (int) $tokenList->expectUnsignedInt();
                    }
                    break;
                case Keyword::DESCRIPTION:
                    $description = $tokenList->expectString();
                    if (strlen($description) > 2048) {
                        throw new ParserException('SRS description must be at most 2048 characters long.', $tokenList);
                    }
                    break;
            }
        }

        if ($name === null || $definition === null) {
            $tokenList->missingAnyKeyword(Keyword::NAME, Keyword::DEFINITION);
        }

        return new CreateSpatialReferenceSystemCommand($srid, $name, $definition, $organization, $identifiedBy, $description, $orReplace, $ifNotExists);
    }

    /**
     * DROP SPATIAL REFERENCE SYSTEM [IF EXISTS] srid
     */
    public function parseDropSpatialReferenceSystem(TokenList $tokenList): DropSpatialReferenceSystemCommand
    {
        $tokenList->expectKeywords(Keyword::DROP, Keyword::SPATIAL, Keyword::REFERENCE, Keyword::SYSTEM);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);

        $srid = (int) $tokenList->expectUnsignedInt();

        return new DropSpatialReferenceSystemCommand($srid, $ifExists);
    }

}
