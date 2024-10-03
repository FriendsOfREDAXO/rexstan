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

class SetPasswordCommand extends StatementImpl implements UserCommand
{

    /** @var UserName|FunctionCall|null */
    private ?SqlSerializable $user;

    private ?string $passwordFunction;

    private ?string $password;

    private ?string $replace;

    private bool $retainCurrent;

    /**
     * @param UserName|FunctionCall|null $user
     */
    public function __construct(
        ?SqlSerializable $user,
        ?string $passwordFunction,
        ?string $password,
        ?string $replace,
        bool $retainCurrent
    )
    {
        $this->user = $user;
        $this->passwordFunction = $passwordFunction;
        $this->password = $password;
        $this->replace = $replace;
        $this->retainCurrent = $retainCurrent;
    }

    /**
     * @return UserName|FunctionCall|null
     */
    public function getUser(): ?SqlSerializable
    {
        return $this->user;
    }

    public function passwordFunction(): ?string
    {
        return $this->passwordFunction;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getReplace(): ?string
    {
        return $this->replace;
    }

    public function retainCurrent(): bool
    {
        return $this->retainCurrent;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'SET PASSWORD';
        if ($this->user !== null) {
            $result .= ' FOR ' . $this->user->serialize($formatter);
        }
        if ($this->password === null) {
            $result .= ' TO RANDOM';
        } elseif ($this->passwordFunction !== null) {
            $result .= ' = ' . $this->passwordFunction . '(' . $formatter->formatString($this->password) . ')';
        } else {
            $result .= ' = ' . $formatter->formatString($this->password);
        }
        if ($this->replace !== null) {
            $result .= ' REPLACE ' . $formatter->formatString($this->replace);
        }
        if ($this->retainCurrent) {
            $result .= ' RETAIN CURRENT PASSWORD';
        }

        return $result;
    }

}
