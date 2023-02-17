<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Cache;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\SqlSerializable;
use function is_array;

class TableIndexList implements SqlSerializable
{

    private ObjectIdentifier $table;

    /** @var non-empty-list<string>|null */
    private ?array $indexes;

    /** @var non-empty-list<string>|bool|null */
    private $partitions;

    private bool $ignoreLeaves;

    /**
     * @param non-empty-list<string>|null $indexes
     * @param non-empty-list<string>|true|null $partitions
     */
    public function __construct(
        ObjectIdentifier $table,
        ?array $indexes = null,
        $partitions = null,
        bool $ignoreLeaves = false
    ) {
        $this->table = $table;
        $this->indexes = $indexes;
        $this->partitions = $partitions;
        $this->ignoreLeaves = $ignoreLeaves;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->table;
    }

    /**
     * @return non-empty-list<string>|null
     */
    public function getIndexes(): ?array
    {
        return $this->indexes;
    }

    /**
     * @return non-empty-list<string>|bool|null
     */
    public function getPartitions()
    {
        return $this->partitions;
    }

    public function ignoreLeafs(): bool
    {
        return $this->ignoreLeaves;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->table->serialize($formatter);

        if ($this->partitions !== null) {
            $result .= ' PARTITION';
            if (is_array($this->partitions)) {
                $result .= ' (' . $formatter->formatNamesList($this->partitions) . ')';
            } else {
                $result .= ' (ALL)';
            }
        }

        if ($this->indexes !== null) {
            $result .= ' INDEX (' . $formatter->formatNamesList($this->indexes) . ')';
        }

        if ($this->ignoreLeaves) {
            $result .= ' IGNORE LEAVES';
        }

        return $result;
    }

}
