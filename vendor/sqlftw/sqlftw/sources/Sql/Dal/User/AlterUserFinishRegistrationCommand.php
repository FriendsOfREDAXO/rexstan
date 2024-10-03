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

class AlterUserFinishRegistrationCommand extends StatementImpl implements AlterUserRegistrationCommand
{

    /** @var UserName|FunctionCall */
    private SqlSerializable $user;

    private int $factor;

    private string $authString;

    private bool $ifExists;

    /**
     * @param UserName|FunctionCall $user
     */
    public function __construct(SqlSerializable $user, int $factor, string $authString, bool $ifExists = false)
    {
        $this->user = $user;
        $this->factor = $factor;
        $this->authString = $authString;
        $this->ifExists = $ifExists;
    }

    /**
     * @return UserName|FunctionCall
     */
    public function getUser(): SqlSerializable
    {
        return $this->user;
    }

    public function getFactor(): int
    {
        return $this->factor;
    }

    public function getAuthString(): string
    {
        return $this->authString;
    }

    public function ifExists(): bool
    {
        return $this->ifExists;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'ALTER USER ' . ($this->ifExists ? 'IF EXISTS ' : '') . $this->user->serialize($formatter)
            . ' ' . $this->factor . ' FINISH REGISTRATION SET CHALLENGE_RESPONSE AS ' . $formatter->formatString($this->authString);
    }

}
