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
use SqlFtw\Sql\StatementImpl;

class StopReplicaCommand extends StatementImpl implements ReplicationCommand
{

    private bool $ioThread;

    private bool $sqlThread;

    private ?string $channel;

    public function __construct(bool $ioThread = false, bool $sqlThread = false, ?string $channel = null)
    {
        $this->ioThread = $ioThread;
        $this->sqlThread = $sqlThread;
        $this->channel = $channel;
    }

    public function getIoThread(): bool
    {
        return $this->ioThread;
    }

    public function getSqlThread(): bool
    {
        return $this->sqlThread;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'STOP REPLICA';
        if ($this->ioThread) {
            $result .= ' IO_THREAD';
        }
        if ($this->ioThread && $this->sqlThread) {
            $result .= ',';
        }
        if ($this->sqlThread) {
            $result .= ' SQL_THREAD';
        }
        if ($this->channel !== null) {
            $result .= ' FOR CHANNEL ' . $formatter->formatString($this->channel);
        }

        return $result;
    }

}
