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
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\UserName;

class RevokeAllCommand extends Statement implements UserCommand
{

    /** @var non-empty-list<UserName|FunctionCall> */
    private array $users;

    private bool $ifExists;

    private bool $ignoreUnknownUser;

    /**
     * @param non-empty-list<UserName|FunctionCall> $users
     */
    public function __construct(
        array $users,
        bool $ifExists = false,
        bool $ignoreUnknownUser = false
    ) {
        $this->users = $users;
        $this->ifExists = $ifExists;
        $this->ignoreUnknownUser = $ignoreUnknownUser;
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
            . ' ALL, GRANT OPTION FROM ' . $formatter->formatSerializablesList($this->users)
            . ($this->ignoreUnknownUser ? ' IGNORE UNKNOWN USER' : '');
    }

}
