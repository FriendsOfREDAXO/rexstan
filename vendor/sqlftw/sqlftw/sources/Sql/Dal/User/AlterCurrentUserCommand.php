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
use SqlFtw\Sql\Statement;

class AlterCurrentUserCommand extends Statement implements UserCommand
{

    private ?AuthOption $option;

    private ?string $replace;

    private bool $retainCurrentPassword;

    private bool $discardOldPassword;

    private bool $ifExists;

    public function __construct(
        ?AuthOption $option,
        ?string $replace = null,
        bool $retainCurrentPassword = false,
        bool $discardOldPassword = false,
        bool $ifExists = false
    ) {
        $this->option = $option;
        $this->replace = $replace;
        $this->retainCurrentPassword = $retainCurrentPassword;
        $this->discardOldPassword = $discardOldPassword;
        $this->ifExists = $ifExists;
    }

    public function getOption(): ?AuthOption
    {
        return $this->option;
    }

    public function getReplace(): ?string
    {
        return $this->replace;
    }

    public function retainCurrentPassword(): bool
    {
        return $this->retainCurrentPassword;
    }

    public function discardOldPassword(): bool
    {
        return $this->discardOldPassword;
    }

    public function ifExists(): bool
    {
        return $this->ifExists;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'ALTER USER ' . ($this->ifExists ? 'IF EXISTS ' : '') . 'USER()';

        if ($this->option !== null) {
            $result .= ' ' . $this->option->serialize($formatter);
        }
        if ($this->replace !== null) {
            $result .= ' REPLACE ' . $formatter->formatString($this->replace);
        }
        if ($this->retainCurrentPassword) {
            $result .= ' RETAIN CURRENT PASSWORD';
        }
        if ($this->discardOldPassword) {
            $result .= ' DISCARD OLD PASSWORD';
        }

        return $result;
    }

}
