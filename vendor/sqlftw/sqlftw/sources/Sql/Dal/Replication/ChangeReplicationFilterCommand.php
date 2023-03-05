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

class ChangeReplicationFilterCommand extends Statement implements ReplicationCommand
{

    /** @var non-empty-list<ReplicationFilter> */
    private array $filters;

    private ?string $channel;

    /**
     * @param non-empty-list<ReplicationFilter> $filters
     */
    public function __construct(array $filters, ?string $channel = null)
    {
        $this->filters = $filters;
        $this->channel = $channel;
    }

    /**
     * @return non-empty-list<ReplicationFilter>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = "CHANGE REPLICATION FILTER\n  " . $formatter->formatSerializablesList($this->filters);

        if ($this->channel !== null) {
            $result .= "\n  FOR CHANNEL " . $formatter->formatName($this->channel);
        }

        return $result;
    }

}
