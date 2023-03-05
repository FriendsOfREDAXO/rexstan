<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use DateTimeImmutable;
use Dogma\Math\Interval\InvalidIntervalStringFormatException;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\Interval\DateTimeInterval;
use Dogma\Time\Interval\NightInterval;
use Dogma\Time\Interval\NightIntervalSet;
use Dogma\Time\InvalidDateTimeUnitException;
use Dogma\Time\InvalidIntervalStartEndOrderException;
use Dogma\Time\Span\DateSpan;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\Time;

require_once __DIR__ . '/../../bootstrap.php';

$startDate = new Date('2000-01-10');
$endDate = new Date('2000-01-21');
$interval = new NightInterval($startDate, $endDate);
$empty = NightInterval::empty();
$all = NightInterval::all();

$d = static function (int $day): Date {
    return new Date('2000-01-' . $day);
};
$i = static function (int $start, int $end): NightInterval {
    return new NightInterval(new Date('2000-01-' . $start), new Date('2000-01-' . $end));
};
$s = static function (NightInterval ...$items): NightIntervalSet {
    return new NightIntervalSet($items);
};


__construct:
Assert::exception(static function (): void {
    new NightInterval(new Date('today'), new Date('yesterday'));
}, InvalidIntervalStartEndOrderException::class);


createFromDateInterval:
Assert::equal(NightInterval::createFromDateInterval(DateInterval::empty()), NightInterval::empty());


createFromString:
Assert::equal(NightInterval::createFromString('2000-01-10,2000-01-21'), $interval);
Assert::equal(NightInterval::createFromString('2000-01-10|2000-01-21'), $interval);
Assert::equal(NightInterval::createFromString('2000-01-10/2000-01-21'), $interval);
Assert::equal(NightInterval::createFromString('2000-01-10 - 2000-01-21'), $interval);
Assert::equal(NightInterval::createFromString('[2000-01-10,2000-01-21]'), $interval);
Assert::equal(NightInterval::createFromString('[2000-01-10,2000-01-22)'), $interval);
Assert::equal(NightInterval::createFromString('(2000-01-09,2000-01-22)'), $interval);
Assert::equal(NightInterval::createFromString('(2000-01-09,2000-01-21]'), $interval);
Assert::exception(static function (): void {
    NightInterval::createFromString('foo|bar|baz');
}, InvalidIntervalStringFormatException::class);
Assert::exception(static function (): void {
    NightInterval::createFromString('foo');
}, InvalidIntervalStringFormatException::class);


createFromStartAndLength:
Assert::equal(NightInterval::createFromStartAndLength($startDate, DateTimeUnit::year(), 2), new NightInterval($startDate, new Date('2002-01-09')));
Assert::equal(NightInterval::createFromStartAndLength($startDate, DateTimeUnit::quarter(), 2), new NightInterval($startDate, new Date('2000-07-09')));
Assert::equal(NightInterval::createFromStartAndLength($startDate, DateTimeUnit::month(), 2), new NightInterval($startDate, new Date('2000-03-09')));
Assert::equal(NightInterval::createFromStartAndLength($startDate, DateTimeUnit::week(), 2), new NightInterval($startDate, new Date('2000-01-23')));
Assert::equal(NightInterval::createFromStartAndLength($startDate, DateTimeUnit::day(), 2), new NightInterval($startDate, new Date('2000-01-11')));
Assert::exception(static function () use ($startDate): void {
    NightInterval::createFromStartAndLength($startDate, DateTimeUnit::hour(), 2);
}, InvalidDateTimeUnitException::class);


toDateTimeInterval:
Assert::equal($interval->toDateTimeInterval(
    new Time('12:00'),
    new Time('12:00')
), DateTimeInterval::createFromString('2000-01-10 12:00 - 2000-01-21 12:00'));


toDateArray:
Assert::equal($i(1, 2)->toDateArray(), [$d(1)]);
Assert::equal($i(1, 3)->toDateArray(), [$d(1), $d(2)]);
Assert::equal($empty->toDateArray(), []);


