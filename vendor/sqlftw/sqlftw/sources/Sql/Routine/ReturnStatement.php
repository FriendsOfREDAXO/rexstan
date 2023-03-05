<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Routine;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\Statement;

class ReturnStatement extends Statement implements SqlSerializable
{

    private RootNode $expression;

    public function __construct(RootNode $expression)
    {
        $this->expression = $expression;
    }

    public function getExpression(): RootNode
    {
        return $this->expression;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'RETURN ' . $this->expression->serialize($formatter);
    }

}
