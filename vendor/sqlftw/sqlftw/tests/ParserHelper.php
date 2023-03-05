<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Tests;

use SqlFtw\Parser\Lexer;
use SqlFtw\Parser\Parser;
use SqlFtw\Parser\ParserFactory;
use SqlFtw\Platform\ClientSideExtension;
use SqlFtw\Platform\Platform;
use SqlFtw\Session\Session;

class ParserHelper
{

    /**
     * @param Platform::*|null $platform
     * @param int|string|null $version
     */
    public static function getParserFactory(
        ?string $platform = null,
        $version = null,
        ?string $delimiter = null,
        bool $withComments = true,
        bool $withWhitespace = true
    ): ParserFactory
    {
        $platform = Platform::get($platform ?? Platform::MYSQL, $version);

        $extensions = ClientSideExtension::ALLOW_QUESTION_MARK_PLACEHOLDERS_OUTSIDE_PREPARED_STATEMENTS
            | ClientSideExtension::ALLOW_NUMBERED_QUESTION_MARK_PLACEHOLDERS
            | ClientSideExtension::ALLOW_NAMED_DOUBLE_COLON_PLACEHOLDERS;

        $session = new Session($platform, $delimiter, null, null, $extensions);

        $lexer = new Lexer($session, $withComments, $withWhitespace);
        $parser = new Parser($session, $lexer);

        return $parser->getParserFactory();
    }

}
