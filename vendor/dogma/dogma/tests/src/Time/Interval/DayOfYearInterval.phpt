<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use Dogma\Math\Interval\InvalidIntervalStringFormatException;
use Dogma\Str;
use Dogma\Tester\Assert;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\DayOfYear;
use Dogma\Time\Interval\DayOfYearInterval;
use Dogma\Time\Interval\DayOfYearIntervalSet;
use Dogma\Time\InvalidDateTimeUnitException;
use function strval;

require_once __DIR__ . '/../../bootstrap.php';

$startDay = new DayOfYear('01-10');
$endDay = new DayOfYear('01-20');
$interval = new DayOfYearInterval($startDay, $endDay);

$empty = DayOfYearInterval::empty();
$all = DayOfYearInterval::all();

$d = static function (int $day): DayOfYear {
    return new DayOfYear('01-' . Str::padLeft(strval($day), 2, '0'));
};
$i = static function (int $start, int $end): DayOfYearInterval {
    return new DayOfYearInterval(
        new DayOfYear($start),
        new DayOfYear($end)
    );
};
$s = static function (DayOfYearInterval ...$items): DayOfYearIntervalSet {
    return new DayOfYearIntervalSet($items);
};


createFromString:
Assert::equal(DayOfYearInterval::createFromString('01-10,01-20'), $interval);
Assert::equal(DayOfYearInterval::createFromString('01-10|01-20'), $interval);
Assert::equal(DayOfYearInterval::createFromString('01-10/01-20'), $interval);
Assert::equal(DayOfYearInterval::createFromString('01-10 - 01-20'), $interval);
Assert::exception(static function (): void {
    DayOfYearInterval::createFromString('foo|bar|baz');
}, InvalidIntervalStringFormatException::class);
Assert::exception(static function (): void {
    DayOfYearInterval::createFromString('foo');
}, InvalidIntervalStringFormatException::class);


createFromStartAndLength:
Assert::equal(DayOfYearInterval::createFromStartAndLength($startDay, DateTimeUnit::quarter(), 2), new DayOfYearInterval($startDay, new DayOfYear('07-10')));
Assert::equal(DayOfYearInterval::createFromStartAndLength($startDay, DateTimeUnit::month(), 2), new DayOfYearInterval($startDay, new DayOfYear('03-10')));
Assert::equal(DayOfYearInterval::createFromStartAndLength($startDay, DateTimeUnit::week(), 2), new DayOfYearInterval($startDay, new DayOfYear('01-24')));
Assert::equal(DayOfYearInterval::createFromStartAndLength($startDay, DateTimeUnit::day(), 2), new DayOfYearInterval($startDay, new DayOfYear('01-12')));
Assert::exception(static function () use ($startDay): void {
    DayOfYearInterval::createFromStartAndLength($startDay, DateTimeUnit::hour(), 2);
}, InvalidDateTimeUnitException::class);
Assert::exception(static function () use ($startDay): void {
    DayOfYearInterval::createFromStartAndLength($startDay, DateTimeUnit::year(), 2);
}, InvalidDateTimeUnitException::class);


shift:
Assert::equal($interval->shift('+1 day'), $i(11, 21));


getStart:
Assert::equal($interval->getStart(), new DayOfYear('01-10'));


getEnd:
Assert::equal($interval->getEnd(), new DayOfYear('01-20'));


isEmpty:
Assert::false($interval->isEmpty());
Assert::false($all->isEmpty());
Assert::true($empty->isEmpty());


isOverEndOfYear:
Assert::false($interval->isOverEndOfYear());
Assert::true(DayOfYearInterval::createFromString('12-01 - 01-12')->isOverEndOfYear());


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
Assert::false($interval->containsValue($d(21)));


contains:
Assert::true($interval->contains($i(10, 20)));
Assert::true($interval->contains($i(10, 15)));
Assert::true($interval->contains($i(15, 20)));
Assert::false($interval->contains($i(5, 20)));
Assert::false($interval->contains($i(10, 23)));
Assert::false($interval->contains($i(1, 5)));
Assert::false($interval->contains($empty));


intersects:
Assert::true($interval->intersects($i(5, 22)));
Assert::true($interval->intersects($i(10, 20)));
Assert::true($interval->intersects($i(5, 15)));
Assert::true($interval->intersects($i(15, 23)));
Assert::false($interval->intersects($i(1, 5)));
Assert::false($interval->intersects($empty));


$i1 = new DayOfYearInterval(
    DayOfYear::createFromMonthAndDay(12, 31),
    DayOfYear::createFromMonthAndDay(1, 1)
);
$i2 = new DayOfYearInterval(
    DayOfYear::createFromMonthAndDay(12, 31),
    DayOfYear::createFromMonthAndDay(1, 3)
);
Assert::true($i1->intersects($i2));


touches:
Assert::true($interval->touches($i(1, 9)));
Assert::true($interval->touches($i(21, 23)));
Assert::false($interval->touches($i(1, 10)));
Assert::false($interval->touches($i(20, 23)));
Assert::false($interval->touches($i(22, 23)));
Assert::false($interval->touches($empty));


split:
Assert::equal($interval->split(1), $s($interval));
Assert::equal($interval->split(2), $s($i(10, 15), $i(16, 20)));
Assert::equal($interval->split(3), $s(
    new DayOfYearInterval(new DayOfYear('01-10'), new DayOfYear('01-13')),
    new DayOfYearInterval(new DayOfYear('01-14'), new DayOfYear('01-16')),
    new DayOfYearInterval(new DayOfYear('01-17'), new DayOfYear('01-20'))
));
Assert::equal($empty->split(5), $s());


