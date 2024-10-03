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
use SqlFtw\Sql\StatementImpl;
use SqlFtw\Sql\UserName;

class CreateRoleCommand extends StatementImpl implements UserCommand
{

    /** @var non-empty-list<UserName> */
    private array $roles;

    private bool $ifNotExists;

    /**
     * @param non-empty-list<UserName> $roles
     */
    public function __construct(array $roles, bool $ifNotExists = false)
    {
        $this->roles = $roles;
        $this->ifNotExists = $ifNotExists;
    }

    /**
     * @return non-empty-list<UserName>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function ifNotExists(): bool
    {
        return $this->ifNotExists;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'CREATE ROLE ' . ($this->ifNotExists ? 'IF NOT EXISTS ' : '') . $formatter->formatSerializablesList($this->roles);
    }

}
