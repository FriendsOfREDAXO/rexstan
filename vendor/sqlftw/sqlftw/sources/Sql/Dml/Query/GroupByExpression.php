<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Query;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ExpressionNode;
use SqlFtw\Sql\Order;
use SqlFtw\Sql\SqlSerializable;

class GroupByExpression implements SqlSerializable
{

    private ExpressionNode $expression;

    private ?Order $order;

    public function __construct(ExpressionNode $expression, ?Order $order = null)
    {
        $this->expression = $expression;
        $this->order = $order;
    }

    public function getExpression(): ExpressionNode
    {
        return $this->expression;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->expression->serialize($formatter);
        if ($this->order !== null) {
            $result .= ' ' . $this->order->serialize($formatter);
        }

        return $result;
    }

}
