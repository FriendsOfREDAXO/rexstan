<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Constraint;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\SqlSerializable;

class ReferenceDefinition implements SqlSerializable
{

    private ObjectIdentifier $sourceTable;

    /** @var non-empty-list<string> */
    private array $sourceColumns;

    private ?ForeignKeyAction $onUpdate;

    private ?ForeignKeyAction $onDelete;

    private ?ForeignKeyMatchType $matchType;

    /**
     * @param non-empty-list<string> $sourceColumns
     */
    public function __construct(
        ObjectIdentifier $sourceTable,
        array $sourceColumns,
        ?ForeignKeyAction $onDelete = null,
        ?ForeignKeyAction $onUpdate = null,
        ?ForeignKeyMatchType $matchType = null
    ) {
        $this->sourceTable = $sourceTable;
        $this->sourceColumns = $sourceColumns;
        $this->onDelete = $onDelete;
        $this->onUpdate = $onUpdate;
        $this->matchType = $matchType;
    }

    public function getSourceTable(): ObjectIdentifier
    {
        return $this->sourceTable;
    }

    /**
     * @return non-empty-list<string>
     */
    public function getSourceColumns(): array
    {
        return $this->sourceColumns;
    }

    public function getOnDelete(): ?ForeignKeyAction
    {
        return $this->onDelete;
    }

    public function setOnDelete(ForeignKeyAction $action): void
    {
        $this->onDelete = $action;
    }

    public function getOnUpdate(): ?ForeignKeyAction
    {
        return $this->onUpdate;
    }

    public function setOnUpdate(ForeignKeyAction $action): void
    {
        $this->onUpdate = $action;
    }

    public function getMatchType(): ?ForeignKeyMatchType
    {
        return $this->matchType;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'REFERENCES ' . $this->sourceTable->serialize($formatter) . ' (' . $formatter->formatNamesList($this->sourceColumns) . ')';

        if ($this->matchType !== null) {
            $result .= ' MATCH ' . $this->matchType->serialize($formatter);
        }
        if ($this->onDelete !== null) {
            $result .= ' ON DELETE ' . $this->onDelete->serialize($formatter);
        }
        if ($this->onUpdate !== null) {
            $result .= ' ON UPDATE ' . $this->onUpdate->serialize($formatter);
        }

        return $result;
    }

}
