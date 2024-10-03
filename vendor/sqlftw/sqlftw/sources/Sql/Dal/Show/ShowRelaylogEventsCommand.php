<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Show;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\StatementImpl;

class ShowRelaylogEventsCommand extends StatementImpl implements ShowCommand
{

    private ?string $logName;

    private ?int $from;

    private ?int $limit;

    private ?int $offset;

    private ?string $channel;

    public function __construct(?string $logName, ?int $from, ?int $limit, ?int $offset, ?string $channel)
    {
        $this->logName = $logName;
        $this->from = $from;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->channel = $channel;
    }

    public function getLogName(): ?string
    {
        return $this->logName;
    }

    public function getFrom(): ?int
    {
        return $this->from;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'SHOW RELAYLOG EVENTS';
        if ($this->logName !== null) {
            $result .= ' IN ' . $formatter->formatString($this->logName);
        }
        if ($this->from !== null) {
            $result .= ' FROM ' . $this->from;
        }
        if ($this->limit !== null) {
            $result .= ' LIMIT ' . $this->limit;
            if ($this->offset !== null) {
                $result .= ' OFFSET ' . $this->offset;
            }
        }
        if ($this->channel !== null) {
            $result .= ' FOR CHANNEL ' . $formatter->formatString($this->channel);
        }

        return $result;
    }

}
