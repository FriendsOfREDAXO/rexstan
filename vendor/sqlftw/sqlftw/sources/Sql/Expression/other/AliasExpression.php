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
 * expression AS name
 */
class AliasExpression implements ArgumentNode
{

    private ExpressionNode $expression;

    private string $alias;

    public function __construct(ExpressionNode $expression, string $alias)
    {
        $this->expression = $expression;
        $this->alias = $alias;
    }

    public function getExpression(): ExpressionNode
    {
        return $this->expression;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->expression->serialize($formatter) . ' AS ' . $formatter->formatName($this->alias);
    }

}
