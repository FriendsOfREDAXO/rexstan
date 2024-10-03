<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Resource;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\StatementImpl;

class DropResourceGroupCommand extends StatementImpl implements ResourceGroupCommand
{

    private string $name;

    private bool $force;

    public function __construct(string $name, bool $force = false)
    {
        $this->name = $name;
        $this->force = $force;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function force(): bool
    {
        return $this->force;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'DROP RESOURCE GROUP ' . $formatter->formatName($this->name) . ($this->force ? ' FORCE' : '');
    }

}
