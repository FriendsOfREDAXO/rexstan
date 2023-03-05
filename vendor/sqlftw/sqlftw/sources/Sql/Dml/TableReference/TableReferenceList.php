<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\TableReference;

use Countable;
use SqlFtw\Formatter\Formatter;
use function count;

class TableReferenceList implements TableReferenceNode, Countable
{

    /** @var non-empty-list<TableReferenceNode> */
    private array $references;

    /**
     * @param non-empty-list<TableReferenceNode> $references
     */
    public function __construct(array $references)
    {
        $this->references = $references;
    }

    public function count(): int
    {
        return count($this->references);
    }

    /**
     * @return non-empty-list<TableReferenceNode>
     */
    public function getReferences(): array
    {
        return $this->references;
    }

    public function serialize(Formatter $formatter): string
    {
        return $formatter->formatSerializablesList($this->references);
    }

}
