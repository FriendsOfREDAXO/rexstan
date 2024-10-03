<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\User;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\FunctionCall;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\StatementImpl;
use SqlFtw\Sql\UserName;

class RevokeProxyCommand extends StatementImpl implements UserCommand
{

    /** @var UserName|FunctionCall */
    private SqlSerializable $proxy;

    /** @var non-empty-list<UserName|FunctionCall> */
    private array $users;

    private bool $ifExists;

    private bool $ignoreUnknownUser;

    /**
     * @param UserName|FunctionCall $proxy
     * @param non-empty-list<UserName|FunctionCall> $users
     */
    public function __construct(
        SqlSerializable $proxy,
        array $users,
        bool $ifExists = false,
        bool $ignoreUnknownUser = false
    ) {
        $this->proxy = $proxy;
        $this->users = $users;
        $this->ifExists = $ifExists;
        $this->ignoreUnknownUser = $ignoreUnknownUser;
    }

    /**
     * @return UserName|FunctionCall
     */
    public function getProxy(): SqlSerializable
    {
        return $this->proxy;
    }

    /**
     * @return non-empty-list<UserName|FunctionCall>
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    public function ifExists(): bool
    {
        return $this->ifExists;
    }

    public function ignoreUnknownUser(): bool
    {
        return $this->ignoreUnknownUser;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'REVOKE ' . ($this->ifExists ? 'IF EXISTS' : '')
            . ' PROXY ON ' . $this->proxy->serialize($formatter)
            . ' FROM ' . $formatter->formatSerializablesList($this->users)
            . ($this->ignoreUnknownUser ? ' IGNORE UNKNOWN USER' : '');
    }

}
