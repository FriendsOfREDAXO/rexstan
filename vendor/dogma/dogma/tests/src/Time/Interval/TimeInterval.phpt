<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use Dogma\Math\Interval\InvalidIntervalStringFormatException;
use Dogma\Str;
use Dogma\Tester\Assert;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\Interval\TimeInterval;
use Dogma\Time\Interval\TimeIntervalSet;
use Dogma\Time\InvalidDateTimeUnitException;
use Dogma\Time\Microseconds;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\Span\TimeSpan;
use Dogma\Time\Time;
use function strval;

require_once __DIR__ . '/../../bootstrap.php';

$startTime = new Time('10:00:00.000000');
$endTime = new Time('20:00:00.000000');
$interval = new TimeInterval($startTime, $endTime);

$empty = TimeInterval::empty();
$all = TimeInterval::all();

$t = static function (int $hour): Time {
    return new Time(Str::padLeft(strval($hour), 2, '0') . ':00:00.000000');
};
$i = static function (int $start, int $end): TimeInterval {
    return new TimeInterval(
        new Time(Str::padLeft(strval($start), 2, '0') . ':00:00.000000'),
        new Time(Str::padLeft(strval($end), 2, '0') . ':00:00.000000')
    );
};
$s = static function (TimeInterval ...$items): TimeIntervalSet {
    return new TimeIntervalSet($items);
};


createFromString:
Assert::equal(TimeInterval::createFromString('10:00,20:00'), $interval);
Assert::equal(TimeInterval::createFromString('10:00|20:00'), $interval);
Assert::equal(TimeInterval::createFromString('10:00/20:00'), $interval);
Assert::equal(TimeInterval::createFromString('10:00 - 20:00'), $interval);
Assert::equal(TimeInterval::createFromString('00:00 - 00:00'), new TimeInterval($t(0), $t(0)));
Assert::equal(TimeInterval::createFromString('00:00 - 00:00')->getLengthInMicroseconds(), 0);
Assert::equal(TimeInterval::createFromString('00:00 - 24:00'), new TimeInterval($t(0), $t(24)));
Assert::equal(TimeInterval::createFromString('00:00 - 24:00')->getLengthInMicroseconds(), Microseconds::DAY);
Assert::exception(static function (): void {
    TimeInterval::createFromString('foo|bar|baz');
}, InvalidIntervalStringFormatException::class);
Assert::exception(static function (): void {
    TimeInterval::createFromString('foo');
}, InvalidIntervalStringFormatException::class);


createFromStartAndLength:
Assert::equal(TimeInterval::createFromStartAndLength($startTime, DateTimeUnit::hour(), 2), new TimeInterval($startTime, new Time('12:00')));
Assert::equal(TimeInterval::createFromStartAndLength($startTime, DateTimeUnit::minute(), 2), new TimeInterval($startTime, new Time('10:02')));
Assert::equal(TimeInterval::createFromStartAndLength($startTime, DateTimeUnit::second(), 2), new TimeInterval($startTime, new Time('10:00:02')));
Assert::equal(TimeInterval::createFromStartAndLength($startTime, DateTimeUnit::milisecond(), 2), new TimeInterval($startTime, new Time('10:00:00.002')));
Assert::equal(TimeInterval::createFromStartAndLength($startTime, DateTimeUnit::microsecond(), 2), new TimeInterval($startTime, new Time('10:00:00.000002')));
Assert::exception(static function () use ($startTime): void {
    TimeInterval::createFromStartAndLength($startTime, DateTimeUnit::day(), 2);
}, InvalidDateTimeUnitException::class);


shift:
Assert::equal($interval->shift('+1 hour'), $i(11, 21));


getStart:
Assert::equal($interval->getStart(), new Time('10:00:00.000000'));


getEnd:
Assert::equal($interval->getEnd(), new Time('20:00:00.000000'));


getSpan:
Assert::equal($interval->getSpan(), new DateTimeSpan(0, 0, 0, 10));


getTimeSpan:
Assert::equal($interval->getTimeSpan(), new TimeSpan(10));


getLengthInMicroseconds:
Assert::same($interval->getLengthInMicroseconds(), Microseconds::HOUR * 10);
Assert::same($empty->getLengthInMicroseconds(), 0);


isEmpty:
Assert::false($interval->isEmpty());
Assert::false($all->isEmpty());
Assert::true($empty->isEmpty());


isOverMidnight:
Assert::false($interval->isOverMidnight());
Assert::true(TimeInterval::createFromString('20:00 - 04:00')->isOverMidnight());


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
Assert::true($interval->containsValue($t(10)));
Assert::true($interval->containsValue($t(15)));
Assert::true($interval->containsValue($t(19)));
Assert::false($interval->containsValue($t(5)));
Assert::false($interval->containsValue($t(20)));


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


touches:
Assert::true($interval->touches($i(1, 10)));
Assert::true($interval->touches($i(20, 23)));
Assert::false($interval->touches($i(1, 11)));
Assert::false($interval->touches($i(19, 23)));
Assert::false($interval->touches($i(21, 23)));
Assert::false($interval->touches($empty));


