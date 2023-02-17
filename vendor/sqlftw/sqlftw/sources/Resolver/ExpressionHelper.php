<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver;

use SqlFtw\Sql\Expression\Asterisk;
use SqlFtw\Sql\Expression\ExpressionNode;
use SqlFtw\Sql\Expression\UnresolvedExpression;
use SqlFtw\Sql\Expression\Value;
use function is_array;
use function is_scalar;

/**
 * @phpstan-type ExpressionOrValue scalar|ExpressionNode|Asterisk|mixed[]|null
 */
class ExpressionHelper
{

    /**
     * @param scalar|ExpressionNode|Asterisk|mixed[]|null $value
     */
    public static function isValue($value): bool
    {
        return $value === null || is_scalar($value) || $value instanceof Value;
    }

    /**
     * @param scalar|ExpressionNode|Asterisk|mixed[]|null $value
     */
    public static function isValueOrArray($value): bool
    {
        return $value === null || is_scalar($value) || is_array($value) || $value instanceof Value;
    }

    /**
     * @param scalar|ExpressionNode|Asterisk|mixed[]|null $value
     * @return scalar|Value|UnresolvedExpression|null
     */
    public static function valueOrUnresolved($value)
    {
        if ($value === null || is_scalar($value) || $value instanceof Value) {
            return $value;
        } else {
            return new UnresolvedExpression($value);
        }
    }

}
