<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

/**
 * Special function argument values invalid in other places of expressions
 *
 * e.g. CastType, Collation etc.
 */
interface ArgumentValue extends ExpressionNode
{

}
