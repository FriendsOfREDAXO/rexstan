<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use DateTimeImmutable;
use Dogma\Math\Interval\InvalidIntervalStringFormatException;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\Interval\DateIntervalSet;
use Dogma\Time\Interval\DateTimeInterval;
use Dogma\Time\InvalidDateTimeUnitException;
use Dogma\Time\InvalidIntervalStartEndOrderException;
use Dogma\Time\Span\DateSpan;
use Dogma\Time\Span\DateTimeSpan;

require_once __DIR__ . '/../../bootstrap.php';

$startDate = new Date('2000-01-10');
$endDate = new Date('2000-01-20');
$interval = new DateInterval($startDate, $endDate);
$empty = DateInterval::empty();
$all = DateInterval::all();

$d = static function (int $day): Date {
    return new Date('2000-01-' . $day);
};
$i = static function (int $start, int $end): DateInterval {
    return new DateInterval(new Date('2000-01-' . $start), new Date('2000-01-' . $end));
};
$s = static function (DateInterval ...$items): DateIntervalSet {
    return new DateIntervalSet($items);
};


__construct:
Assert::exception(static function (): void {
    new DateInterval(new Date('today'), new Date('yesterday'));
}, InvalidIntervalStartEndOrderException::class);


createFromString:
Assert::equal(DateInterval::createFromString('2000-01-10,2000-01-20'), $interval);
Assert::equal(DateInterval::createFromString('2000-01-10|2000-01-20'), $interval);
Assert::equal(DateInterval::createFromString('2000-01-10/2000-01-20'), $interval);
Assert::equal(DateInterval::createFromString('2000-01-10 - 2000-01-20'), $interval);
Assert::equal(DateInterval::createFromString('[2000-01-10,2000-01-20]'), $interval);
Assert::equal(DateInterval::createFromString('[2000-01-10,2000-01-21)'), $interval);
Assert::equal(DateInterval::createFromString('(2000-01-09,2000-01-21)'), $interval);
Assert::equal(DateInterval::createFromString('(2000-01-09,2000-01-20]'), $interval);
Assert::exception(static function (): void {
    DateInterval::createFromString('foo|bar|baz');
}, InvalidIntervalStringFormatException::class);
Assert::exception(static function (): void {
    DateInterval::createFromString('foo');
}, InvalidIntervalStringFormatException::class);


createFromStartAndLength:
Assert::equal(DateInterval::createFromStartAndLength($startDate, DateTimeUnit::year(), 2), new DateInterval($startDate, new Date('2002-01-09')));
Assert::equal(DateInterval::createFromStartAndLength($startDate, DateTimeUnit::quarter(), 2), new DateInterval($startDate, new Date('2000-07-09')));
Assert::equal(DateInterval::createFromStartAndLength($startDate, DateTimeUnit::month(), 2), new DateInterval($startDate, new Date('2000-03-09')));
Assert::equal(DateInterval::createFromStartAndLength($startDate, DateTimeUnit::week(), 2), new DateInterval($startDate, new Date('2000-01-23')));
Assert::equal(DateInterval::createFromStartAndLength($startDate, DateTimeUnit::day(), 2), new DateInterval($startDate, new Date('2000-01-11')));
Assert::exception(static function () use ($startDate): void {
    DateInterval::createFromStartAndLength($startDate, DateTimeUnit::hour(), 2);
}, InvalidDateTimeUnitException::class);


toDateTimeInterval:
Assert::equal($interval->toDateTimeInterval(), DateTimeInterval::createFromString('2000-01-10 00:00 - 2000-01-21 00:00'));


toDateArray:
Assert::equal($i(1, 1)->toDateArray(), [$d(1)]);
Assert::equal($i(1, 2)->toDateArray(), [$d(1), $d(2)]);
Assert::equal($empty->toDateArray(), []);


format:
Assert::same($interval->format('d|-d'), '10-20');


shift:
Assert::equal($interval->shift('+1 day'), $i(11, 21));


getStart:
Assert::equal($interval->getStart(), new Date('2000-01-10'));


getEnd:
Assert::equal($interval->getEnd(), new Date('2000-01-20'));


getSpan:
Assert::equal($interval->getSpan(), new DateTimeSpan(0, 0, 10));


getDateSpan:
Assert::equal($interval->getDateSpan(), new DateSpan(0, 0, 10));


getLengthInDays:
Assert::same($interval->getLengthInDays(), 10);
Assert::same($empty->getLengthInDays(), 0);


getDayCount:
Assert::same($interval->getDayCount(), 11);
Assert::same($empty->getDayCount(), 0);


isEmpty:
Assert::false($interval->isEmpty());
Assert::false($all->isEmpty());
Assert::true($empty->isEmpty());


equals:
Assert::true($interval->equals($i(10, 20)));
Assert::false($interval->equals($i(10, 15)));
Assert::false($interval->equals($i(15, 20)));


compare:
Assert::same($interval->compare($i(10, 19)), 1);
Assert::same($interval->compare($i(10, 21)), -1);
Assert::same($interval->compare($interval), 0);
Assert::same($interval->compare($i(9, 19)), 1);
Assert::same($interval->compare($i(11, 21)), -1);


containsValue:
Assert::true($interval->containsValue($d(10)));
Assert::true($interval->containsValue($d(15)));
Assert::true($interval->containsValue($d(20)));
Assert::false($interval->containsValue($d(5)));
Assert::false($interval->containsValue($d(25)));
Assert::true($interval->containsValue(new DateTimeImmutable('2000-01-15')));


contains:
Assert::true($interval->contains($i(10, 20)));
Assert::true($interval->contains($i(10, 15)));
Assert::true($interval->contains($i(15, 20)));
Assert::false($interval->contains($i(5, 20)));
Assert::false($interval->contains($i(10, 25)));
Assert::false($interval->contains($i(1, 5)));
Assert::false($interval->contains($empty));


