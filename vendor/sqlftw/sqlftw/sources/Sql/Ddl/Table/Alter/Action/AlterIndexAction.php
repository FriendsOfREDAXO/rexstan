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

class AlterIndexAction implements IndexAction
{

    private string $index;

    private bool $visible;

    public function __construct(string $index, bool $visible)
    {
        $this->index = $index;
        $this->visible = $visible;
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    public function visible(): bool
    {
        return $this->visible;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'ALTER INDEX ' . $formatter->formatName($this->index) . ($this->visible ? ' VISIBLE' : ' INVISIBLE');
    }

}
