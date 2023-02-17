<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver\Functions;

use SqlFtw\Sql\Expression\Value;
use function abs;
use function acos;
use function asin;
use function atan;
use function atan2;
use function base_convert;
use function ceil;
use function cos;
use function deg2rad;
use function exp;
use function explode;
use function floor;
use function intdiv;
use function log;
use function log10;
use function random_int;
use function round;
use function sin;
use function sqrt;
use function substr;
use function tan;
use const M_PI;
use const PHP_INT_MAX;

trait FunctionsNumeric
{

    /**
     * * - Multiplication operator
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     * @return int|float|null
     */
    public function _multiply($left, $right)
    {
        $left = $this->cast->toIntOrFloat($left);
        $right = $this->cast->toIntOrFloat($right);

        if ($left === null || $right === null) {
            return null;
        } else {
            return $left * $right;
        }
    }

    /**
     * / - Division operator
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     * @return int|float|null
     */
    public function _divide($left, $right)
    {
        $left = $this->cast->toIntOrFloat($left);
        $right = $this->cast->toIntOrFloat($right);

        if ($left === null || $right === null || $right === 0 || $right === 0.0) {
            return null;
        } else {
            return $left / $right;
        }
    }

    /**
     * DIV - Integer division
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     */
    public function _div($left, $right): ?int
    {
        $left = $this->cast->toInt($left);
        $right = $this->cast->toInt($right);

        if ($left === null || $right === null || $right === 0) {
            return null;
        } else {
            return intdiv($left, $right);
        }
    }

    /**
     * %, MOD - Modulo operator
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     */
    public function _modulo($left, $right): ?int
    {
        $left = $this->cast->toInt($left);
        $right = $this->cast->toInt($right);

        if ($left === null || $right === null || $right === 0) {
            return null;
        } else {
            return $left % $right;
        }
    }

    /**
     * + - Addition operator
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     * @return int|float|null
     */
    public function _plus($left, $right)
    {
        $left = $this->cast->toIntOrFloat($left);
        $right = $this->cast->toIntOrFloat($right);

        if ($left === null || $right === null) {
            return null;
        } else {
            return $left + $right;
        }
    }

    /**
     * + - Unary plus (basically casts to number)
     *
     * @param scalar|Value|null $right
     * @return int|float|null
     */
    public function _unary_plus($right)
    {
        $right = $this->cast->toIntOrFloat($right);

        if ($right === null) {
            return null;
        } else {
            return $right;
        }
    }

    /**
     * - - Minus operator
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     * @return int|float|null
     */
    public function _minus($left, $right)
    {
        $left = $this->cast->toIntOrFloat($left);
        $right = $this->cast->toIntOrFloat($right);

        if ($left === null || $right === null) {
            return null;
        } else {
            return $left - $right;
        }
    }

    /**
     * - - Change the sign of the argument
     *
     * @param scalar|Value|null $right
     * @return int|float|null
     */
    public function _unary_minus($right)
    {
        $right = $this->cast->toIntOrFloat($right);

        if ($right === null) {
            return null;
        } else {
            return -$right;
        }
    }

    /**
     * ABS() - Return the absolute value
     *
     * @param scalar|Value|null $x
     * @return int|float|null
     */
    public function abs($x)
    {
        $x = $this->cast->toIntOrFloat($x);

        if ($x === null) {
            return null;
        } else {
            return abs($x);
        }
    }

