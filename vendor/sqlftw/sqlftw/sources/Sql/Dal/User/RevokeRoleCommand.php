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
use SqlFtw\Sql\StatementImpl;
use SqlFtw\Sql\UserName;

class RevokeRoleCommand extends StatementImpl implements UserCommand
{

    /** @var non-empty-list<UserName> */
    private array $roles;

    /** @var non-empty-list<UserName|FunctionCall> */
    private array $users;

    private bool $ifExists;

    private bool $ignoreUnknownUser;

    /**
     * @param non-empty-list<UserName> $roles
     * @param non-empty-list<UserName|FunctionCall> $users
     */
    public function __construct(
        array $roles,
        array $users,
        bool $ifExists = false,
        bool $ignoreUnknownUser = false
    ) {
        $this->roles = $roles;
        $this->users = $users;
        $this->ifExists = $ifExists;
        $this->ignoreUnknownUser = $ignoreUnknownUser;
    }

    /**
     * @return non-empty-list<UserName>
     */
    public function getRoles(): array
    {
        return $this->roles;
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
        return 'REVOKE ' . ($this->ifExists ? 'IF EXISTS ' : '')
            . $formatter->formatSerializablesList($this->roles)
            . ' FROM ' . $formatter->formatSerializablesList($this->users)
            . ($this->ignoreUnknownUser ? ' IGNORE UNKNOWN USER' : '');
    }

}
