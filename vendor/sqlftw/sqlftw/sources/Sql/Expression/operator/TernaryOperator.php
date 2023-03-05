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
 * left BETWEEN middle AND right
 * left LIKE middle ESCAPE right
 */
class TernaryOperator implements OperatorExpression
{

    private ExpressionNode $left;

    private Operator $leftOperator;

    private ExpressionNode $middle;

    private Operator $rightOperator;

    private ExpressionNode $right;

    public function __construct(
        ExpressionNode $left,
        Operator $leftOperator,
        ExpressionNode $middle,
        Operator $rightOperator,
        ExpressionNode $right
    ) {
        Operator::checkTernary($leftOperator, $rightOperator);

        $this->left = $left;
        $this->leftOperator = $leftOperator;
        $this->middle = $middle;
        $this->rightOperator = $rightOperator;
        $this->right = $right;
    }

    public function getLeft(): ExpressionNode
    {
        return $this->left;
    }

    public function getLeftOperator(): Operator
    {
        return $this->leftOperator;
    }

    public function getMiddle(): ExpressionNode
    {
        return $this->middle;
    }

    public function getRightOperator(): Operator
    {
        return $this->rightOperator;
    }

    public function getRight(): ExpressionNode
    {
        return $this->right;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->left->serialize($formatter) . ' ' . $this->leftOperator->serialize($formatter) . ' ' .
            $this->middle->serialize($formatter) . ' ' . $this->rightOperator->serialize($formatter) . ' ' .
            $this->right->serialize($formatter);
    }

}
