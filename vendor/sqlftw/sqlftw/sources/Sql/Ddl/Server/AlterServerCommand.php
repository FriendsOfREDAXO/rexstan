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
use SqlFtw\Sql\Statement;
use function rtrim;

class AlterServerCommand extends Statement implements ServerCommand
{

    private string $server;

    private ?string $host;

    private ?string $schema;

    private ?string $user;

    private ?string $password;

    private ?string $socket;

    private ?string $owner;

    private ?int $port;

    public function __construct(
        string $server,
        ?string $host = null,
        ?string $schema = null,
        ?string $user = null,
        ?string $password = null,
        ?string $socket = null,
        ?string $owner = null,
        ?int $port = null
    ) {
        $this->server = $server;
        $this->host = $host;
        $this->schema = $schema;
        $this->user = $user;
        $this->password = $password;
        $this->socket = $socket;
        $this->owner = $owner;
        $this->port = $port;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getSchema(): ?string
    {
        return $this->schema;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSocket(): ?string
    {
        return $this->socket;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'ALTER SERVER ' . $formatter->formatName($this->server) . ' OPTIONS (';

        if ($this->host !== null) {
            $result .= 'HOST ' . $formatter->formatString($this->host) . ', ';
        }
        if ($this->schema !== null) {
            $result .= 'DATABASE ' . $formatter->formatString($this->schema) . ', ';
        }
        if ($this->user !== null) {
            $result .= 'USER ' . $formatter->formatString($this->user) . ', ';
        }
        if ($this->password !== null) {
            $result .= 'PASSWORD ' . $formatter->formatString($this->password) . ', ';
        }
        if ($this->socket !== null) {
            $result .= 'SOCKET ' . $formatter->formatString($this->socket) . ', ';
        }
        if ($this->owner !== null) {
            $result .= 'OWNER ' . $formatter->formatString($this->owner) . ', ';
        }
        if ($this->port !== null) {
            $result .= 'PORT ' . $this->port . ', ';
        }

        return rtrim(rtrim($result, ' '), ',') . ')';
    }

}