split:
Assert::equal($interval->split(1), $s($interval));
Assert::equal($interval->split(2), $s($i(10, 15), $i(15, 20)));
Assert::equal($interval->split(3), $s(
    new TimeInterval(new Time('10:00:00'), new Time('13:20:00')),
    new TimeInterval(new Time('13:20:00'), new Time('16:40:00')),
    new TimeInterval(new Time('16:40:00'), new Time('20:00:00'))
));
Assert::equal($empty->split(5), $s());


splitBy:
Assert::equal($interval->splitBy([$t(5), $t(15), $t(25)]), $s($i(10, 15), $i(15, 20)));
Assert::equal($empty->splitBy([$t(5)]), $s());


envelope:
Assert::equal($interval->envelope($i(5, 15)), $i(5, 20));
Assert::equal($interval->envelope($i(15, 25)), $i(10, 25));
Assert::equal($interval->envelope($i(1, 5)), $i(1, 20));
Assert::equal($interval->envelope($i(21, 25)), $i(10, 25));
Assert::equal($interval->envelope($i(4, 5), $i(21, 25)), $i(4, 25));
Assert::equal($interval->envelope($empty), $interval);


intersect:
Assert::equal($interval->intersect($i(1, 15)), $s($i(10, 15)));
Assert::equal($interval->intersect($i(15, 25)), $s($i(15, 20)));
Assert::equal($interval->intersect($i(1, 18), $i(14, 25)), $s($i(14, 18)));
Assert::equal($interval->intersect($i(1, 5)), new TimeIntervalSet([]));
Assert::equal($interval->intersect($i(1, 5), $i(5, 15)), new TimeIntervalSet([]));
Assert::equal($interval->intersect($empty), new TimeIntervalSet([]));

// intersecting denormalized and normalized intervals
$interval1 = TimeInterval::createFromString('11:16 - 03:31');
$interval2 = TimeInterval::createFromString('03:25 - 09:30');
Assert::true($interval1->intersects($interval2));
Assert::equal($interval1->intersect($interval2)->format('h:i| - h:i'), '03:25 - 03:31');
Assert::equal($interval2->intersect($interval1)->format('h:i| - h:i'), '03:25 - 03:31');


union:
Assert::equal($interval->union($i(1, 15)), $s($i(1, 20)));
Assert::equal($interval->union($i(15, 25)), $s($i(10, 25)));
Assert::equal($interval->union($i(4, 15), $i(15, 25)), $s($i(4, 25)));
Assert::equal($interval->union($i(25, 26)), $s($i(25, 26), $interval));
Assert::equal($interval->union($all), $s($all));
Assert::equal($interval->union($empty), $s($interval));


difference:
Assert::equal($interval->difference($i(15, 25)), $s($i(10, 15), $i(20, 25)));
Assert::equal($interval->difference($i(5, 15)), $s($i(5, 10), $i(15, 20)));
Assert::equal($interval->difference($i(5, 15), $i(15, 25)), $s($i(5, 10), $i(20, 25)));
Assert::equal($interval->difference($i(22, 25)), $s($interval, $i(22, 25)));
Assert::equal($interval->difference($all), $s(
    new TimeInterval(new Time(Time::MIN), $t(10)),
    new TimeInterval($t(20), new Time(Time::MAX))
));
Assert::equal($interval->difference($empty), $s($interval));


subtract:
Assert::equal($interval->subtract($i(5, 15)), $s($i(15, 20)));
Assert::equal($interval->subtract($i(15, 25)), $s($i(10, 15)));
Assert::equal($interval->subtract($i(13, 17)), $s($i(10, 13), $i(17, 20)));
Assert::equal($interval->subtract($i(5, 10), $i(20, 25)), $s($i(10, 20)));
Assert::equal($interval->subtract($empty), $s($interval));
Assert::equal($interval->subtract($all), $s());
Assert::equal($all->subtract($empty), $s($all));
Assert::equal($empty->subtract($empty), $s());


invert:
Assert::equal($interval->invert(), $s(
    new TimeInterval(new Time(Time::MIN), $t(10)),
    new TimeInterval($t(20), new Time(Time::MAX))
));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s($empty));


countOverlaps:
Assert::equal(TimeInterval::countOverlaps($empty), []);
Assert::equal(TimeInterval::countOverlaps($interval, $i(5, 15)), [
    [$i(5, 10), 1],
    [$i(10, 15), 2],
    [$i(15, 20), 1],
]);
Assert::equal(TimeInterval::countOverlaps(
    $i(5, 15),
    $i(10, 20),
    $i(15, 25)
), [
    [$i(5, 10), 1],
    [$i(10, 15), 2],
    [$i(15, 20), 2],
    [$i(20, 25), 1],
]);


explodeOverlaps:
Assert::equal(TimeInterval::explodeOverlaps($empty), []);
Assert::equal($s(...TimeInterval::explodeOverlaps($interval, $i(5, 15))), $s(
    $i(5, 10),
    $i(10, 15),
    $i(10, 15),
    $i(15, 20)
));
Assert::equal($s(...TimeInterval::explodeOverlaps(
    $i(5, 15),
    $i(10, 20),
    $i(15, 25)
)), $s(
    $i(5, 10),
    $i(10, 15),
    $i(10, 15),
    $i(15, 20),
    $i(15, 20),
    $i(20, 25)
));
