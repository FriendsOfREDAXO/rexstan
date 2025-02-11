<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\InvalidFormattingStringException;
use Dogma\Time\Time;
use function count;
use function explode;

/**
 * Uses standard PHP date() formatting and "|" separator for start and end part of formatting string.
 */
class SimpleDateTimeIntervalFormatter implements DateTimeIntervalFormatter
{
    use StrictBehaviorMixin;

    public function format(DateOrTimeInterval $interval, ?string $format = null): string
    {
        if ($format !== null) {
            $parts = explode(self::START_END_SEPARATOR, $format);
            if (count($parts) !== 2) {
                throw new InvalidFormattingStringException(
                    "Format string '$format' should contain exactly one '|' separator, to distinguish format for start and end date/time."
                );
            }
            [$startFormat, $endFormat] = $parts;
            $separator = '';
        } elseif ($interval instanceof TimeInterval) {
            $startFormat = $endFormat = Time::DEFAULT_FORMAT;
            $separator = ' - ';
        } elseif ($interval instanceof DateTimeInterval) {
            $startFormat = $endFormat = DateTime::DEFAULT_FORMAT;
            $separator = ' - ';
        } elseif ($interval instanceof DateInterval || $interval instanceof NightInterval) {
            $startFormat = $endFormat = Date::DEFAULT_FORMAT;
            $separator = ' - ';
        } else {
            throw new ShouldNotHappenException('Default format for ' . get_class($interval) . ' is not defined.');
        }

        if ($interval instanceof DateInterval) {
            $start = $interval->getStart()->toDateTime();
            $end = $interval->getEnd()->toDateTime();
        } elseif ($interval instanceof TimeInterval) {
            $start = $interval->getStart()->toDateTime();
            $end = $interval->getEnd()->toDateTime();
        } else {
            $start = $interval->getStart();
            $end = $interval->getEnd();
        }

        return $start->format($startFormat) . $separator . $end->format($endFormat);
    }

}
