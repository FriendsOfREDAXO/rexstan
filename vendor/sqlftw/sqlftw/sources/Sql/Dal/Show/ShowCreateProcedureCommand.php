<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Show;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\StatementImpl;

class ShowCreateProcedureCommand extends StatementImpl implements ShowCommand
{

    private ObjectIdentifier $procedure;

    public function __construct(ObjectIdentifier $procedure)
    {
        $this->procedure = $procedure;
    }

    public function getProcedure(): ObjectIdentifier
    {
        return $this->procedure;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'SHOW CREATE PROCEDURE ' . $this->procedure->serialize($formatter);
    }

}
