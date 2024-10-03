<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\StatementImpl;

class DropTableCommand extends StatementImpl implements DdlTablesCommand
{

    /** @var non-empty-list<ObjectIdentifier> */
    private array $names;

    private bool $temporary;

    private bool $ifExists;

    private ?bool $cascadeRestrict;

    /**
     * @param non-empty-list<ObjectIdentifier> $names
     */
    public function __construct(
        array $names,
        bool $temporary = false,
        bool $ifExists = false,
        ?bool $cascadeRestrict = null
    ) {
        $this->names = $names;
        $this->temporary = $temporary;
        $this->ifExists = $ifExists;
        $this->cascadeRestrict = $cascadeRestrict;
    }

    /**
     * @return non-empty-list<ObjectIdentifier>
     */
    public function getTables(): array
    {
        return $this->names;
    }

    public function getTemporary(): bool
    {
        return $this->temporary;
    }

    public function ifExists(): bool
    {
        return $this->ifExists;
    }

    public function cascade(): bool
    {
        return $this->cascadeRestrict === true;
    }

    public function restrict(): bool
    {
        return $this->cascadeRestrict === false;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'DROP ';
        if ($this->temporary) {
            $result .= 'TEMPORARY ';
        }
        $result .= 'TABLE ';
        if ($this->ifExists) {
            $result .= 'IF EXISTS ';
        }

        $result .= $formatter->formatSerializablesList($this->names);

        if ($this->cascadeRestrict === true) {
            $result .= ' CASCADE';
        } elseif ($this->cascadeRestrict === false) {
            $result .= ' RESTRICT';
        }

        return $result;
    }

}
