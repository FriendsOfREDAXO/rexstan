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

/**
 * ROW (...[, ...])
 *
 * @see https://dev.mysql.com/doc/refman/8.0/en/row-subqueries.html
 */
class RowExpression implements RootNode
{

    /** @var non-empty-list<RootNode> */
    private array $contents;

    /**
     * @param non-empty-list<RootNode> $contents
     */
    public function __construct(array $contents)
    {
        $this->contents = $contents;
    }

    /**
     * @return non-empty-list<RootNode>
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'ROW (' . $formatter->formatSerializablesList($this->contents) . ')';
    }

}
