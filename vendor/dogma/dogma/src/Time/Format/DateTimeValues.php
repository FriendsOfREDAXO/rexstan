<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Format;

use DateTimeZone;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateOrTime;

class DateTimeValues
{
    use StrictBehaviorMixin;

    /** @var int */
    public $year;

    /** @var bool */
    public $leapYear;

    /** @var int */
    public $dayOfYear;

    /** @var int */
    public $quarter;

    /** @var int */
    public $month;

    /** @var int */
    public $weekOfYear;

    /** @var int */
    public $isoWeekYear;

    /** @var int */
    public $dayOfWeek;

    /** @var int */
    public $day;

    /** @var int */
    public $hours;

    /** @var int */
    public $minutes;

    /** @var int */
    public $seconds;

    /** @var int */
    public $miliseconds;

    /** @var int */
    public $microseconds;

    /** @var bool */
    public $dst;

    /** @var string */
    public $offset;

    /** @var DateTimeZone */
    public $timezone;

    /** @var DateOrTime */
    public $dateTime;

    private function __construct(DateOrTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function create(DateOrTime $dateTime): self
    {
        $self = new self($dateTime);

        $dateTime->fillValues($self);

        return $self;
    }

}
