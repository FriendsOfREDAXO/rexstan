<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Alter\Action;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\Table\Partition\PartitionDefinition;
use SqlFtw\Sql\InvalidDefinitionException;

class ReorganizePartitionAction implements PartitioningAction
{

    /** @var non-empty-list<string>|null */
    private ?array $partitions;

    /** @var non-empty-list<PartitionDefinition>|null */
    private ?array $newPartitions;

    /**
     * @param non-empty-list<string>|null $partitions
     * @param non-empty-list<PartitionDefinition>|null $newPartitions
     */
    public function __construct(?array $partitions, ?array $newPartitions)
    {
        if (($partitions === null) ^ ($newPartitions === null)) { // @phpstan-ignore-line XOR needed
            throw new InvalidDefinitionException('Old partitions and new partitions must be both null or both defined.');
        }

        $this->partitions = $partitions;
        $this->newPartitions = $newPartitions;
    }

    /**
     * @return non-empty-list<string>|null
     */
    public function getPartitions(): ?array
    {
        return $this->partitions;
    }

    /**
     * @return non-empty-list<PartitionDefinition>|null
     */
    public function getNewPartitions(): ?array
    {
        return $this->newPartitions;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'REORGANIZE PARTITION';
        if ($this->partitions !== null && $this->newPartitions !== null) {
            $result .= ' ' . $formatter->formatNamesList($this->partitions)
                . ' INTO (' . $formatter->formatSerializablesList($this->newPartitions) . ')';
        }

        return $result;
    }

}
