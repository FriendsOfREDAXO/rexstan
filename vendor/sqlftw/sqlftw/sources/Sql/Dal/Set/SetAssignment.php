<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Set;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\Identifier;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\SqlSerializable;

class SetAssignment implements SqlSerializable
{

    private Identifier $variable;

    private RootNode $expression;

    private string $operator;

    public function __construct(Identifier $variable, RootNode $expression, string $operator = Operator::EQUAL)
    {
        $this->variable = $variable;
        $this->expression = $expression;
        $this->operator = $operator;
    }

    public function getVariable(): Identifier
    {
        return $this->variable;
    }

    public function getExpression(): RootNode
    {
        return $this->expression;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->variable->serialize($formatter) . ' ' . $this->operator . ' ' . $this->expression->serialize($formatter);
    }

}
