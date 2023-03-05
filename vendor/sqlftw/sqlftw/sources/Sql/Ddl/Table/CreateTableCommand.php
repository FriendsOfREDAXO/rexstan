<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\Table\Column\ColumnDefinition;
use SqlFtw\Sql\Ddl\Table\Option\TableOption;
use SqlFtw\Sql\Ddl\Table\Option\TableOptionsList;
use SqlFtw\Sql\Ddl\Table\Partition\PartitioningDefinition;
use SqlFtw\Sql\Dml\DuplicateOption;
use SqlFtw\Sql\Dml\Query\Query;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\Statement;
use function is_array;

/**
 * @phpstan-import-type TableOptionValue from TableOption
 */
class CreateTableCommand extends Statement implements AnyCreateTableCommand
{

    private ObjectIdentifier $name;

    /** @var non-empty-list<TableItem>|null */
    private ?array $items;

    private ?TableOptionsList $options;

    private ?PartitioningDefinition $partitioning;

    private bool $temporary;

    private bool $ifNotExists;

    private ?DuplicateOption $duplicateOption;

    private ?Query $query;

    private bool $startTransaction;

    /**
     * @param non-empty-list<TableItem>|null $items
     * @param TableOptionsList|array<TableOption::*, TableOptionValue>|null $options
     */
    public function __construct(
        ObjectIdentifier $name,
        ?array $items,
        $options = null,
        ?PartitioningDefinition $partitioning = null,
        bool $temporary = false,
        bool $ifNotExists = false,
        ?DuplicateOption $duplicateOption = null,
        ?Query $query = null,
        bool $startTransaction = false
    ) {
        if ($duplicateOption !== null && $query === null) {
            throw new InvalidDefinitionException('IGNORE/REPLACE can be uses only with CREATE TABLE AS ... command.');
        }

        $this->name = $name;
        $this->items = $items;
        $this->options = is_array($options) ? new TableOptionsList($options) : $options;
        $this->partitioning = $partitioning;
        $this->temporary = $temporary;
        $this->ifNotExists = $ifNotExists;
        $this->duplicateOption = $duplicateOption;
        $this->query = $query;
        $this->startTransaction = $startTransaction;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->name;
    }

    /**
     * @return non-empty-list<TableItem>|null
     */
    public function getItems(): ?array
    {
        return $this->items;
    }

    /**
     * @return list<ColumnDefinition>
     */
    public function getColumns(): array
    {
        if ($this->items === null) {
            return [];
        }
        $columns = [];
        foreach ($this->items as $item) {
            if ($item instanceof ColumnDefinition) {
                $columns[] = $item;
            }
        }

        return $columns;
    }

    /**
     * @return array<TableOption::*, TableOptionValue|null>
     */
    public function getOptions(): array
    {
        return $this->options !== null ? $this->options->getOptions() : [];
    }

    public function getOptionsList(): TableOptionsList
    {
        return $this->options ?? new TableOptionsList([]);
    }

    public function getPartitioning(): ?PartitioningDefinition
    {
        return $this->partitioning;
    }

    public function isTemporary(): bool
    {
        return $this->temporary;
    }

    public function ifNotExists(): bool
    {
        return $this->ifNotExists;
    }

    public function getDuplicateOption(): ?DuplicateOption
    {
        return $this->duplicateOption;
    }

    public function getQuery(): ?Query
    {
        return $this->query;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CREATE ';
        if ($this->temporary) {
            $result .= 'TEMPORARY ';
        }
        $result .= 'TABLE ';
        if ($this->ifNotExists) {
            $result .= 'IF NOT EXISTS ';
        }
        $result .= $this->name->serialize($formatter);

        if ($this->items !== null) {
            $result .= " (\n" . $formatter->indent . $formatter->formatSerializablesList($this->items, ",\n" . $formatter->indent) . "\n)";
        }

        if ($this->options !== null && !$this->options->isEmpty()) {
            $result .= ' ' . $this->options->serialize($formatter, ', ', ' ');
        }

        if ($this->partitioning !== null) {
            $result .= ' ' . $this->partitioning->serialize($formatter);
        }

        if ($this->duplicateOption !== null) {
            $result .= "\n" . $this->duplicateOption->serialize($formatter);
        }

        if ($this->query !== null) {
            $result .= "\nAS " . $this->query->serialize($formatter);
        }

        if ($this->startTransaction) {
            $result .= "\nSTART TRANSACTION";
        }

        return $result;
    }

}
