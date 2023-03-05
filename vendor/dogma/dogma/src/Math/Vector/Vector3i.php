<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Vector;

use Dogma\Check;
use Dogma\Equalable;
use Dogma\StrictBehaviorMixin;

class Vector3i implements Equalable
{
    use StrictBehaviorMixin;

    /** @var int */
    private $x;

    /** @var int */
    private $y;

    /** @var int */
    private $z;

    final public function __construct(int $x, int $y, int $z)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getZ(): int
    {
        return $this->z;
    }

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return [$this->x, $this->y, $this->z];
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $other->x === $this->x && $other->y === $this->y && $other->z === $this->z;
    }

    public function add(Vector3i $other): self
    {
        return new static($this->x + $other->x, $this->y + $other->y, $this->z + $other->z);
    }

    public function subtract(Vector3i $other): self
    {
        return new static($this->x - $other->x, $this->y - $other->y, $this->z - $other->z);
    }

}
