<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Server;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\StatementImpl;

class DropServerCommand extends StatementImpl implements ServerCommand
{

    private string $server;

    private bool $ifExists;

    public function __construct(string $server, bool $ifExists = false)
    {
        $this->server = $server;
        $this->ifExists = $ifExists;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function getIfExists(): bool
    {
        return $this->ifExists;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'DROP SERVER ' . ($this->ifExists ? 'IF EXISTS ' : '') . $formatter->formatName($this->server);
    }

}