format:
Assert::same($interval->format('d|-d'), '10-21');


shift:
Assert::equal($interval->shift('+1 day'), $i(11, 22));


getStart:
Assert::equal($interval->getStart(), new Date('2000-01-10'));


getEnd:
Assert::equal($interval->getEnd(), new Date('2000-01-21'));


getSpan:
Assert::equal($interval->getSpan(), new DateTimeSpan(0, 0, 11));


getDateSpan:
Assert::equal($interval->getDateSpan(), new DateSpan(0, 0, 11));


getLengthInDays:
Assert::same($i(1, 3)->getLengthInDays(), 2);
Assert::same($interval->getLengthInDays(), 11);
Assert::same($empty->getLengthInDays(), 0);


getNightsCount:
Assert::same($interval->getNightsCount(), 11);
Assert::same($empty->getNightsCount(), 0);


isEmpty:
Assert::false($interval->isEmpty());
Assert::false($all->isEmpty());
Assert::true($empty->isEmpty());


equals:
Assert::true($interval->equals($i(10, 21)));
Assert::false($interval->equals($i(10, 15)));
Assert::false($interval->equals($i(15, 21)));


compare:
Assert::same($interval->compare($i(10, 20)), 1);
Assert::same($interval->compare($i(10, 22)), -1);
Assert::same($interval->compare($interval), 0);
Assert::same($interval->compare($i(9, 20)), 1);
Assert::same($interval->compare($i(11, 22)), -1);


containsValue:
Assert::true($interval->containsValue($d(10)));
Assert::true($interval->containsValue($d(15)));
Assert::true($interval->containsValue($d(20)));
Assert::false($interval->containsValue($d(21)));
Assert::false($interval->containsValue($d(5)));
Assert::false($interval->containsValue($d(25)));
Assert::true($interval->containsValue(new DateTimeImmutable('2000-01-15')));


contains:
Assert::true($interval->contains($i(10, 21)));
Assert::true($interval->contains($i(10, 15)));
Assert::true($interval->contains($i(15, 21)));
Assert::false($interval->contains($i(15, 22)));
Assert::false($interval->contains($i(5, 21)));
Assert::false($interval->contains($i(10, 25)));
Assert::false($interval->contains($i(1, 5)));
Assert::false($interval->contains($empty));


intersects:
Assert::true($interval->intersects($i(5, 25)));
Assert::true($interval->intersects($i(10, 21)));
Assert::true($interval->intersects($i(5, 15)));
Assert::true($interval->intersects($i(15, 25)));
Assert::true($interval->intersects($i(20, 25)));
Assert::false($interval->intersects($i(21, 25)));
Assert::false($interval->intersects($i(1, 5)));
Assert::false($interval->intersects($empty));


touches:
Assert::false($interval->touches($i(1, 9)));
Assert::true($interval->touches($i(1, 10)));
Assert::false($interval->touches($i(1, 11)));
Assert::false($interval->touches($i(20, 25)));
Assert::true($interval->touches($i(21, 25)));
Assert::false($interval->touches($i(22, 25)));
Assert::false($interval->touches($empty));


split:
Assert::equal($interval->split(1), $s($interval));
Assert::equal($interval->split(2), $s($i(10, 16), $i(16, 21)));
Assert::equal($interval->split(3), $s($i(10, 14), $i(14, 17), $i(17, 21)));
Assert::equal($interval->split(4), $s($i(10, 13), $i(13, 16), $i(16, 18), $i(18, 21)));
Assert::equal($interval->split(11), $s($i(10, 11), $i(11, 12), $i(12, 13), $i(13, 14), $i(14, 15), $i(15, 16), $i(16, 17), $i(17, 18), $i(18, 19), $i(19, 20), $i(20, 21)));
Assert::equal($empty->split(5), $s());


splitBy:
Assert::equal($interval->splitBy([$d(5), $d(15), $d(25)]), $s($i(10, 15), $i(15, 21)));
Assert::equal($empty->splitBy([$d(5)]), $s());


