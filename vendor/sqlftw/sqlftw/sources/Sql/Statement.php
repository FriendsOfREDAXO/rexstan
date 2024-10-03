<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

use SqlFtw\Parser\TokenList;

interface Statement extends SqlSerializable
{

    public function setTokenList(TokenList $tokenList): void;

    public function getTokenList(): TokenList;

    public function setDelimiter(string $delimiter): void;

    public function getDelimiter(): ?string;

    /**
     * @param list<string> $comments
     */
    public function setCommentsBefore(array $comments): void;

    /**
     * @return list<string>
     */
    public function getCommentsBefore(): array;

}
