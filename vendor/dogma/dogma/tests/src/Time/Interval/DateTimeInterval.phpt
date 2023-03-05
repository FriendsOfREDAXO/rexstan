<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use DateTime as PhpDateTime;
use DateTimeImmutable;
use Dogma\Math\Interval\InvalidIntervalStringFormatException;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\Interval\DateTimeInterval;
use Dogma\Time\Interval\DateTimeIntervalSet;
use Dogma\Time\Interval\TimeInterval;
use Dogma\Time\InvalidFormattingStringException;
use Dogma\Time\InvalidIntervalStartEndOrderException;
use Dogma\Time\Microseconds;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\Time;

require_once __DIR__ . '/../../bootstrap.php';

$startTime = new DateTime('2000-01-10 00:00:00.000000');
$endTime = new DateTime('2000-01-20 00:00:00.000000');
$interval = new DateTimeInterval($startTime, $endTime);
$empty = DateTimeInterval::empty();
$all = DateTimeInterval::all();

$d = static function (int $day): DateTime {
    return new DateTime('2000-01-' . $day . ' 00:00:00');
};
$i = static function (int $start, int $end): DateTimeInterval {
    return new DateTimeInterval(
        new DateTime('2000-01-' . $start . ' 00:00:00.000000'),
        new DateTime('2000-01-' . $end . ' 00:00:00.000000')
    );
};
$s = static function (DateTimeInterval ...$items): DateTimeIntervalSet {
    return new DateTimeIntervalSet($items);
};


__construct:
Assert::exception(static function (): void {
    new DateTimeInterval(new DateTime('today'), new DateTime('yesterday'));
}, InvalidIntervalStartEndOrderException::class);


createFromString:
Assert::equal(DateTimeInterval::createFromString('2000-01-10 00:00,2000-01-20 00:00'), $interval);
Assert::equal(DateTimeInterval::createFromString('2000-01-10 00:00|2000-01-20 00:00'), $interval);
Assert::equal(DateTimeInterval::createFromString('2000-01-10 00:00/2000-01-20 00:00'), $interval);
Assert::equal(DateTimeInterval::createFromString('2000-01-10 00:00 - 2000-01-20 00:00'), $interval);
Assert::exception(static function (): void {
    DateTimeInterval::createFromString('foo|bar|baz');
}, InvalidIntervalStringFormatException::class);
Assert::exception(static function (): void {
    DateTimeInterval::createFromString('foo');
}, InvalidIntervalStringFormatException::class);


createFromStartAndLength:
Assert::equal(DateTimeInterval::createFromStartAndLength($startTime, DateTimeUnit::year(), 2), new DateTimeInterval($startTime, new DateTime('2002-01-10 00:00')));
Assert::equal(DateTimeInterval::createFromStartAndLength($startTime, DateTimeUnit::quarter(), 2), new DateTimeInterval($startTime, new DateTime('2000-07-10 00:00')));
Assert::equal(DateTimeInterval::createFromStartAndLength($startTime, DateTimeUnit::month(), 2), new DateTimeInterval($startTime, new DateTime('2000-03-10 00:00')));
Assert::equal(DateTimeInterval::createFromStartAndLength($startTime, DateTimeUnit::week(), 2), new DateTimeInterval($startTime, new DateTime('2000-01-24 00:00')));
Assert::equal(DateTimeInterval::createFromStartAndLength($startTime, DateTimeUnit::day(), 2), new DateTimeInterval($startTime, new DateTime('2000-01-12 00:00')));
Assert::equal(DateTimeInterval::createFromStartAndLength($startTime, DateTimeUnit::hour(), 2), new DateTimeInterval($startTime, new DateTime('2000-01-10 02:00')));
Assert::equal(DateTimeInterval::createFromStartAndLength($startTime, DateTimeUnit::minute(), 2), new DateTimeInterval($startTime, new DateTime('2000-01-10 00:02')));
Assert::equal(DateTimeInterval::createFromStartAndLength($startTime, DateTimeUnit::second(), 2), new DateTimeInterval($startTime, new DateTime('2000-01-10 00:00:02')));
Assert::equal(DateTimeInterval::createFromStartAndLength($startTime, DateTimeUnit::milisecond(), 2), new DateTimeInterval($startTime, new DateTime('2000-01-10 00:00:00.002')));
Assert::equal(DateTimeInterval::createFromStartAndLength($startTime, DateTimeUnit::microsecond(), 2), new DateTimeInterval($startTime, new DateTime('2000-01-10 00:00:00.000002')));


createFromDateAndTimeInterval:
Assert::equal(DateTimeInterval::createFromDateAndTimeInterval(
    new Date('2000-01-01'),
    new TimeInterval(new Time('10:00'), new Time('20:00'))
), DateTimeInterval::createFromString('2000-01-01 10:00 - 2000-01-01 20:00'));


