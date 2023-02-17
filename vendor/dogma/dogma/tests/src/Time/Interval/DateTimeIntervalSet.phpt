<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\DayOfWeek;
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\Interval\DateIntervalSet;
use Dogma\Time\Interval\DateTimeInterval;
use Dogma\Time\Interval\DateTimeIntervalSet;
use Dogma\Time\Interval\TimeInterval;
use Dogma\Time\Interval\TimeIntervalSet;
use Dogma\Time\Interval\WeekDayHours;
use Dogma\Time\Interval\WeekDayHoursSet;
use Dogma\Time\Microseconds;

require_once __DIR__ . '/../../bootstrap.php';

$dt = static function (int $day): DateTime {
    return DateTime::createFromComponents(2000, 1, $day);
};
$i = static function (int $start, int $end) use ($dt): DateTimeInterval {
    return new DateTimeInterval($dt($start), $dt($end));
};
$s = static function (DateTimeInterval ...$items): DateTimeIntervalSet {
    return new DateTimeIntervalSet($items);
};

$interval = new DateTimeInterval($dt(1), $dt(5));
$emptyInterval = DateTimeInterval::empty();

$set = new DateTimeIntervalSet([$interval]);
$emptySet = new DateTimeIntervalSet([]);


createFromDateAndTimeIntervalSet:
Assert::equal(DateTimeIntervalSet::createFromDateAndTimeIntervalSet(
    new Date('2000-01-01'),
    new TimeIntervalSet([
        TimeInterval::createFromString('10:00 - 11:00'),
        TimeInterval::createFromString('12:00 - 13:00'),
    ])
), new DateTimeIntervalSet([
    DateTimeInterval::createFromString('2000-01-01 10:00 - 2000-01-01 11:00'),
    DateTimeInterval::createFromString('2000-01-01 12:00 - 2000-01-01 13:00'),
]));


createFromDateIntervalAndTimeInterval:
Assert::equal(DateTimeIntervalSet::createFromDateIntervalAndTimeInterval(
    DateInterval::createFromString('2000-01-01 - 2000-01-02'),
    TimeInterval::createFromString('10:00 - 11:00')
), new DateTimeIntervalSet([
    DateTimeInterval::createFromString('2000-01-01 10:00 - 2000-01-01 11:00'),
    DateTimeInterval::createFromString('2000-01-02 10:00 - 2000-01-02 11:00'),
]));


createFromDateIntervalAndTimeIntervalSet:
Assert::equal(DateTimeIntervalSet::createFromDateIntervalAndTimeIntervalSet(
    DateInterval::createFromString('2000-01-01 - 2000-01-02'),
    new TimeIntervalSet([
        TimeInterval::createFromString('10:00 - 11:00'),
        TimeInterval::createFromString('12:00 - 13:00'),
    ])
), new DateTimeIntervalSet([
    DateTimeInterval::createFromString('2000-01-01 10:00 - 2000-01-01 11:00'),
    DateTimeInterval::createFromString('2000-01-01 12:00 - 2000-01-01 13:00'),
    DateTimeInterval::createFromString('2000-01-02 10:00 - 2000-01-02 11:00'),
    DateTimeInterval::createFromString('2000-01-02 12:00 - 2000-01-02 13:00'),
]));


createFromDateIntervalSetAndTimeInterval:
Assert::equal(DateTimeIntervalSet::createFromDateIntervalSetAndTimeInterval(
    new DateIntervalSet([
        DateInterval::createFromString('2000-01-01 - 2000-01-02'),
        DateInterval::createFromString('2000-01-05 - 2000-01-06'),
    ]),
    TimeInterval::createFromString('10:00 - 11:00')
), new DateTimeIntervalSet([
    DateTimeInterval::createFromString('2000-01-01 10:00 - 2000-01-01 11:00'),
    DateTimeInterval::createFromString('2000-01-02 10:00 - 2000-01-02 11:00'),
    DateTimeInterval::createFromString('2000-01-05 10:00 - 2000-01-05 11:00'),
    DateTimeInterval::createFromString('2000-01-06 10:00 - 2000-01-06 11:00'),
]));


createFromDateIntervalSetAndTimeIntervalSet:
Assert::equal(DateTimeIntervalSet::createFromDateIntervalSetAndTimeIntervalSet(
    new DateIntervalSet([
        DateInterval::createFromString('2000-01-01 - 2000-01-02'),
        DateInterval::createFromString('2000-01-05 - 2000-01-06'),
    ]),
    new TimeIntervalSet([
        TimeInterval::createFromString('10:00 - 11:00'),
        TimeInterval::createFromString('12:00 - 13:00'),
    ])
), new DateTimeIntervalSet([
    DateTimeInterval::createFromString('2000-01-01 10:00 - 2000-01-01 11:00'),
    DateTimeInterval::createFromString('2000-01-01 12:00 - 2000-01-01 13:00'),
    DateTimeInterval::createFromString('2000-01-02 10:00 - 2000-01-02 11:00'),
    DateTimeInterval::createFromString('2000-01-02 12:00 - 2000-01-02 13:00'),
    DateTimeInterval::createFromString('2000-01-05 10:00 - 2000-01-05 11:00'),
    DateTimeInterval::createFromString('2000-01-05 12:00 - 2000-01-05 13:00'),
    DateTimeInterval::createFromString('2000-01-06 10:00 - 2000-01-06 11:00'),
    DateTimeInterval::createFromString('2000-01-06 12:00 - 2000-01-06 13:00'),
]));


