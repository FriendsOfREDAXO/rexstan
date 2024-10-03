<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Handler;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\Literal;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\StatementImpl;

class HandlerReadCommand extends StatementImpl implements HandlerCommand
{

    private ObjectIdentifier $table;

    private HandlerReadTarget $what;

    private ?string $index;

    /** @var non-empty-list<scalar|Literal>|null */
    private ?array $values;

    private ?RootNode $where;

    private ?int $limit;

    private ?int $offset;

    /**
     * @param non-empty-list<scalar|Literal>|null $values
     */
    public function __construct(
        ObjectIdentifier $table,
        HandlerReadTarget $what,
        ?string $index = null,
        ?array $values = null,
        ?RootNode $where = null,
        ?int $limit = null,
        ?int $offset = null
    ) {
        $this->table = $table;
        $this->what = $what;
        $this->index = $index;
        $this->values = $values;
        $this->where = $where;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->table;
    }

    public function getWhat(): HandlerReadTarget
    {
        return $this->what;
    }

    public function getIndex(): ?string
    {
        return $this->index;
    }

    /**
     * @return non-empty-list<scalar|Literal>|null
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    public function getWhere(): ?RootNode
    {
        return $this->where;
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
        $result = 'HANDLER ' . $this->table->serialize($formatter) . ' READ';
        if ($this->index !== null) {
            $result .= ' ' . $formatter->formatName($this->index);
        }
        $result .= ' ' . $this->what->serialize($formatter);

        if ($this->values !== null) {
            $result .= ' (' . $formatter->formatValuesList($this->values) . ')';
        }

        if ($this->where !== null) {
            $result .= ' WHERE ' . $this->where->serialize($formatter);
        }
        if ($this->limit !== null) {
            $result .= ' LIMIT ' . $this->limit;
        }
        if ($this->offset !== null) {
            $result .= ' OFFSET ' . $this->offset;
        }

        return $result;
    }

}
