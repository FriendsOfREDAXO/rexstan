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

class ShowBinlogEventsCommand extends StatementImpl implements ShowCommand
{

    private ?string $logName;

    private ?int $limit;

    private ?int $offset;

    public function __construct(?string $logName, ?int $limit, ?int $offset)
    {
        $this->logName = $logName;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getLogName(): ?string
    {
        return $this->logName;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'SHOW BINLOG EVENTS';
        if ($this->logName !== null) {
            $result .= ' IN ' . $formatter->formatString($this->logName);
        }
        if ($this->offset !== null && $this->limit === null) {
            $result .= ' FROM ' . $this->offset;
        }
        if ($this->limit !== null) {
            $result .= ' LIMIT ' . $this->limit;
            if ($this->offset !== null) {
                $result .= ' OFFSET ' . $this->offset;
            }
        }

        return $result;
    }

}
