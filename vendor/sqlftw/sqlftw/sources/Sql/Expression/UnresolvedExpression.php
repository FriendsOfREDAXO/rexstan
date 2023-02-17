<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Sql\Dml\Query\SelectCommand;

class UnresolvedExpression
{

    /** @var ExpressionNode|Asterisk|SelectCommand|mixed[]|null */
    private $expression;

    /**
     * @param ExpressionNode|Asterisk|SelectCommand|mixed[]|null $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return ExpressionNode|Asterisk|SelectCommand|mixed[]|null
     */
    public function getExpression()
    {
        return $this->expression;
    }

}
