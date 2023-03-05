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
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\UserName;

class GrantProxyCommand extends Statement implements UserCommand
{

    /** @var UserName|FunctionCall */
    private SqlSerializable $proxy;

    /** @var non-empty-list<IdentifiedUser|FunctionCall> */
    private array $users;

    private bool $withGrantOption;

    /**
     * @param UserName|FunctionCall $proxy
     * @param non-empty-list<IdentifiedUser|FunctionCall> $users
     */
    public function __construct(SqlSerializable $proxy, array $users, bool $withGrantOption = false)
    {
        $this->proxy = $proxy;
        $this->users = $users;
        $this->withGrantOption = $withGrantOption;
    }

    /**
     * @return UserName|FunctionCall
     */
    public function getProxy(): SqlSerializable
    {
        return $this->proxy;
    }

    /**
     * @return non-empty-list<IdentifiedUser|FunctionCall>
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    public function withGrantOption(): bool
    {
        return $this->withGrantOption;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'GRANT PROXY ON ' . $this->proxy->serialize($formatter)
            . ' TO ' . $formatter->formatSerializablesList($this->users)
            . ($this->withGrantOption ? ' WITH GRANT OPTION' : '');
    }

}