intersects:
Assert::true($interval->intersects($i(5, 25)));
Assert::true($interval->intersects($i(10, 20)));
Assert::true($interval->intersects($i(5, 15)));
Assert::true($interval->intersects($i(15, 25)));
Assert::false($interval->intersects($i(1, 5)));
Assert::false($interval->intersects($empty));


touches:
Assert::true($interval->touches($i(1, 9)));
Assert::true($interval->touches($i(21, 25)));
Assert::false($interval->touches($i(1, 10)));
Assert::false($interval->touches($i(20, 25)));
Assert::false($interval->touches($empty));


split:
Assert::equal($interval->split(1), $s($interval));
Assert::equal($interval->split(2), $s($i(10, 15), $i(16, 20)));
Assert::equal($interval->split(3), $s($i(10, 13), $i(14, 16), $i(17, 20)));
Assert::equal($interval->split(4), $s($i(10, 12), $i(13, 15), $i(16, 17), $i(18, 20)));
Assert::equal($interval->split(11), $s($i(10, 10), $i(11, 11), $i(12, 12), $i(13, 13), $i(14, 14), $i(15, 15), $i(16, 16), $i(17, 17), $i(18, 18), $i(19, 19), $i(20, 20)));
Assert::equal($empty->split(5), $s());


splitBy:
Assert::equal($interval->splitBy([$d(5), $d(15), $d(25)]), $s($i(10, 14), $i(15, 20)));
Assert::equal($empty->splitBy([$d(5)]), $s());


envelope:
Assert::equal($interval->envelope($i(5, 15)), $i(5, 20));
Assert::equal($interval->envelope($i(15, 25)), $i(10, 25));
Assert::equal($interval->envelope($i(1, 5)), $i(1, 20));
Assert::equal($interval->envelope($i(25, 30)), $i(10, 30));
Assert::equal($interval->envelope($i(1, 5), $i(25, 30)), $i(1, 30));
Assert::equal($interval->envelope($empty), $interval);


intersect:
Assert::equal($interval->intersect($i(1, 15)), $i(10, 15));
Assert::equal($interval->intersect($i(15, 30)), $i(15, 20));
Assert::equal($interval->intersect($i(1, 18), $i(14, 30)), $i(14, 18));
Assert::equal($interval->intersect($i(1, 5)), $empty);
Assert::equal($interval->intersect($i(1, 5), $i(5, 15)), $empty);
Assert::equal($interval->intersect($empty), $empty);


union:
Assert::equal($interval->union($i(1, 15)), $s($i(1, 20)));
Assert::equal($interval->union($i(15, 30)), $s($i(10, 30)));
Assert::equal($interval->union($i(1, 15), $i(15, 30)), $s($i(1, 30)));
Assert::equal($interval->union($i(25, 30)), $s($interval, $i(25, 30)));
Assert::equal($interval->union($all), $s($all));
Assert::equal($interval->union($empty), $s($interval));


difference:
Assert::equal($interval->difference($i(15, 30)), $s($i(10, 14), $i(21, 30)));
Assert::equal($interval->difference($i(5, 15)), $s($i(5, 9), $i(16, 20)));
Assert::equal($interval->difference($i(5, 15), $i(15, 30)), $s($i(5, 9), $i(21, 30)));
Assert::equal($interval->difference($i(25, 30)), $s($interval, $i(25, 30)));
Assert::equal($interval->difference($all), $s(
    new DateInterval(new Date(Date::MIN), $d(9)),
    new DateInterval($d(21), new Date(Date::MAX))
));
Assert::equal($interval->difference($empty), $s($interval));


subtract:
Assert::equal($interval->subtract($i(5, 15)), $s($i(16, 20)));
Assert::equal($interval->subtract($i(15, 25)), $s($i(10, 14)));
Assert::equal($interval->subtract($i(13, 17)), $s($i(10, 12), $i(18, 20)));
Assert::equal($interval->subtract($i(5, 10), $i(20, 25)), $s($i(11, 19)));
Assert::equal($interval->subtract($empty), $s($interval));
Assert::equal($interval->subtract($all), $s());
Assert::equal($all->subtract($empty), $s($all));
Assert::equal($empty->subtract($empty), $s());


invert:
Assert::equal($interval->invert(), $s(
    new DateInterval(new Date(Date::MIN), $d(9)),
    new DateInterval($d(21), new Date(Date::MAX))
));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s($empty));


countOverlaps:
Assert::equal(DateInterval::countOverlaps($empty), []);
Assert::equal(DateInterval::countOverlaps($interval, $i(5, 15)), [
    [$i(5, 9), 1],
    [$i(10, 15), 2],
    [$i(16, 20), 1],
]);
Assert::equal(DateInterval::countOverlaps($i(5, 15), $i(10, 20), $i(15, 25)), [
    [$i(5, 9), 1],
    [$i(10, 14), 2],
    [$i(15, 15), 3],
    [$i(16, 20), 2],
    [$i(21, 25), 1],
]);


explodeOverlaps:
Assert::equal(DateInterval::explodeOverlaps($empty), []);
Assert::equal($s(...DateInterval::explodeOverlaps($interval, $i(5, 15))), $s(
    $i(5, 9),
    $i(10, 15),
    $i(10, 15),
    $i(16, 20)
));
Assert::equal($s(...DateInterval::explodeOverlaps($i(5, 15), $i(10, 20), $i(15, 25))), $s(
    $i(5, 9),
    $i(10, 14),
    $i(10, 14),
    $i(15, 15),
    $i(15, 15),
    $i(15, 15),
    $i(16, 20),
    $i(16, 20),
    $i(21, 25)
));
