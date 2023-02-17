<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Formatter\Formatter;

class JsonTableColumnsList implements ArgumentNode, ArgumentValue
{

    /** @var non-empty-list<JsonTableColumn> */
    private array $columns;

    /**
     * @param non-empty-list<JsonTableColumn> $columns
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return non-empty-list<JsonTableColumn>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function serialize(Formatter $formatter): string
    {
        return '(' . $formatter->formatSerializablesList($this->columns) . ')';
    }

}
