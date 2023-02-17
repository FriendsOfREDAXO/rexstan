<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Constraint;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\Table\TableItem;
use SqlFtw\Sql\Expression\RootNode;

class CheckDefinition implements TableItem, ConstraintBody
{

    private RootNode $expression;

    private ?bool $enforced;

    public function __construct(RootNode $expression, ?bool $enforced = null)
    {
        $this->expression = $expression;
        $this->enforced = $enforced;
    }

    public function duplicateWithEnforced(bool $enforced): self
    {
        $that = clone $this;
        $that->enforced = $enforced;

        return $that;
    }

    public function getExpression(): RootNode
    {
        return $this->expression;
    }

    public function isEnforced(): ?bool
    {
        return $this->enforced;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CHECK (' . $this->expression->serialize($formatter) . ')';

        if ($this->enforced !== null) {
            $result .= ' ' . ($this->enforced ? 'ENFORCED' : 'NOT ENFORCED');
        }

        return $result;
    }

}
