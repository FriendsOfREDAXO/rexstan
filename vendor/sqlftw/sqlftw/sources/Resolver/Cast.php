<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver;

use SqlFtw\Sql\Expression\BoolValue;
use SqlFtw\Sql\Expression\IntValue;
use SqlFtw\Sql\Expression\NullLiteral;
use SqlFtw\Sql\Expression\NumericValue;
use SqlFtw\Sql\Expression\OnOffLiteral;
use SqlFtw\Sql\Expression\StringValue;
use SqlFtw\Sql\Expression\Value;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function rtrim;

class Cast
{

    /**
     * @param scalar|Value|null $value
     */
    public function toBool($value): ?bool
    {
        if ($value === null || $value instanceof NullLiteral) {
            return null;
        } elseif (is_string($value)) {
            return (bool) $value;
        } elseif (is_int($value)) {
            return (bool) $value;
        } elseif (is_float($value)) {
            return (bool) $value;
        } elseif (is_bool($value)) {
            return $value;
        } elseif ($value instanceof StringValue) {
            return (bool) $value->asString();
        } elseif ($value instanceof IntValue) {
            return (bool) $value->asInt();
        } elseif ($value instanceof NumericValue) {
            return (bool) $value->asNumber();
        } elseif ($value instanceof BoolValue) {
            return $value->asBool();
        } else {
            throw new UncastableTypeException('bool', $value);
        }
    }

    /**
     * @param scalar|Value|null $value
     */
    public function toString($value): ?string
    {
        if ($value === null || $value instanceof NullLiteral) {
            return null;
        } elseif (is_string($value)) {
            return $value;
        } elseif (is_int($value)) {
            return (string) $value;
        } elseif (is_float($value)) {
            return (string) $value;
        } elseif (is_bool($value)) {
            return $value ? '1' : '0';
        } elseif ($value instanceof StringValue) {
            return $value->asString();
        } elseif ($value instanceof IntValue) {
            return (string) $value->asInt();
        } elseif ($value instanceof NumericValue) {
            return rtrim($value->getValue(), '.');
        } elseif ($value instanceof OnOffLiteral) {
            return $value->getValue();
        } elseif ($value instanceof BoolValue) {
            $value = $value->asBool();

            return $value === null ? null : ($value ? '1' : '0');
        } else {
            throw new UncastableTypeException('string', $value);
        }
    }

    /**
     * @param scalar|Value|null $value
     */
    public function toInt($value): ?int
    {
        if ($value === null || $value instanceof NullLiteral) {
            return null;
        } elseif (is_string($value)) {
            return (int) $value;
        } elseif (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return (int) $value;
        } elseif (is_bool($value)) {
            return $value ? 1 : 0;
        } elseif ($value instanceof IntValue) {
            return $value->asInt();
        } elseif ($value instanceof NumericValue) {
            return (int) $value->asNumber();
        } elseif ($value instanceof StringValue) {
            return (int) $value->asString();
        } elseif ($value instanceof BoolValue) {
            $value = $value->asBool();

            return $value === null ? null : ($value ? 1 : 0);
        } else {
            throw new UncastableTypeException('int', $value);
        }
    }

    /**
     * @param scalar|Value|null $value
     */
    public function toFloat($value): ?float
    {
        if ($value === null || $value instanceof NullLiteral) {
            return null;
        } elseif (is_string($value)) {
            return (float) $value;
        } elseif (is_int($value)) {
            return (float) $value;
        } elseif (is_float($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return $value ? 1.0 : 0.0;
        } elseif ($value instanceof NumericValue) {
            return (float) $value->asNumber();
        } elseif ($value instanceof StringValue) {
            return (float) $value->asString();
        } elseif ($value instanceof BoolValue) {
            $value = $value->asBool();

            return $value === null ? null : ($value ? 1.0 : 0.0);
        } else {
            throw new UncastableTypeException('float', $value);
        }
    }

    /**
     * @param scalar|Value|null $value
     * @return int|float|null
     */
    public function toIntOrFloat($value)
    {
        if ($value === null || $value instanceof NullLiteral) {
            return null;
        } elseif (is_string($value)) {
            $value = (float) $value;
            if ($value === (float) (int) $value) {
                return (int) $value;
            } else {
                return $value;
            }
        } elseif (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return $value ? 1 : 0;
        } elseif ($value instanceof StringValue) {
            $value = $value->asString();

            return ((string) (int) $value) === $value ? (int) $value : (float) $value;
        } elseif ($value instanceof IntValue) {
            return $value->asInt();
        } elseif ($value instanceof NumericValue) {
            return (float) $value->asNumber();
        } elseif ($value instanceof BoolValue) {
            $value = $value->asBool();

            return $value === null ? null : ($value ? 1 : 0);
        } else {
            throw new UncastableTypeException('int|float', $value);
        }
    }

    /**
     * @param scalar|Value|null $value
     * @return int|string|null
     */
    public function toIntOrString($value)
    {
        if ($value === null || $value instanceof NullLiteral) {
            return null;
        } elseif (is_string($value)) {
            if ($value === (string) (int) $value) {
                return (int) $value;
            } else {
                return $value;
            }
        } elseif (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return (int) $value;
        } elseif (is_bool($value)) {
            return $value ? 1 : 0;
        } elseif ($value instanceof IntValue) {
            return $value->asInt();
        } elseif ($value instanceof NumericValue) {
            return (int) $value->asNumber();
        } elseif ($value instanceof StringValue) {
            return $value->asString();
        } elseif ($value instanceof BoolValue) {
            $value = $value->asBool();

            return $value === null ? null : ($value ? 1 : 0);
        } else {
            throw new UncastableTypeException('int|string', $value);
        }
    }

    /**
     * @param scalar|Value|null $value
     * @return scalar|null
     */
    public function toScalar($value)
    {
        if ($value === null || $value instanceof NullLiteral) {
            return null;
        } elseif (is_string($value)) {
            return $value;
        } elseif (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return $value;
        } elseif ($value instanceof IntValue) {
            return $value->asInt();
        } elseif ($value instanceof NumericValue) {
            return $value->asNumber();
        } elseif ($value instanceof StringValue) {
            return $value->asString();
        } elseif ($value instanceof BoolValue) {
            return $value->asBool();
        } else {
            throw new UncastableTypeException('scalar', $value);
        }
    }

    /**
     * Casting for bitwise operations
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     * @return array{int|null, int|null}|array{string|null, string|null}
     */
    public function toStringOrIntPair($left, $right): array
    {
        if (is_string($left) || is_string($right) || $left instanceof StringValue || $right instanceof StringValue) {
            $left = $this->toString($left);
            $right = $this->toString($right);
        } else {
            $left = $this->toInt($left);
            $right = $this->toInt($right);
        }

        return [$left, $right];
    }

    /**
     * Casting for comparison operations
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     * @return array{int|float|null, int|float|null}|array{string|null, string|null}
     */
    public function toNumberOrStringPair($left, $right): array
    {
        if (is_float($left) || is_float($right) || ($left instanceof NumericValue && !$left instanceof IntValue) || ($right instanceof NumericValue && !$right instanceof IntValue)) {
            $left = $this->toFloat($left);
            $right = $this->toFloat($right);
        } elseif (is_int($left) || is_int($right)) {
            $left = $this->toInt($left);
            $right = $this->toInt($right);
        } else {
            // todo: binary vs textual
            $left = $this->toString($left);
            $right = $this->toString($right);
        }

        return [$left, $right];
    }

}
