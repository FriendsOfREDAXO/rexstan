<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Partition;

use Dogma\Arr;
use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\Table\Option\StorageEngine;
use SqlFtw\Sql\Expression\MaxValueLiteral;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\SqlSerializable;
use function implode;
use function is_array;

class PartitionDefinition implements SqlSerializable
{

    private string $name;

    /** @var non-empty-list<RootNode>|MaxValueLiteral|null */
    private $lessThan;

    /** @var non-empty-list<RootNode>|null */
    private ?array $values;

    /** @var non-empty-array<PartitionOption::*, int|string|StorageEngine>|null */
    private ?array $options;

    /** @var non-empty-array<string, non-empty-array<PartitionOption::*, int|string|StorageEngine>|null>|null */
    private ?array $subpartitions;

    /**
     * @param non-empty-list<RootNode>|MaxValueLiteral|null $lessThan
     * @param non-empty-list<RootNode>|null $values
     * @param non-empty-array<PartitionOption::*, int|string|StorageEngine>|null $options
     * @param non-empty-array<string, non-empty-array<PartitionOption::*, int|string|StorageEngine>|null>|null $subpartitions
     */
    public function __construct(string $name, $lessThan, ?array $values = null, ?array $options = null, ?array $subpartitions = null)
    {
        if ($options !== null) {
            foreach ($options as $option => $value) {
                PartitionOption::checkValue($option);
            }
        }
        if ($subpartitions !== null) {
            foreach ($subpartitions as $subpartitionOptions) {
                if ($subpartitionOptions !== null) {
                    foreach ($subpartitionOptions as $option => $value) {
                        PartitionOption::checkValue($option);
                    }
                }
            }
        }

        $this->name = $name;
        $this->lessThan = $lessThan;
        $this->values = $values;
        $this->options = $options;
        $this->subpartitions = $subpartitions;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-list<RootNode>|MaxValueLiteral|null
     */
    public function getLessThan()
    {
        return $this->lessThan;
    }

    /**
     * @return non-empty-list<RootNode>|null
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * @return non-empty-array<PartitionOption::*, int|string|StorageEngine>|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @return non-empty-array<string, non-empty-array<PartitionOption::*, int|string|StorageEngine>|null>|null
     */
    public function getSubpartitions(): ?array
    {
        return $this->subpartitions;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'PARTITION ' . $formatter->formatName($this->name);

        if ($this->lessThan !== null) {
            $result .= ' VALUES LESS THAN ';
            if (is_array($this->lessThan)) {
                $result .= '(' . $formatter->formatValuesList($this->lessThan) . ')';
            } else {
                $result .= $this->lessThan->serialize($formatter);
            }
        } elseif ($this->values !== null) {
            $result .= ' VALUES IN (' . $formatter->formatSerializablesList($this->values) . ')';
        }
        if ($this->options !== null) {
            foreach ($this->options as $option => $value) {
                if ($value instanceof SqlSerializable) {
                    $result .= ' ' . $option . ' ' . $value->serialize($formatter);
                } elseif ($option === PartitionOption::TABLESPACE) {
                    $result .= ' ' . $option . ' ' . $formatter->formatName((string) $value);
                } else {
                    $result .= ' ' . $option . ' ' . $formatter->formatValue($value);
                }
            }
        }
        if ($this->subpartitions !== null) {
            $result .= ' (' . implode(', ', Arr::mapPairs($this->subpartitions, static function ($name, $options) use ($formatter): string {
                $sub = 'SUBPARTITION ' . $formatter->formatName($name);
                if ($options !== null) {
                    foreach ($options as $option => $value) {
                        if ($option === PartitionOption::TABLESPACE) {
                            $sub .= ' ' . $option . ' ' . $formatter->formatName($value);
                        } else {
                            $sub .= ' ' . $option . ' ' . $formatter->formatValue($value);
                        }
                    }
                }

                return $sub;
            })) . ')';
        }

        return $result;
    }

}