createFromDateIntervalAndTime:
Assert::equal(DateTimeInterval::createFromDateIntervalAndTime(
    DateInterval::createFromString('2000-01-01 - 2000-01-05'),
    new Time('10:00')
), DateTimeInterval::createFromString('2000-01-01 10:00 - 2000-01-05 10:00'));


format:
Assert::same($interval->format('d|-d'), '10-20');
Assert::exception(static function () use ($interval): void {
    $interval->format('d|-d|-d');
}, InvalidFormattingStringException::class);


shift:
Assert::equal($interval->shift('+1 day'), $i(11, 21));


getStart:
Assert::equal($interval->getStart(), new DateTime('2000-01-10 00:00:00.000000'));


getEnd:
Assert::equal($interval->getEnd(), new DateTime('2000-01-20 00:00:00.000000'));


getSpan:
Assert::equal($interval->getSpan(), new DateTimeSpan(0, 0, 10));


toDateInterval:
Assert::equal($interval->toDateInterval(), new DateInterval(new Date('2000-01-10'), new Date('2000-01-19')));


getLengthInMicroseconds:
Assert::same($interval->getLengthInMicroseconds(), Microseconds::DAY * 10);
Assert::same($empty->getLengthInMicroseconds(), 0);


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
Assert::true($interval->containsValue($d(19)));
Assert::false($interval->containsValue($d(5)));
Assert::false($interval->containsValue($d(20)));


containsDateTime:
Assert::true($interval->containsDateTime(new PhpDateTime('2000-01-15')));
Assert::true($interval->containsDateTime(new DateTimeImmutable('2000-01-15')));


contains:
Assert::true($interval->contains($i(10, 20)));
Assert::true($interval->contains($i(10, 15)));
Assert::true($interval->contains($i(15, 20)));
Assert::false($interval->contains($i(5, 20)));
Assert::false($interval->contains($i(10, 25)));
Assert::false($interval->contains($i(1, 5)));
Assert::false($interval->contains($empty));


intersects:
Assert::true($interval->intersects($i(5, 22)));
Assert::true($interval->intersects($i(10, 20)));
Assert::true($interval->intersects($i(5, 15)));
Assert::true($interval->intersects($i(15, 25)));
Assert::false($interval->intersects($i(1, 5)));
Assert::false($interval->intersects($empty));


touches:
Assert::true($interval->touches($i(1, 10)));
Assert::true($interval->touches($i(20, 25)));
Assert::false($interval->touches($i(1, 11)));
Assert::false($interval->touches($i(19, 25)));
Assert::false($interval->touches($i(21, 25)));
Assert::false($interval->touches($empty));


split:
Assert::equal($interval->split(1), $s($interval));
Assert::equal($interval->split(2), $s($i(10, 15), $i(15, 20)));
Assert::equal($interval->split(3), $s(
    new DateTimeInterval(new DateTime('2000-01-10 00:00:00'), new DateTime('2000-01-13 08:00:00')),
    new DateTimeInterval(new DateTime('2000-01-13 08:00:00'), new DateTime('2000-01-16 16:00:00')),
    new DateTimeInterval(new DateTime('2000-01-16 16:00:00'), new DateTime('2000-01-20 00:00:00'))
));
Assert::equal($empty->split(5), $s());


splitBy:
Assert::equal($interval->splitBy([$d(5), $d(15), $d(25)]), $s($i(10, 15), $i(15, 20)));
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
Assert::equal($interval->difference($i(15, 30)), $s($i(10, 15), $i(20, 30)));
Assert::equal($interval->difference($i(5, 15)), $s($i(5, 10), $i(15, 20)));
Assert::equal($interval->difference($i(5, 15), $i(15, 30)), $s($i(5, 10), $i(20, 30)));
Assert::equal($interval->difference($i(25, 30)), $s($interval, $i(25, 30)));
Assert::equal($interval->difference($all), $s(
    new DateTimeInterval(new DateTime(DateTime::MIN), $d(10)),
    new DateTimeInterval($d(20), new DateTime(DateTime::MAX))
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
    new DateTimeInterval(new DateTime(DateTime::MIN), $d(10)),
    new DateTimeInterval($d(20), new DateTime(DateTime::MAX))
));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s($empty));


countOverlaps:
Assert::equal(DateTimeInterval::countOverlaps($empty), []);
Assert::equal(DateTimeInterval::countOverlaps($interval, $i(5, 15)), [
    [$i(5, 10), 1],
    [$i(10, 15), 2],
    [$i(15, 20), 1],
]);
Assert::equal(DateTimeInterval::countOverlaps(
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
Assert::equal(DateTimeInterval::explodeOverlaps($empty), []);
Assert::equal($s(...DateTimeInterval::explodeOverlaps($interval, $i(5, 15))), $s(
    $i(5, 10),
    $i(10, 15),
    $i(10, 15),
    $i(15, 20)
));

Assert::equal($s(...DateTimeInterval::explodeOverlaps(
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
