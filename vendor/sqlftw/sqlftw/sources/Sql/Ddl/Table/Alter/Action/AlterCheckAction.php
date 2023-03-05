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

class AlterCheckAction implements CheckAction
{

    private string $check;

    private bool $enforced;

    public function __construct(string $check, bool $enforced)
    {
        $this->check = $check;
        $this->enforced = $enforced;
    }

    public function getCheck(): string
    {
        return $this->check;
    }

    public function isEnforced(): bool
    {
        return $this->enforced;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'ALTER CHECK ' . $formatter->formatName($this->check);
        $result .= $this->enforced ? ' ENFORCED' : ' NOT ENFORCED';

        return $result;
    }

}
