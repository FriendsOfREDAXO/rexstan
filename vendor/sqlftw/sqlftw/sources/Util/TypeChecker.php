<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Util;

use Dogma\Str;
use LogicException;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\InvalidDefinitionException;
use function assert;
use function class_exists;
use function ctype_digit;
use function explode;
use function get_class;
use function gettype;
use function in_array;
use function interface_exists;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_scalar;
use function is_string;
use function preg_match;
use function substr;

/**
 * Used to check types of misc arguments that can not be checked statically yet
 *
 * SQL types:
 * - CHAR for strings
 * - BOOL for booleans
 * - NUMERIC for integers and floats
 * - SIGNED for signed integers
 * - UNSIGNED for unsigned integers
 * - FLOAT for floating point numbers
 * - DECIMAL for decimal numbers
 *
 * Suffix with "[]" for `array<T>` and with "{}" for `array<non-empty-string, T>`
 *
 * Multiple types separated by "|"
 */
class TypeChecker
{

    /**
     * @param string|list<string|int> $types
     * @param mixed $value
     */
    public static function check($value, $types, ?string $field = null): void
    {
        if (is_array($types)) {
            if (!in_array($value, $types, true)) {
                assert(is_scalar($value));
                throw new InvalidDefinitionException("Value should be one of '" . Str::join($types, ', ', ' or ') . "', but '$value' received.");
            }

            return;
        }

        $ok = false;
        foreach (explode('|', $types) as $type) {
            $array = Str::endsWith($type, '[]');
            $map = Str::endsWith($type, '{}');
            if ($array || $map) {
                $t = substr($type, 0, -2);
                if (is_array($value)) {
                    $itemsOk = true;
                    foreach ($value as $k => $v) {
                        if ($map && (!is_string($k) || $k === '')) {
                            $itemsOk = false;
                            break;
                        }
                        if (!self::checkValue($v, $t)) {
                            $itemsOk = false;
                            break;
                        }
                    }
                    if ($itemsOk) {
                        $ok = true;
                        break;
                    }
                }
            } elseif (self::checkValue($value, $type)) {
                $ok = true;
            }
        }

        if (!$ok) {
            $realValue = is_array($value) ? 'array<...>' : (is_scalar($value) ? (string) $value : null);
            $realType = is_object($value) ? get_class($value) : gettype($value);
            if ($field !== null) {
                if ($realValue !== null) {
                    throw new InvalidDefinitionException("Value of field '$field' should be of type '$types', but '$realValue' received.");
                } else {
                    throw new InvalidDefinitionException("Value of field '$field' should be of type '$types', but value of type '$realType' received.");
                }
            } else {
                if ($realValue !== null) {
                    throw new InvalidDefinitionException("Value should be of type '$types', but '$realValue' received.");
                } else {
                    throw new InvalidDefinitionException("Value should be of type '$types', but value of type '$realType' received.");
                }
            }
        }
    }

    /**
     * @param mixed $value
     */
    private static function checkValue($value, string $type): bool
    {
        switch ($type) {
            case BaseType::CHAR:
                return is_string($value);
            case BaseType::SIGNED:
                // 64bit uint may be represented as string
                return is_int($value) || (is_string($value) && preg_match('~^(?:0|-?[1-9][0-9]*)$~', $value) !== 0);
            case BaseType::UNSIGNED:
                // 64bit uint may be represented as string
                return (is_int($value) && $value >= 0) || (is_string($value) && ctype_digit($value));
            case BaseType::FLOAT:
                return is_float($value);
            case BaseType::NUMERIC:
                return is_int($value) || is_float($value) || (is_string($value) && preg_match('~[0-9]+(\\.[0-9]+)~', $value) === 0);
            case BaseType::DECIMAL:
                return is_string($value) && preg_match('~[0-9]+(\\.[0-9]+)~', $value) === 0;
            case BaseType::BOOL:
                return is_bool($value);
            default:
                if (class_exists($type) || interface_exists($type)) {
                    return $value instanceof $type;
                } else {
                    throw new LogicException("Unexpected type '$type' in TypeChecker.");
                }
        }
    }

}
