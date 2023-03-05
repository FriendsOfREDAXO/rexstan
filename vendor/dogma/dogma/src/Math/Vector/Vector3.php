<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: bz

namespace Dogma\Math\Vector;

use Dogma\Math\FloatCalc;
use Dogma\StrictBehaviorMixin;
use function abs;
use function atan2;
use function cos;
use function sin;
use function sqrt;

class Vector3
{
    use StrictBehaviorMixin;

    /** @var float */
    private $x;

    /** @var float */
    private $y;

    /** @var float */
    private $z;

    final public function __construct(float $x, float $y, float $z)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * @return float[]
     */
    public function getValues(): array
    {
        return [$this->x, $this->y, $this->z];
    }

    /**
     * @return float[] ($latitude, $longitude)
     */
    public static function normalVectorToRadians(float $x, float $y, float $z): array
    {
        $latitude = atan2($y, $x);
        $longitude = atan2($z, sqrt($x * $x + $y * $y));

        return [$longitude, $latitude];
    }

    /**
     * @return float[] ($x, $y, $z)
     */
    public static function radiansToNormalVector(float $latitude, float $longitude): array
    {
        $x = cos($latitude) * cos($longitude);
        $y = cos($latitude) * sin($longitude);
        $z = sin($latitude);

        return [$x, $y, $z];
    }

    /**
     * @return float[]
     */
    public static function normalize(float $x, float $y, float $z): array
    {
        $size = abs(sqrt($x * $x + $y * $y + $z * $z));

        if (!FloatCalc::equals($size, 1.0)) {
            $x /= $size;
            $y /= $size;
            $z /= $size;
        }

        return [$x, $y, $z];
    }

    public static function dotProduct(float $ax, float $ay, float $az, float $bx, float $by, float $bz): float
    {
        $dotProduct = $ax * $bx + $ay * $by + $az * $bz;

        // fixes rounding error on 16th place, which can cause a NAN later
        if ($dotProduct > 1.0) {
            $dotProduct = 1.0;
        } elseif ($dotProduct < -1.0) {
            $dotProduct = -1.0;
        }

        return $dotProduct;
    }

}
