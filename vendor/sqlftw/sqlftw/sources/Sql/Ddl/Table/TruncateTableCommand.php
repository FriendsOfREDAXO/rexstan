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

class TruncateTableCommand extends StatementImpl implements DdlTableCommand
{

    private ObjectIdentifier $name;

    public function __construct(ObjectIdentifier $name)
    {
        $this->name = $name;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->name;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'TRUNCATE TABLE ' . $this->name->serialize($formatter);
    }

}
