<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Handler;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Statement;

class HandlerCloseCommand extends Statement implements HandlerCommand
{

    private ObjectIdentifier $table;

    public function __construct(ObjectIdentifier $table)
    {
        $this->table = $table;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->table;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'HANDLER ' . $this->table->serialize($formatter) . ' CLOSE';
    }

}
