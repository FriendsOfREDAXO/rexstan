<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Insert;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dml\OptimizerHint\OptimizerHint;
use SqlFtw\Sql\Dml\Query\Query;
use SqlFtw\Sql\Expression\ColumnIdentifier;
use SqlFtw\Sql\Expression\ObjectIdentifier;

class ReplaceSelectCommand extends InsertOrReplaceCommand implements ReplaceCommand
{

    private Query $query;

    /**
     * @param list<ColumnIdentifier>|null $columns
     * @param non-empty-list<string>|null $partitions
     * @param non-empty-list<OptimizerHint>|null $optimizerHints
     */
    public function __construct(
        ObjectIdentifier $table,
        Query $query,
        ?array $columns = null,
        ?array $partitions = null,
        ?InsertPriority $priority = null,
        bool $ignore = false,
        ?array $optimizerHints = null
    ) {
        parent::__construct($table, $columns, $partitions, $priority, $ignore, $optimizerHints);

        $this->query = $query;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'REPLACE' . $this->serializeBody($formatter) . ' ' . $this->query->serialize($formatter);
    }

}
