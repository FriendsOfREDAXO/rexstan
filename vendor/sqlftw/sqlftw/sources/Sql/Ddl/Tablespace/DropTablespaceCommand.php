<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Tablespace;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\Table\Option\StorageEngine;
use SqlFtw\Sql\Statement;

class DropTablespaceCommand extends Statement implements TablespaceCommand
{

    private string $tablespace;

    private ?StorageEngine $engine;

    private bool $undo;

    public function __construct(string $tablespace, ?StorageEngine $engine = null, bool $undo = false)
    {
        $this->tablespace = $tablespace;
        $this->engine = $engine;
        $this->undo = $undo;
    }

    public function getTablespace(): string
    {
        return $this->tablespace;
    }

    public function getEngine(): ?StorageEngine
    {
        return $this->engine;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'DROP ';
        if ($this->undo) {
            $result .= 'UNDO ';
        }
        $result .= 'TABLESPACE ' . $formatter->formatName($this->tablespace);

        if ($this->engine !== null) {
            $result .= ' ENGINE ' . $this->engine->serialize($formatter);
        }

        return $result;
    }

}
