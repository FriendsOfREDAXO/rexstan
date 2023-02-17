<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Replication;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Statement;

class StartGroupReplicationCommand extends Statement implements GroupReplicationCommand
{

    private ?string $user;

    private ?string $password;

    private ?string $defaultAuth;

    public function __construct(?string $user = null, ?string $password = null, ?string $defaultAuth = null)
    {
        $this->user = $user;
        $this->password = $password;
        $this->defaultAuth = $defaultAuth;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getDefaultAuth(): ?string
    {
        return $this->defaultAuth;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'START GROUP_REPLICATION';
        if ($this->user !== null) {
            $result .= ' USER = ' . $formatter->formatString($this->user);
            if ($this->password !== null) {
                $result .= ', PASSWORD = ' . $formatter->formatString($this->password);
            }
        }
        if ($this->defaultAuth !== null) {
            $result .= ($this->user !== null ? ',' : '') . ' DEFAULT_AUTH ' . $formatter->formatString($this->defaultAuth);
        }

        return $result;
    }

}
