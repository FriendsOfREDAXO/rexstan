<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Formatter\Formatter;

/**
 * {type expr}
 */
class CurlyExpression implements RootNode
{

    private string $type;

    private RootNode $expression;

    public function __construct(string $type, RootNode $expression)
    {
        $this->type = $type;
        $this->expression = $expression;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getExpression(): RootNode
    {
        return $this->expression;
    }

    public function serialize(Formatter $formatter): string
    {
        return '{' . $this->type . ' ' . $this->expression->serialize($formatter) . '}';
    }

}
