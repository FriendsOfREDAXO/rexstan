<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\Interval\WeekDateInterval;
use Dogma\Time\InvalidWeekDateIntervalException;

require_once __DIR__ . '/../../bootstrap.php';

$startDate = new Date('2000-01-10');
$endDate = new Date('2000-01-16');
$interval = new WeekDateInterval($startDate, $endDate);

$d = static function (int $day): Date {
    return new Date('2000-01-' . $day);
};


__construct:
// wrong start day
Assert::exception(static function () use ($d): void {
    new WeekDateInterval($d(1), $d(8));
}, InvalidWeekDateIntervalException::class);

// too short
Assert::exception(static function () use ($startDate, $d): void {
    new WeekDateInterval($startDate, $d(15));
}, InvalidWeekDateIntervalException::class);

// too long
Assert::exception(static function () use ($startDate, $d): void {
    new WeekDateInterval($startDate, $d(20));
}, InvalidWeekDateIntervalException::class);


createFromDate:
Assert::equal(WeekDateInterval::createFromDate(new Date('2000-01-13')), $interval);


createFromDateTime:
Assert::equal(WeekDateInterval::createFromDate($d(13)), $interval);


createFromYearAndWeekNumber:
Assert::equal(WeekDateInterval::createFromIsoYearAndWeek(2000, 2), $interval);


previous:
$previous = WeekDateInterval::createFromIsoYearAndWeek(2000, 1);
Assert::equal($interval->previous(), $previous);


next:
$next = WeekDateInterval::createFromIsoYearAndWeek(2000, 3);
Assert::equal($interval->next(), $next);


createOverlappingIntervals:
Assert::equal(WeekDateInterval::createOverlappingIntervals($interval), [$interval]);
Assert::equal(WeekDateInterval::createOverlappingIntervals(new DateInterval($d(5), $d(20))), [$previous, $interval, $next]);
