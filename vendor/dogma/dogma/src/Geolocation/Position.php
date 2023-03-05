<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: latlong

namespace Dogma\Geolocation;

use Dogma\Check;
use Dogma\Math\Constant;
use Dogma\Math\Vector\Vector3;
use Dogma\StrictBehaviorMixin;
use function deg2rad;
use function rad2deg;

/**
 * http://www.movable-type.co.uk/scripts/latlong.html
 */
class Position
{
    use StrictBehaviorMixin;

    public const PLANET_EARTH_RADIUS = 6371000.0;

    /** @var float [m] */
    private $planetRadius;

    /** @var float [degrees] */
    private $latitude;

    /** @var float [degrees] */
    private $longitude;

    /** @var float[] */
    private $normalVector;

    final public function __construct(float $latitude, float $longitude, float $planetRadius = self::PLANET_EARTH_RADIUS)
    {
        Check::range($latitude, -90.0, 90.0);
        Check::range($longitude, -180.0, 180.0);
        Check::min($planetRadius, 0.0);

        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->planetRadius = $planetRadius;
    }

    public static function fromRadians(float $latitude, float $longitude, float $planetRadius = self::PLANET_EARTH_RADIUS): self
    {
        Check::range($latitude, -Constant::HALF_PI, Constant::HALF_PI);
        Check::range($longitude, -Constant::PI, Constant::PI);

        return new static(rad2deg($latitude), rad2deg($longitude), $planetRadius);
    }

    public static function fromNormalVector(float $x, float $y, float $z, float $planetRadius = self::PLANET_EARTH_RADIUS): self
    {
        Check::range($x, 0.0, 1.0);
        Check::range($y, 0.0, 1.0);
        Check::range($z, 0.0, 1.0);

        [$latitude, $longitude] = Vector3::normalVectorToRadians($x, $y, $z);

        $position = new static(rad2deg($latitude), rad2deg($longitude), $planetRadius);
        $position->normalVector = [$x, $y, $z];

        return $position;
    }

    /**
     * @return float[] array('latitude' => $latitude, 'longitude' => $longitude)
     */
    public function export(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getPlanetRadius(): float
    {
        return $this->planetRadius;
    }

    /**
     * @return float[]
     */
    public function getNormalVector(): array
    {
        if ($this->normalVector === null) {
            $this->normalVector = Vector3::radiansToNormalVector(deg2rad($this->latitude), deg2rad($this->longitude));
        }

        return $this->normalVector;
    }

}
