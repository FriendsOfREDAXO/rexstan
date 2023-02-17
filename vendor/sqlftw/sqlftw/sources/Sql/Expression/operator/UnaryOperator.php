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
 * +right
 * -right
 * ~right
 * !right
 * NOT right
 * BINARY right
 */
class UnaryOperator implements OperatorExpression
{

    private Operator $operator;

    private RootNode $right;

    public function __construct(Operator $operator, RootNode $right)
    {
        $operator->checkUnary();

        $this->operator = $operator;
        $this->right = $right;
    }

    public function getOperator(): Operator
    {
        return $this->operator;
    }

    public function getRight(): RootNode
    {
        return $this->right;
    }

    public function serialize(Formatter $formatter): string
    {
        $isSymbol = !$this->operator->equalsAnyValue(Operator::NOT, Operator::BINARY);
        return $this->operator->serialize($formatter) . ($isSymbol ? '' : ' ') . $this->right->serialize($formatter);
    }

}
