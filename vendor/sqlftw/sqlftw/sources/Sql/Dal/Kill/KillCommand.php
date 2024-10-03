<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Kill;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dal\DalCommand;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\StatementImpl;

class KillCommand extends StatementImpl implements DalCommand
{

    private RootNode $processId;

    public function __construct(RootNode $processId)
    {
        $this->processId = $processId;
    }

    public function getProcessId(): RootNode
    {
        return $this->processId;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'KILL ' . $this->processId->serialize($formatter);
    }

}
