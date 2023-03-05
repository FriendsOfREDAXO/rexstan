<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Analyzer\Types;

use LogicException;
use SqlFtw\Resolver\Cast;
use SqlFtw\Resolver\UncastableTypeException;
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Collation;
use SqlFtw\Sql\Ddl\Table\Option\StorageEngine;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\OnOffLiteral;
use SqlFtw\Sql\Expression\Value;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlMode;
use function assert;
use function class_exists;
use function count;
use function explode;
use function in_array;
use function interface_exists;
use function is_array;
use function is_bool;
use function is_int;
use function is_numeric;
use function is_string;
use function strtoupper;

class CastingTypeChecker
{

    /**
     * @param scalar|Value|list<scalar>|null $value
     * @param list<int|string>|null $values
     */
    public function canBeCastedTo($value, string $type, ?array $values, Cast $cast): bool
    {
        // hack for "SET foo = (SELECT ...)"
        if (is_array($value)) {
            if (count($value) === 1) {
                $value = $value[0];
            } else {
                throw new LogicException('Array types are not supported in CastingTypeChecker.');
            }
        }

        try {
            switch ($type) {
                case BaseType::ENUM:
                    assert($values !== null);
                    return $this->validateEnum($value, $values, $cast);
                case BaseType::SET:
                    assert($values !== null);
                    return $this->validateSet($value, $values, $cast);
                case BaseType::CHAR:
                    $value = $cast->toString($value);

                    return is_string($value);
                case BaseType::SIGNED:
                    $value = $cast->toInt($value);

                    return is_int($value);
                case BaseType::UNSIGNED:
                    $value = $cast->toInt($value);

                    return is_int($value) && $value >= 0;
                case BaseType::FLOAT:
                case BaseType::DECIMAL:
                case BaseType::NUMERIC:
                    $value = $cast->toFloat($value);

                    return is_numeric($value);
                case BaseType::BOOL:
                    if (is_int($value) && ($value < 0 || $value > 1)) {
                        return false;
                    } elseif (is_string($value)) {
                        $upper = strtoupper($value);

                        return $upper === 'TRUE' || $upper === 'FALSE' || $upper === 'ON' || $upper === 'OFF';/* || $upper === 'YES' || $upper === 'NO'*/
                    }
                    $value = $cast->toBool($value);

                    return is_bool($value);
                case Charset::class:
                    return $value instanceof Charset
                        || (is_string($value) && Charset::validateValue($value))
                        || (is_int($value) && $value > 0)
                        || $value === true; // true -> 1
                case Collation::class:
                    return $value instanceof Collation
                        || (is_string($value) && Collation::validateValue($value))
                        || (is_int($value) && $value > 0)
                        || $value === true; // true -> 1
                case StorageEngine::class:
                    return $value instanceof StorageEngine
                        || (is_string($value) && StorageEngine::validateValue($value))
                        || (is_int($value) && $value > 0)
                        || $value === true; // true -> 1
                case SqlMode::class:
                    if ($value instanceof SqlMode || is_bool($value)) {
                        return true;
                    } elseif (is_string($value)) {
                        try {
                            SqlMode::getFromString($value);

                            return true;
                        } catch (InvalidDefinitionException $e) {
                            return false;
                        }
                    } elseif (is_int($value)) {
                        try {
                            SqlMode::getFromInt($value);

                            return true;
                        } catch (InvalidDefinitionException $e) {
                            return false;
                        }
                    }
                default:
                    if (class_exists($type) || interface_exists($type)) {
                        return $value instanceof $type;
                    } else {
                        throw new LogicException("Unexpected type '$type' in CastingTypeChecker.");
                    }
            }
        } catch (UncastableTypeException $e) {
            return false;
        }
    }

    /**
     * @param scalar|Value|null $value
     * @param list<int|string> $values
     */
    public function validateEnum($value, array $values, Cast $cast): bool
    {
        if ($value instanceof OnOffLiteral) {
            $value = $value->getValue();
        }
        if (is_bool($value) || $value === null) {
            $value = (int) $value;
        }
        $str = strtoupper((string) $cast->toString($value));

        if (in_array($str, $values, true)) {
            // string values
            return true;
        } elseif (is_int($value) && $value >= 0 && $value < count($values)) {
            // index (0, 1, 2, 3...)
            return true;
        } elseif (is_numeric($value)) {
            // numeric values
            $int = (int) $value;
            if ($value === ((string) $int) && in_array($int, $values, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param scalar|Value|null $value
     * @param list<int|string> $values
     */
    public function validateSet($value, array $values, Cast $cast): bool
    {
        if (is_bool($value) || $value === null) {
            $value = (int) $value;
        }
        $str = strtoupper((string) $cast->toString($value));

        if (is_int($value) && $value >= 0 && $value < (2 ** count($values))) {
            // empty (0) or indexes (1, 2, 4, 8...)
            return true;
        }
        $vals = explode(',', $str);
        foreach ($vals as $val) {
            // string values, including empty ("")
            if ($val === '') {
                continue;
            } elseif (!in_array($val, $values, true)) {
                return false;
            }
        }

        return true;
    }

}