createFromDateIntervalAndWeekDayHoursSet:
Assert::equal(DateTimeIntervalSet::createFromDateIntervalAndWeekDayHoursSet(
    DateInterval::createFromString('2000-01-01 - 2000-01-02'),
    new WeekDayHoursSet([
        new WeekDayHours(DayOfWeek::monday(), new TimeIntervalSet([TimeInterval::createFromString('10:00 - 11:00')])),
        new WeekDayHours(DayOfWeek::sunday(), new TimeIntervalSet([TimeInterval::createFromString('12:00 - 13:00')])),
    ])
), new DateTimeIntervalSet([
    DateTimeInterval::createFromString('2000-01-02 12:00 - 2000-01-02 13:00'),
]));


createFromDateIntervalSetAndWeekDayHoursSet:
Assert::equal(DateTimeIntervalSet::createFromDateIntervalSetAndWeekDayHoursSet(
    new DateIntervalSet([
        DateInterval::createFromString('2000-01-01 - 2000-01-02'),
        DateInterval::createFromString('2000-01-08 - 2000-01-09'),
    ]),
    new WeekDayHoursSet([
        new WeekDayHours(DayOfWeek::monday(), new TimeIntervalSet([TimeInterval::createFromString('10:00 - 11:00')])),
        new WeekDayHours(DayOfWeek::sunday(), new TimeIntervalSet([TimeInterval::createFromString('12:00 - 13:00')])),
    ])
), new DateTimeIntervalSet([
    DateTimeInterval::createFromString('2000-01-02 12:00 - 2000-01-02 13:00'),
    DateTimeInterval::createFromString('2000-01-09 12:00 - 2000-01-09 13:00'),
]));


getIntervals:
getIterator:
Assert::same($set->getIntervals(), iterator_to_array($set->getIterator()));


isEmpty:
Assert::true((new DateTimeIntervalSet([]))->isEmpty());
Assert::true((new DateTimeIntervalSet([$emptyInterval]))->isEmpty());


equals:
Assert::true($set->equals($s($i(1, 5))));
Assert::false($set->equals($s($i(1, 5), $i(6, 7))));
Assert::false($set->equals($s($i(1, 6))));
Assert::false($set->equals($emptySet));
Assert::false($emptySet->equals($set));


containsValue:
Assert::true($set->containsValue($dt(1)));
Assert::true($set->containsValue($dt(4)));
Assert::false($set->containsValue($dt(5)));
Assert::false($set->containsValue($dt(6)));


envelope:
Assert::equal($s($i(1, 2), $i(4, 5))->envelope(), $interval);
Assert::equal($emptySet->envelope(), $emptyInterval);


normalize:
Assert::equal($s($i(1, 4), $i(2, 5))->normalize(), $set);


add:
Assert::equal($s($i(1, 2), $i(3, 4), $i(5, 6)), $s($i(1, 2))->add($s($i(3, 4), $i(5, 6))));


subtract:
Assert::equal($s($i(1, 10))->subtract($s($i(3, 4), $i(7, 8))), $s($i(1, 3), $i(4, 7), $i(8, 10)));


intersect:
Assert::equal($s($i(1, 5), $i(10, 15))->intersect($s($i(4, 12), $i(14, 20))), $s($i(4, 5), $i(10, 12), $i(14, 15)));


map:
Assert::equal($set->map(static function (DateTimeInterval $interval) {
    return $interval;
}), $set);
Assert::equal($set->map(static function (DateTimeInterval $interval) {
    return $interval->split(2);
}), $s($i(1, 3), $i(3, 5)));
Assert::equal($set->map(static function (DateTimeInterval $interval) {
    return $interval->split(2)->getIntervals();
}), $s($i(1, 3), $i(3, 5)));

$set = $s(DateTimeInterval::empty(), $i(1, 1), $i(1, 2), $i(1, 3));


filterByLength:
Assert::equal($set->filterByLength('>', Microseconds::DAY), $s($i(1, 3)));
Assert::equal($set->filterByLength('>=', Microseconds::DAY), $s($i(1, 2), $i(1, 3)));
Assert::equal($set->filterByLength('=', Microseconds::DAY), $s($i(1, 2)));
Assert::equal($set->filterByLength('<>', Microseconds::DAY), $s(DateTimeInterval::empty(), $i(1, 1), $i(1, 3)));
Assert::equal($set->filterByLength('<=', Microseconds::DAY), $s(DateTimeInterval::empty(), $i(1, 1), $i(1, 2)));
Assert::equal($set->filterByLength('<', Microseconds::DAY), $s(DateTimeInterval::empty(), $i(1, 1)));
