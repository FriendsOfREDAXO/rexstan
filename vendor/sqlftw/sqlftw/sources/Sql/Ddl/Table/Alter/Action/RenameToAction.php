<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Alter\Action;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;

class RenameToAction implements TableAction
{

    private ObjectIdentifier $newName;

    public function __construct(ObjectIdentifier $newName)
    {
        $this->newName = $newName;
    }

    public function getNewName(): ObjectIdentifier
    {
        return $this->newName;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'RENAME TO ' . $this->newName->serialize($formatter);
    }

}
