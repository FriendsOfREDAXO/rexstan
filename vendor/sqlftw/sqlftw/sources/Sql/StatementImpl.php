<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

use LogicException;
use SqlFtw\Parser\TokenList;

abstract class StatementImpl implements Statement
{

    protected ?TokenList $tokenList = null;

    protected ?string $delimiter = null;

    /** @var list<string> */
    protected array $commentsBefore = [];

    public function setTokenList(TokenList $tokenList): void
    {
        $this->tokenList = $tokenList;
    }

    public function getTokenList(): TokenList
    {
        if ($this->tokenList === null) {
            throw new LogicException('Command was not generated with token list. Use ParserConfig with $provideTokenLists set to true.');
        }

        return $this->tokenList;
    }

    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    public function getDelimiter(): ?string
    {
        return $this->delimiter;
    }

    /**
     * @param list<string> $comments
     */
    public function setCommentsBefore(array $comments): void
    {
        $this->commentsBefore = $comments;
    }

    /**
     * @return list<string>
     */
    public function getCommentsBefore(): array
    {
        return $this->commentsBefore;
    }

}
