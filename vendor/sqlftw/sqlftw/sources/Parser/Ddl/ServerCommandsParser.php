<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Ddl;

use SqlFtw\Parser\TokenList;
use SqlFtw\Sql\Ddl\Server\AlterServerCommand;
use SqlFtw\Sql\Ddl\Server\CreateServerCommand;
use SqlFtw\Sql\Ddl\Server\DropServerCommand;
use SqlFtw\Sql\Keyword;

class ServerCommandsParser
{

    /**
     * ALTER SERVER server_name
     *     OPTIONS (option [, option] ...)
     *
     * option:
     *     HOST character-literal
     *   | DATABASE character-literal
     *   | USER character-literal
     *   | PASSWORD character-literal
     *   | SOCKET character-literal
     *   | OWNER character-literal
     *   | PORT numeric-literal
     */
    public function parseAlterServer(TokenList $tokenList): AlterServerCommand
    {
        $tokenList->expectKeywords(Keyword::ALTER, Keyword::SERVER);
        $name = $tokenList->expectNonReservedNameOrString();

        $tokenList->expectKeyword(Keyword::OPTIONS);
        $tokenList->expectSymbol('(');
        $host = $schema = $user = $password = $socket = $owner = $port = null;
        do {
            if ($tokenList->hasKeyword(Keyword::HOST)) {
                $host = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::DATABASE)) {
                $schema = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::USER)) {
                $user = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::PASSWORD)) {
                $password = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::SOCKET)) {
                $socket = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::OWNER)) {
                $owner = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::PORT)) {
                $port = (int) $tokenList->expectUnsignedInt();
            }
        } while ($tokenList->hasSymbol(','));
        if ($host === null && $schema === null && $user === null && $password === null && $socket === null && $owner === null && $port === null) {
            $tokenList->missingAnyKeyword(Keyword::HOST, Keyword::DATABASE, Keyword::USER, Keyword::PASSWORD, Keyword::SOCKET, Keyword::OWNER, Keyword::PORT);
        }

        $tokenList->expectSymbol(')');

        return new AlterServerCommand($name, $host, $schema, $user, $password, $socket, $owner, $port);
    }

    /**
     * CREATE SERVER server_name
     *     FOREIGN DATA WRAPPER wrapper_name
     *     OPTIONS (option [, option] ...)
     *
     * option:
     *     HOST character-literal
     *   | DATABASE character-literal
     *   | USER character-literal
     *   | PASSWORD character-literal
     *   | SOCKET character-literal
     *   | OWNER character-literal
     *   | PORT numeric-literal
     */
    public function parseCreateServer(TokenList $tokenList): CreateServerCommand
    {
        $tokenList->expectKeywords(Keyword::CREATE, Keyword::SERVER);
        $name = $tokenList->expectNonReservedNameOrString();
        $tokenList->expectKeywords(Keyword::FOREIGN, Keyword::DATA, Keyword::WRAPPER);
        $wrapper = $tokenList->expectNonReservedNameOrString();

        $tokenList->expectKeyword(Keyword::OPTIONS);
        $tokenList->expectSymbol('(');
        $host = $schema = $user = $password = $socket = $owner = $port = null;
        do {
            if ($tokenList->hasKeyword(Keyword::HOST)) {
                $host = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::DATABASE)) {
                $schema = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::USER)) {
                $user = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::PASSWORD)) {
                $password = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::SOCKET)) {
                $socket = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::OWNER)) {
                $owner = $tokenList->expectString();
            } elseif ($tokenList->hasKeyword(Keyword::PORT)) {
                $port = (int) $tokenList->expectUnsignedInt();
            }
        } while ($tokenList->hasSymbol(','));
        if ($host === null && $schema === null && $user === null && $password === null && $socket === null && $owner === null && $port === null) {
            $tokenList->missingAnyKeyword(Keyword::HOST, Keyword::DATABASE, Keyword::USER, Keyword::PASSWORD, Keyword::SOCKET, Keyword::OWNER, Keyword::PORT);
        }
        $tokenList->expectSymbol(')');

        return new CreateServerCommand($name, $wrapper, $host, $schema, $user, $password, $socket, $owner, $port);
    }

    /**
     * DROP SERVER [ IF EXISTS ] server_name
     */
    public function parseDropServer(TokenList $tokenList): DropServerCommand
    {
        $tokenList->expectKeywords(Keyword::DROP, Keyword::SERVER);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);
        $name = $tokenList->expectNonReservedNameOrString();

        return new DropServerCommand($name, $ifExists);
    }

}
