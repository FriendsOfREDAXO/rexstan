<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Enum;

use Dogma\InvalidRegularExpressionException;
use Dogma\LogicException;
use function in_array;
use function preg_match;

abstract class PartialStringEnum extends StringEnum
{

    public static function isKnownValue(string $value): bool
    {
        return in_array($value, static::getAllowedValues(), true);
    }

    public static function validateValue(string &$value): bool
    {
        $regexp = '/^' . static::getValueRegexp() . '?$/';
        $result = preg_match($regexp, $value);
        if ($result === false) {
            throw new InvalidRegularExpressionException($regexp);
        }
        return (bool) $result;
    }

    public static function getValueRegexp(): string
    {
        $class = static::class;

        throw new LogicException(
            "Validation rule cannot be created automatically for class $class. Reimplement the validateValue() or getValueRegexp() method."
        );
    }

}
