<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Flush;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dal\DalCommand;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Statement;

class FlushTablesCommand extends Statement implements DalCommand
{

    /** @var non-empty-list<ObjectIdentifier>|null */
    private ?array $tables;

    private bool $withReadLock;

    private bool $forExport;

    private bool $local;

    /**
     * @param non-empty-list<ObjectIdentifier>|null $tables
     */
    public function __construct(
        ?array $tables = null,
        bool $withReadLock = false,
        bool $forExport = false,
        bool $local = false
    ) {
        $this->tables = $tables;
        $this->withReadLock = $withReadLock;
        $this->forExport = $forExport;
        $this->local = $local;
    }

    /**
     * @return non-empty-list<ObjectIdentifier>|null
     */
    public function getTables(): ?array
    {
        return $this->tables;
    }

    public function withReadLock(): bool
    {
        return $this->withReadLock;
    }

    public function forExport(): bool
    {
        return $this->forExport;
    }

    public function isLocal(): bool
    {
        return $this->local;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'FLUSH ';
        if ($this->isLocal()) {
            $result .= 'LOCAL ';
        }
        $result .= 'TABLES';

        if ($this->tables !== null) {
            $result .= ' ' . $formatter->formatSerializablesList($this->tables);
        }
        if ($this->withReadLock) {
            $result .= ' WITH READ LOCK';
        }
        if ($this->forExport) {
            $result .= ' FOR EXPORT';
        }

        return $result;
    }

}
