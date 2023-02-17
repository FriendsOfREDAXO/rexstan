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
 * left AND right
 * left OR right
 * left XOR right
 * left && right
 * left || right
 * left + right
 * left - right
 * left * right
 * left / right
 * left DIV right
 * left MOD right
 * left % right
 * left & right
 * left | right
 * left ^ right
 * left << right
 * left >> right
 * left IS [NOT] right
 * left [NOT] LIKE right
 * left [NOT] REGEXP right
 * left [NOT] RLIKE right
 * left SOUNDS LIKE right
 * left -> right
 * left ->> right
 */
class BinaryOperator implements OperatorExpression
{

    private RootNode $left;

    private Operator $operator;

    private RootNode $right;

    public function __construct(RootNode $left, Operator $operator, RootNode $right)
    {
        $operator->checkBinary();

        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    public function getLeft(): RootNode
    {
        return $this->left;
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
        return $this->left->serialize($formatter) . ' '
            . $this->operator->serialize($formatter) . ' '
            . $this->right->serialize($formatter);
    }

}
