<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type\Time;

use DateTimeZone;
use Dogma\Mapping\Mapper;
use Dogma\Mapping\Type\TypeHandler;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\Time;
use Dogma\Type;

/**
 * Creates Date/Time/DateTime instances from raw data and vice versa
 */
class DateTimeHandler implements TypeHandler
{
    use StrictBehaviorMixin;

    /** @var string */
    private $dateTimeFormat;

    /** @var string */
    private $dateFormat;

    /** @var string */
    private $timeFormat;

    /** @var DateTimeZone|null */
    private $timeZone;

    public function __construct(
        string $dateTimeFormat = 'Y-m-d H:i:s',
        string $dateFormat = 'Y-m-d',
        string $timeFormat = 'H:i:s',
        ?DateTimeZone $timeZone = null
    )
    {
        $this->dateTimeFormat = $dateTimeFormat;
        $this->dateFormat = $dateFormat;
        $this->timeFormat = $timeFormat;
        $this->timeZone = $timeZone;
    }

    public function acceptsType(Type $type): bool
    {
        return $type->isImplementing(DateTime::class)
            || $type->isImplementing(Date::class)
            || $type->isImplementing(Time::class);
    }

    /**
     * @return Type[]|null
     */
    public function getParameters(Type $type): ?array
    {
        return null;
    }

    /**
     * @param mixed $value
     * @return DateTime|Date|Time
     */
    public function createInstance(Type $type, $value, Mapper $mapper)
    {
        if ($type->isImplementing(Date::class)) {
            return Date::createFromFormat($this->dateFormat, $value);
        } elseif ($type->isImplementing(Time::class)) {
            return Time::createFromFormat($this->timeFormat, $value);
        } else {
            return DateTime::createFromFormat($this->dateTimeFormat, $value, $this->timeZone);
        }
    }

    /**
     * @param DateTime|Date|Time $instance
     * @return string
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper): string
    {
        if ($instance instanceof Date) {
            return $instance->format($this->dateFormat);
        } elseif ($instance instanceof Time) {
            return $instance->format($this->timeFormat);
        } else {
            return $instance->format($this->dateTimeFormat);
        }
    }

}
