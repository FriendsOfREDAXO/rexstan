<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

class OperatorPrioritySolver
{

    /**
     * priority:
     *   ^
     *   *, /, DIV, %, MOD
     *   -, +
     *   <<, >>
     *   &
     *   |
     */
    public function orderArithmeticOperators(RootNode $node): RootNode
    {
        // todo: operator priority
        return $node;
    }

    /**
     * priority:
     *   NOT
     *   AND, &&
     *   XOR
     *   OR, ||
     */
    public function orderLogicOperators(RootNode $node): RootNode
    {
        // todo: operator priority
        return $node;
    }

}
