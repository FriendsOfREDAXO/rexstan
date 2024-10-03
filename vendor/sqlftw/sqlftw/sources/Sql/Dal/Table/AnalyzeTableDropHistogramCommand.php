<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Table;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\StatementImpl;

class AnalyzeTableDropHistogramCommand extends StatementImpl implements DalTablesCommand
{

    /** @var non-empty-list<ObjectIdentifier> */
    private array $tables;

    /** @var non-empty-list<string> */
    private array $columns;

    private bool $local;

    /**
     * @param non-empty-list<ObjectIdentifier> $tables
     * @param non-empty-list<string> $columns
     */
    public function __construct(array $tables, array $columns, bool $local = false)
    {
        $this->tables = $tables;
        $this->columns = $columns;
        $this->local = $local;
    }

    /**
     * @return non-empty-list<ObjectIdentifier>
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @return non-empty-list<string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function isLocal(): bool
    {
        return $this->local;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'ANALYZE';
        if ($this->local) {
            $result .= ' LOCAL';
        }
        $result .= ' TABLE ' . $formatter->formatSerializablesList($this->tables)
            . ' DROP HISTOGRAM ON ' . $formatter->formatNamesList($this->columns);

        return $result;
    }

}
