<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver\Functions;

use Dogma\Math\IntCalc;
use LogicException;
use SqlFtw\Resolver\ResolveException;
use SqlFtw\Sql\Expression\Value;
use function is_string;
use function strlen;

trait FunctionsBitwise
{

    /**
     * & - Bitwise AND
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     * @return int|string|null
     */
    public function _bit_and($left, $right)
    {
        [$left, $right] = $this->cast->toStringOrIntPair($left, $right);

        if ($left === null || $right === null) {
            return null;
        } elseif (is_string($left) && is_string($right) && strlen($left) !== strlen($right)) {
            throw new ResolveException('Arguments of uneven lengths for & operator.');
        } else {
            return $left & $right;
        }
    }

    /**
     * | - Bitwise OR
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     * @return int|string|null
     */
    public function _bit_or($left, $right)
    {
        [$left, $right] = $this->cast->toStringOrIntPair($left, $right);

        if ($left === null || $right === null) {
            return null;
        } elseif (is_string($left) && is_string($right) && strlen($left) !== strlen($right)) {
            throw new ResolveException('Arguments of uneven lengths for | operator.');
        } else {
            return $left | $right;
        }
    }

    /**
     * ^ - Bitwise XOR
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     * @return int|string|null
     */
    public function _bit_xor($left, $right)
    {
        [$left, $right] = $this->cast->toStringOrIntPair($left, $right);

        if ($left === null || $right === null) {
            return null;
        } elseif (is_string($left) && is_string($right) && strlen($left) !== strlen($right)) {
            throw new ResolveException('Arguments of uneven lengths for ^ operator.');
        } else {
            return $left ^ $right;
        }
    }

    /**
     * ~ - Bitwise inversion
     *
     * @param scalar|Value|null $right
     * @return int|string|null
     */
    public function _bit_inv($right)
    {
        $right = $this->cast->toIntOrString($right);

        if ($right === null) {
            return null;
        } else {
            return ~$right;
        }
    }

    /**
     * >> - Right shift
     *
     * @param scalar|Value|null $value
     * @param scalar|Value|null $shift
     * @return int|string|null
     */
    public function _right_shift($value, $shift)
    {
        $value = $this->cast->toIntOrString($value);
        $shift = $this->cast->toInt($shift);

        if ($value === null) {
            return null;
        } elseif (is_string($value)) {
            // todo: string shifts
            throw new LogicException('_right_shift() is not implemented yet.');
        } else {
            return $value << $shift;
        }
    }

    /**
     * << - Left shift
     *
     * @param scalar|Value|null $value
     * @param scalar|Value|null $shift
     * @return int|string|null
     */
    public function _left_shift($value, $shift)
    {
        $value = $this->cast->toIntOrString($value);
        $shift = $this->cast->toInt($shift);

        if ($value === null) {
            return null;
        } elseif (is_string($value)) {
            // todo: string shifts
            throw new LogicException('_left_shift() is not implemented yet.');
        } else {
            return $value >> $shift;
        }
    }

    /**
     * BIT_COUNT() - Return the number of bits that are set
     *
     * @param scalar|Value|null $value
     */
    public function bit_count($value): ?int
    {
        $value = $this->cast->toInt($value);

        if ($value === null) {
            return null;
        } else {
            return IntCalc::countOnes($value);
        }
    }

}
