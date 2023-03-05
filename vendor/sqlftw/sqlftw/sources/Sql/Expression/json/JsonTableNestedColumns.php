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

class JsonTableNestedColumns implements JsonTableColumn
{

    private StringValue $path;

    private JsonTableColumnsList $columns;

    public function __construct(StringValue $path, JsonTableColumnsList $columns)
    {
        $this->path = $path;
        $this->columns = $columns;
    }

    public function getPath(): StringValue
    {
        return $this->path;
    }

    /**
     * @return non-empty-list<JsonTableColumn>
     */
    public function getColumns(): array
    {
        return $this->columns->getColumns();
    }

    public function getColumnsList(): JsonTableColumnsList
    {
        return $this->columns;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'NESTED PATH ' . $this->path->serialize($formatter) . ' COLUMNS ' . $this->columns->serialize($formatter);
    }

}
