<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ColumnIdentifier;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;
use function get_class;
use function gettype;
use function is_object;
use function is_scalar;
use function ucfirst;

class Assignment implements SqlSerializable
{

    private ColumnIdentifier $variable;

    /** @var scalar|RootNode */
    private $expression;

    /**
     * @param scalar|RootNode $expression
     */
    public function __construct(ColumnIdentifier $variable, $expression)
    {
        if (!$expression instanceof RootNode && !is_scalar($expression)) {
            $given = is_object($expression) ? get_class($expression) : ucfirst(gettype($expression));
            throw new InvalidDefinitionException("ExpressionNode assigned to variable must be a scalar value or an ExpressionNode. $given given.");
        }
        $this->variable = $variable;
        $this->expression = $expression;
    }

    public function getVariable(): ColumnIdentifier
    {
        return $this->variable;
    }

    /**
     * @return scalar|RootNode
     */
    public function getExpression()
    {
        return $this->expression;
    }

    public function serialize(Formatter $formatter): string
    {
        $value = $this->expression instanceof RootNode
            ? $this->expression->serialize($formatter)
            : $formatter->formatValue($this->expression);

        return $this->variable->serialize($formatter) . ' = ' . $value;
    }

}
