<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Load;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Assignment;
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Dml\DmlCommand;
use SqlFtw\Sql\Dml\DuplicateOption;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\StatementImpl;

abstract class LoadCommand extends StatementImpl implements DmlCommand
{

    private string $file;

    private ObjectIdentifier $table;

    private ?Charset $charset;

    /** @var non-empty-list<string>|null */
    private ?array $fields;

    /** @var non-empty-list<Assignment>|null */
    private ?array $assignments;

    private ?int $ignoreRows;

    private ?LoadPriority $priority;

    private bool $local;

    private ?DuplicateOption $duplicateOption;

    /** @var non-empty-list<string>|null */
    private ?array $partitions;

    /**
     * @param non-empty-list<string>|null $fields
     * @param non-empty-list<Assignment>|null $assignments
     * @param non-empty-list<string>|null $partitions
     */
    public function __construct(
        string $file,
        ObjectIdentifier $table,
        ?Charset $charset = null,
        ?array $fields = null,
        ?array $assignments = null,
        ?int $ignoreRows = null,
        ?LoadPriority $priority = null,
        bool $local = false,
        ?DuplicateOption $duplicateOption = null,
        ?array $partitions = null
    ) {
        $this->file = $file;
        $this->table = $table;
        $this->charset = $charset;
        $this->fields = $fields;
        $this->assignments = $assignments;
        $this->ignoreRows = $ignoreRows;
        $this->priority = $priority;
        $this->local = $local;
        $this->duplicateOption = $duplicateOption;
        $this->partitions = $partitions;
    }

    abstract protected function getWhat(): string;

    abstract protected function serializeFormat(Formatter $formatter): string;

    public function getFile(): string
    {
        return $this->file;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->table;
    }

    public function getCharset(): ?Charset
    {
        return $this->charset;
    }

    /**
     * @return non-empty-list<string>|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @return non-empty-list<Assignment>|null
     */
    public function getAssignments(): ?array
    {
        return $this->assignments;
    }

    public function getIgnoreRows(): ?int
    {
        return $this->ignoreRows;
    }

    public function getPriority(): ?LoadPriority
    {
        return $this->priority;
    }

    public function local(): bool
    {
        return $this->local;
    }

    public function getDuplicateOption(): ?DuplicateOption
    {
        return $this->duplicateOption;
    }

    /**
     * @return non-empty-list<string>|null
     */
    public function getPartitions(): ?array
    {
        return $this->partitions;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'LOAD ' . $this->getWhat();

        if ($this->priority !== null) {
            $result .= ' ' . $this->priority->serialize($formatter);
        }
        if ($this->local) {
            $result .= ' LOCAL';
        }
        $result .= ' INFILE ' . $formatter->formatString($this->file);
        if ($this->duplicateOption !== null) {
            $result .= ' ' . $this->duplicateOption->serialize($formatter);
        }
        $result .= ' INTO TABLE ' . $this->table->serialize($formatter);
        if ($this->partitions !== null) {
            $result .= ' PARTITION (' . $formatter->formatNamesList($this->partitions) . ')';
        }
        if ($this->charset !== null) {
            $result .= ' CHARACTER SET ' . $this->charset->serialize($formatter);
        }

        $result .= $this->serializeFormat($formatter);

        if ($this->ignoreRows !== null) {
            $result .= ' IGNORE ' . $this->ignoreRows . ' LINES';
        }
        if ($this->fields !== null) {
            $result .= ' (' . $formatter->formatNamesList($this->fields) . ')';
        }
        if ($this->assignments !== null) {
            $result .= ' SET ' . $formatter->formatSerializablesList($this->assignments);
        }

        return $result;
    }

}
