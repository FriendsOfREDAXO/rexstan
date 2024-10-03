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

class OptimizeTableCommand extends StatementImpl implements DalTablesCommand
{

    /** @var non-empty-list<ObjectIdentifier> */
    private array $names;

    private bool $local;

    /**
     * @param non-empty-list<ObjectIdentifier> $names
     */
    public function __construct(array $names, bool $local = false)
    {
        $this->names = $names;
        $this->local = $local;
    }

    /**
     * @return non-empty-list<ObjectIdentifier>
     */
    public function getTables(): array
    {
        return $this->names;
    }

    public function isLocal(): bool
    {
        return $this->local;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'OPTIMIZE';
        if ($this->local) {
            $result .= ' LOCAL';
        }
        $result .= ' TABLE ' . $formatter->formatSerializablesList($this->names);

        return $result;
    }

}
