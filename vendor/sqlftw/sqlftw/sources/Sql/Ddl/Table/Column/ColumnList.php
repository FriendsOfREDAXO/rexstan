<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Column;

class ColumnList
{

    /** @var array<string, ColumnDefinition> ($name => $column) */
    private array $columns = [];

    /**
     * @param array<string, ColumnDefinition> $columns
     */
    public function __construct(array $columns)
    {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
    }

    private function addColumn(ColumnDefinition $column): void
    {
        $this->columns[$column->getName()] = $column;
    }

    /**
     * All column actions except DROP
     * @return array<string, ColumnDefinition>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getColumn(string $name): ?ColumnDefinition
    {
        return $this->columns[$name] ?? null;
    }

    public function containsColumn(ColumnDefinition $searchedColumn): bool
    {
        return $this->getColumn($searchedColumn->getName()) === $searchedColumn;
    }

}
