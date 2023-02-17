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
use SqlFtw\Sql\Statement;

class CheckTableCommand extends Statement implements DalTablesCommand
{

    /** @var non-empty-list<ObjectIdentifier> */
    private array $names;

    private ?CheckTableOption $option;

    /**
     * @param non-empty-list<ObjectIdentifier> $names
     */
    public function __construct(array $names, ?CheckTableOption $option = null)
    {
        $this->names = $names;
        $this->option = $option;
    }

    /**
     * @return non-empty-list<ObjectIdentifier>
     */
    public function getTables(): array
    {
        return $this->names;
    }

    public function getOption(): ?CheckTableOption
    {
        return $this->option;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CHECK TABLE ' . $formatter->formatSerializablesList($this->names);

        if ($this->option !== null) {
            $result .= ' ' . $this->option->serialize($formatter);
        }

        return $result;
    }

}
