<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Routine;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Statement;

class DropProcedureCommand extends Statement implements StoredProcedureCommand, DropRoutineCommand
{

    private ObjectIdentifier $procedure;

    private bool $ifExists;

    public function __construct(ObjectIdentifier $procedure, bool $ifExists = false)
    {
        $this->procedure = $procedure;
        $this->ifExists = $ifExists;
    }

    public function getFunction(): ObjectIdentifier
    {
        return $this->procedure;
    }

    public function ifExists(): bool
    {
        return $this->ifExists;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'DROP PROCEDURE ';
        if ($this->ifExists) {
            $result .= 'IF EXISTS ';
        }
        $result .= $this->procedure->serialize($formatter);

        return $result;
    }

}
