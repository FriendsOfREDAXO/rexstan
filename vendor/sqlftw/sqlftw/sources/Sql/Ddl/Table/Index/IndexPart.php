<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Index;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\Order;
use SqlFtw\Sql\SqlSerializable;
use function is_string;

class IndexPart implements SqlSerializable
{

    /** @var string|RootNode */
    private $expression;

    private ?int $length;

    private ?Order $order;

    /**
     * @param string|RootNode $expression
     */
    public function __construct($expression, ?int $length = null, ?Order $order = null)
    {
        if (!is_string($expression) && $length !== null) {
            throw new InvalidDefinitionException('Only column names can have length.');
        }

        $this->expression = $expression;
        $this->length = $length;
        $this->order = $order;
    }

    public static function getDefaultOrder(): Order
    {
        return new Order(Order::ASC);
    }

    /**
     * @return string|RootNode
     */
    public function getExpression()
    {
        return $this->expression;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = is_string($this->expression)
            ? $formatter->formatName($this->expression)
            : '(' . $this->expression->serialize($formatter) . ')';

        if ($this->length !== null) {
            $result .= '(' . $this->length . ')';
        }
        if ($this->order !== null) {
            $result .= ' ' . $this->order->serialize($formatter);
        }

        return $result;
    }

}