envelope:
Assert::equal($interval->envelope($i(5, 15)), $i(5, 21));
Assert::equal($interval->envelope($i(15, 25)), $i(10, 25));
Assert::equal($interval->envelope($i(1, 5)), $i(1, 21));
Assert::equal($interval->envelope($i(25, 30)), $i(10, 30));
Assert::equal($interval->envelope($i(1, 5), $i(25, 30)), $i(1, 30));
Assert::equal($interval->envelope($empty), $interval);


intersect:
Assert::equal($interval->intersect($i(1, 15)), $i(10, 15));
Assert::equal($interval->intersect($i(15, 30)), $i(15, 21));
Assert::equal($interval->intersect($i(1, 18), $i(14, 30)), $i(14, 18));
Assert::equal($interval->intersect($i(1, 5)), $empty);
Assert::equal($interval->intersect($i(1, 5), $i(5, 15)), $empty);
Assert::equal($interval->intersect($empty), $empty);


union:
Assert::equal($interval->union($i(1, 15)), $s($i(1, 21)));
Assert::equal($interval->union($i(15, 30)), $s($i(10, 30)));
Assert::equal($interval->union($i(1, 15), $i(15, 30)), $s($i(1, 30)));
Assert::equal($interval->union($i(25, 30)), $s($interval, $i(25, 30)));
Assert::equal($interval->union($all), $s($all));
Assert::equal($interval->union($empty), $s($interval));


difference:
Assert::equal($interval->difference($i(15, 30)), $s($i(10, 15), $i(21, 30)));
Assert::equal($interval->difference($i(5, 15)), $s($i(5, 10), $i(15, 21)));
Assert::equal($interval->difference($i(5, 15), $i(15, 30)), $s($i(5, 10), $i(21, 30)));
Assert::equal($interval->difference($i(25, 30)), $s($interval, $i(25, 30)));
Assert::equal($interval->difference($all), $s(
    new NightInterval(new Date(Date::MIN), $d(10)),
    new NightInterval($d(21), new Date(Date::MAX))
));
Assert::equal($interval->difference($empty), $s($interval));


subtract:
Assert::equal($interval->subtract($i(5, 15)), $s($i(15, 21)));
Assert::equal($interval->subtract($i(15, 25)), $s($i(10, 15)));
Assert::equal($interval->subtract($i(13, 17)), $s($i(10, 13), $i(17, 21)));
Assert::equal($interval->subtract($i(5, 10), $i(20, 25)), $s($i(10, 20)));
Assert::equal($interval->subtract($empty), $s($interval));
Assert::equal($interval->subtract($all), $s());
Assert::equal($all->subtract($empty), $s($all));
Assert::equal($empty->subtract($empty), $s());


invert:
Assert::equal($interval->invert(), $s(
    new NightInterval(new Date(Date::MIN), $d(10)),
    new NightInterval($d(21), new Date(Date::MAX))
));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s($empty));


countOverlaps:
Assert::equal(NightInterval::countOverlaps($empty), []);
Assert::equal(NightInterval::countOverlaps($interval, $i(5, 15)), [
    [$i(5, 10), 1],
    [$i(10, 15), 2],
    [$i(15, 21), 1],
]);
Assert::equal(NightInterval::countOverlaps($i(5, 15), $i(10, 20), $i(15, 25)), [
    [$i(5, 10), 1],
    [$i(10, 15), 2],
    [$i(15, 20), 2],
    [$i(20, 25), 1],
]);


explodeOverlaps:
Assert::equal(NightInterval::explodeOverlaps($empty), []);
Assert::equal($s(...NightInterval::explodeOverlaps($interval, $i(5, 15))), $s(
    $i(5, 10),
    $i(10, 15),
    $i(10, 15),
    $i(15, 21)
));
Assert::equal($s(...NightInterval::explodeOverlaps($i(5, 15), $i(10, 20), $i(15, 25))), $s(
    $i(5, 10),
    $i(10, 15),
    $i(10, 15),
    $i(15, 20),
    $i(15, 20),
    $i(20, 25)
));
