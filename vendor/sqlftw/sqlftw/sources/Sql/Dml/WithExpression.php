<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dml\Query\Query;
use SqlFtw\Sql\SqlSerializable;

class WithExpression implements SqlSerializable
{

    private Query $query;

    private string $name;

    /** @var non-empty-list<string>|null */
    private ?array $columns;

    /**
     * @param non-empty-list<string>|null $columns
     */
    public function __construct(Query $query, string $name, ?array $columns = null)
    {
        $this->query = $query;
        $this->name = $name;
        $this->columns = $columns;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-list<string>|null
     */
    public function getColumns(): ?array
    {
        return $this->columns;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->name;
        if ($this->columns !== null) {
            $result .= ' (' . $formatter->formatNamesList($this->columns) . ')';
        }

        return $result . ' AS (' . $this->query->serialize($formatter) . ')';
    }

}