splitBy:
Assert::equal($interval->splitBy([$d(5), $d(15), $d(25)]), $s($i(10, 15), $i(16, 20)));
Assert::equal($empty->splitBy([$d(5)]), $s());


envelope:
Assert::equal($interval->envelope($i(5, 15)), $i(5, 20));
Assert::equal($interval->envelope($i(15, 368)), $i(10, 368));
Assert::equal($interval->envelope($i(1, 5)), $i(1, 20));
Assert::equal($interval->envelope($i(21, 368)), $i(10, 368));
Assert::equal($interval->envelope($i(4, 5), $i(21, 368)), $i(4, 368));
Assert::equal($interval->envelope($empty), $interval);


intersect:
Assert::equal($interval->intersect($i(1, 15)), $s($i(10, 15)));
Assert::equal($interval->intersect($i(15, 375)), $s($i(15, 20)));
Assert::equal($interval->intersect($i(1, 18), $i(14, 375)), $s($i(14, 18)));
Assert::equal($interval->intersect($i(1, 5)), new DayOfYearIntervalSet([]));
Assert::equal($interval->intersect($i(1, 5), $i(5, 15)), new DayOfYearIntervalSet([]));
Assert::equal($interval->intersect($empty), new DayOfYearIntervalSet([]));

// intersecting denormalized and normalized intervals
$interval1 = DayOfYearInterval::createFromString('11-16 - 03-31');
$interval2 = DayOfYearInterval::createFromString('03-25 - 09-30');
Assert::true($interval1->intersects($interval2));
Assert::equal($interval1->intersect($interval2)->format(), '03-25 - 03-31');
Assert::equal($interval2->intersect($interval1)->format(), '03-25 - 03-31');


union:
Assert::equal($interval->union($i(1, 15)), $s($i(1, 20)));
Assert::equal($interval->union($i(15, 368)), $s($i(10, 368)));
Assert::equal($interval->union($i(4, 15), $i(15, 368)), $s($i(4, 368)));
Assert::equal($interval->union($i(368, 369)), $s($i(368, 369), $interval));
Assert::equal($interval->union($all), $s($all));
Assert::equal($interval->union($empty), $s($interval));


difference:
Assert::equal($interval->difference($i(15, 368)), $s($i(10, 14), $i(21, 368)));
Assert::equal($interval->difference($i(5, 15)), $s($i(5, 9), $i(16, 20)));
Assert::equal($interval->difference($i(5, 15), $i(15, 368)), $s($i(5, 9), $i(21, 368)));
Assert::equal($interval->difference($i(22, 368)), $s($interval, $i(22, 368)));
Assert::equal($interval->difference($all), $s(
    new DayOfYearInterval(new DayOfYear(DayOfYear::MIN), $d(9)),
    new DayOfYearInterval($d(21), new DayOfYear(DayOfYear::MAX))
));
Assert::equal($interval->difference($empty), $s($interval));


subtract:
Assert::equal($interval->subtract($i(5, 15)), $s($i(15, 20)));
Assert::equal($interval->subtract($i(15, 368)), $s($i(10, 15)));
Assert::equal($interval->subtract($i(13, 17)), $s($i(10, 13), $i(17, 20)));
Assert::equal($interval->subtract($i(5, 10), $i(20, 368)), $s($i(10, 20)));
Assert::equal($interval->subtract($empty), $s($interval));
Assert::equal($interval->subtract($all), $s());
Assert::equal($all->subtract($empty), $s($all));
Assert::equal($empty->subtract($empty), $s());


invert:
Assert::equal($interval->invert(), $s(
    new DayOfYearInterval(new DayOfYear(DayOfYear::MIN), $d(10)),
    new DayOfYearInterval($d(20), new DayOfYear(DayOfYear::MAX))
));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s($empty));


countOverlaps:
Assert::equal(DayOfYearInterval::countOverlaps($empty), []);
Assert::equal(DayOfYearInterval::countOverlaps($interval, $all), [
    [$i(1, 9), 1],
    [$i(10, 20), 2],
    [new DayOfYearInterval($d(21), new DayOfYear(366)), 1],
]);
Assert::equal(DayOfYearInterval::countOverlaps($interval, $i(5, 15)), [
    [$i(5, 9), 1],
    [$i(10, 15), 2],
    [$i(16, 20), 1],
]);
Assert::equal(DayOfYearInterval::countOverlaps(
    $i(5, 15),
    $i(10, 20),
    $i(15, 368)
), [
    [$i(5, 9), 1],
    [$i(10, 14), 2],
    [$i(15, 15), 3],
    [$i(16, 20), 2],
    [$i(21, 368), 1],
]);


explodeOverlaps:
Assert::equal(DayOfYearInterval::explodeOverlaps($empty), []);
Assert::equal(DayOfYearInterval::explodeOverlaps($interval, $all), [
    $i(1, 9),
    $i(10, 20),
    $i(10, 20),
    new DayOfYearInterval($d(21), new DayOfYear(366)),
]);
Assert::equal($s(...DayOfYearInterval::explodeOverlaps($interval, $i(5, 15))), $s(
    $i(5, 9),
    $i(10, 15),
    $i(10, 15),
    $i(16, 20)
));
Assert::equal($s(...DayOfYearInterval::explodeOverlaps(
    $i(5, 15),
    $i(10, 20),
    $i(15, 368)
)), $s(
    $i(5, 9),
    $i(10, 14),
    $i(10, 14),
    $i(15, 15),
    $i(15, 15),
    $i(15, 15),
    $i(16, 20),
    $i(16, 20),
    $i(21, 368)
));
