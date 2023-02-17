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

class AlterConstraintAction implements ConstraintAction
{

    private string $constraint;

    private bool $enforced;

    public function __construct(string $constraint, bool $enforced)
    {
        $this->constraint = $constraint;
        $this->enforced = $enforced;
    }

    public function getConstraint(): string
    {
        return $this->constraint;
    }

    public function isEnforced(): bool
    {
        return $this->enforced;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'ALTER CONSTRAINT ' . $formatter->formatName($this->constraint);
        $result .= $this->enforced ? ' ENFORCED' : ' NOT ENFORCED';

        return $result;
    }

}
