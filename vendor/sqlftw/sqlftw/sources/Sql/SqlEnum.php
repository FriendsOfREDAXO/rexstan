<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

use function strtolower;

/**
 * Case-insensitive StringEnum
 */
abstract class SqlEnum extends StringEnum
{

    /** @var array<class-string, array<string, string>> */
    private static array $lowerValues = [];

    protected static function validateValue(string &$value): bool
    {
        $class = static::class;

        // create lower-case index
        if (!isset(self::$lowerValues[$class])) {
            $values = [];
            foreach (self::getAllowedValues() as $val) {
                $values[strtolower($val)] = $val;
            }
            self::$lowerValues[$class] = $values;
        }

        $lower = strtolower($value);
        $values = self::$lowerValues[$class];
        if (isset($values[$lower])) {
            $value = $values[$lower];
        }

        return parent::validateValue($value);
    }

}