    /**
     * ACOS() - Return the arc cosine
     *
     * @param scalar|Value|null $x
     */
    public function acos($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return acos($x);
        }
    }

    /**
     * ASIN() - Return the arc sine
     *
     * @param scalar|Value|null $x
     */
    public function asin($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return asin($x);
        }
    }

    /**
     * ATAN() - Return the arc tangent
     *
     * @param scalar|Value|null $x
     * @param scalar|Value|null $other
     */
    public function atan($x, $other = 'none'): ?float
    {
        if ($other !== 'none') {
            return $this->atan2($x, $other);
        }

        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return atan($x);
        }
    }

    /**
     * ATAN2(), ATAN() - Return the arc tangent of the two arguments
     *
     * @param scalar|Value|null $y
     * @param scalar|Value|null $x
     */
    public function atan2($y, $x): ?float
    {
        $y = $this->cast->toFloat($y);
        $x = $this->cast->toFloat($x);

        if ($y === null || $x === null) {
            return null;
        } else {
            return atan2($y, $x);
        }
    }

    /**
     * CEIL() - Return the smallest integer value not less than the argument
     *
     * @param scalar|Value|null $x
     */
    public function ceil($x): ?int
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return (int) ceil($x);
        }
    }

    /**
     * CEILING() - Return the smallest integer value not less than the argument
     *
     * @param scalar|Value|null $x
     */
    public function ceiling($x): ?int
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return (int) ceil($x);
        }
    }

    /**
     * CONV() - Convert numbers between different number bases
     *
     * @param scalar|Value|null $x
     * @param scalar|Value|null $from
     * @param scalar|Value|null $to
     */
    public function conv($x, $from, $to): ?string
    {
        $x = $this->cast->toString($x);
        $from = $this->cast->toInt($from);
        $to = $this->cast->toInt($to);

        if ($x === null || $from === null || $to === null) {
            return null;
        } else {
            return base_convert($x, $from, $to);
        }
    }

    /**
     * COS() - Return the cosine
     *
     * @param scalar|Value|null $x
     */
    public function cos($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return cos($x);
        }
    }

    /**
     * COT() - Return the cotangent
     *
     * @param scalar|Value|null $x
     */
    public function cot($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return cos($x) / sin($x);
        }
    }

    /**
     * DEGREES() - Convert radians to degrees
     *
     * @param scalar|Value|null $x
     */
    public function degrees($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return deg2rad($x);
        }
    }

    /**
     * EXP() - Raise to the power of
     *
     * @param scalar|Value|null $x
     */
    public function exp($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return exp($x);
        }
    }

    /**
     * FLOOR() - Return the largest integer value not greater than the argument
     *
     * @param scalar|Value|null $x
     */
    public function floor($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return floor($x);
        }
    }

    /**
     * LN() - Return the natural logarithm of the argument
     *
     * @param scalar|Value|null $x
     */
    public function ln($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return log($x);
        }
    }

    /**
     * LOG() - Return the natural logarithm of the first argument
     *
     * @param scalar|Value|null $x
     * @param scalar|Value|null $other
     */
    public function log($x, $other = 'none'): ?float
    {
        if ($other === 'none') {
            return $this->ln($x);
        }

        $x = $this->cast->toFloat($x);
        $other = $this->cast->toFloat($other);

        if ($x === null || $other === null) {
            return null;
        } else {
            return log($other, $x);
        }
    }

    /**
     * LOG10() - Return the base-10 logarithm of the argument
     *
     * @param scalar|Value|null $x
     */
    public function log10($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return log10($x);
        }
    }

    /**
     * LOG2() - Return the base-2 logarithm of the argument
     *
     * @param scalar|Value|null $x
     */
    public function log2($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return log($x, 2);
        }
    }

    /**
     * MOD() - Return the remainder
     *
     * @param scalar|Value|null $x
     * @param scalar|Value|null $y
     */
    public function mod($x, $y): ?float
    {
        $x = $this->cast->toFloat($x);
        $y = $this->cast->toFloat($y);

        if ($x === null || $y === null || $y === 0.0) {
            return null;
        } else {
            return $x - (floor($x / $y) * $y);
        }
    }

    /**
     * PI() - Return the value of pi
     */
    public function pi(): float
    {
        return M_PI;
    }

    /**
     * POW() - Return the argument raised to the specified power
     *
     * @param scalar|Value|null $x
     * @param scalar|Value|null $y
     */
    public function pow($x, $y): ?float
    {
        $x = $this->cast->toFloat($x);
        $y = $this->cast->toFloat($y);

        if ($x === null || $y === null) {
            return null;
        } else {
            return $x ** $y;
        }
    }

    /**
     * POWER() - Return the argument raised to the specified power
     *
     * @param scalar|Value|null $x
     * @param scalar|Value|null $y
     */
    public function power($x, $y): ?float
    {
        return $this->pow($x, $y);
    }

    /**
     * RADIANS() - Return argument converted to radians
     *
     * @param scalar|Value|null $x
     */
    public function radians($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return deg2rad($x);
        }
    }

    /**
     * RAND() - Return a random floating-point value
     *
     * @param scalar|Value|null $seed
     */
    public function rand($seed = null): float
    {
        $seed = $this->cast->toFloat($seed);
        // todo: seed ignored

        return random_int(0, PHP_INT_MAX) / PHP_INT_MAX;
    }

    /**
     * ROUND() - Round the argument
     *
     * @param scalar|Value|null $x
     * @param scalar|Value|null $decimals
     */
    public function round($x, $decimals = 0): ?float
    {
        $x = $this->cast->toFloat($x);
        $decimals = $this->cast->toInt($decimals);

        if ($x === null || $decimals === null) {
            return null;
        } else {
            return round($x, $decimals);
        }
    }

    /**
     * SIGN() - Return the sign of the argument
     *
     * @param scalar|Value|null $x
     */
    public function sign($x): ?int
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } elseif ($x === 0.0) {
            return 0;
        } else {
            return $x > 0.0 ? 1 : -1;
        }
    }

    /**
     * SIN() - Return the sine of the argument
     *
     * @param scalar|Value|null $x
     */
    public function sin($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return sin($x);
        }
    }

    /**
     * SQRT() - Return the square root of the argument
     *
     * @param scalar|Value|null $x
     */
    public function sqrt($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return sqrt($x);
        }
    }

    /**
     * TAN() - Return the tangent of the argument
     *
     * @param scalar|Value|null $x
     */
    public function tan($x): ?float
    {
        $x = $this->cast->toFloat($x);

        if ($x === null) {
            return null;
        } else {
            return tan($x);
        }
    }

    /**
     * TRUNCATE() - Truncate to specified number of decimal places
     *
     * @param scalar|Value|null $x
     * @param scalar|Value|null $decimals
     */
    public function truncate($x, $decimals = 0): ?float
    {
        $x = $this->cast->toFloat($x);
        $decimals = $this->cast->toInt($decimals);

        if ($x === null || $decimals === null) {
            return null;
        } else {
            $parts = explode('.', $x . '.');

            return (float) ($parts[0] . '.' . substr($parts[1], 0, $decimals));
        }
    }

}
