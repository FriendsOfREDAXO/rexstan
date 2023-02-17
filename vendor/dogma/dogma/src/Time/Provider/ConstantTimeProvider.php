<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Provider;

use DateTimeZone;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\DateTime;

class ConstantTimeProvider implements TimeProvider
{
    use StrictBehaviorMixin;

    /** @var DateTime */
    private $dateTime;

    /** @var DateTimeZone */
    private $timeZone;

    public function __construct(?DateTime $dateTime = null, ?DateTimeZone $timeZone = null)
    {
        if ($dateTime === null) {
            $dateTime = new DateTime();
        }
        if ($timeZone === null) {
            $timeZone = $dateTime->getTimezone();
        }

        $this->dateTime = $dateTime;
        $this->timeZone = $timeZone;
    }

    public function getDate(): Date
    {
        return $this->dateTime->getDate();
    }

    public function getDateTime(?DateTimeZone $timeZone = null): DateTime
    {
        if ($timeZone !== null) {
            return $this->dateTime->setTimezone($timeZone);
        }

        return $this->dateTime;
    }

    public function getTimeZone(): DateTimeZone
    {
        return $this->timeZone;
    }

}
