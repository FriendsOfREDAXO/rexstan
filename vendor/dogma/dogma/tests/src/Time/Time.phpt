<?php declare(strict_types = 1);

namespace Dogma\Tests\Time;

use Dogma\Tester\Assert;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\InvalidDateTimeException;
use Dogma\Time\Time;
use Dogma\ValueOutOfRangeException;

require_once __DIR__ . '/../bootstrap.php';

$timeString = '03:04:05.000006';
$time = new Time($timeString);
$microSeconds = 11045000006;
$denormalizedTime = new Time('27:04:05.000006');
$denormalizedMicroSeconds = 97445000006;


__construct:
Assert::throws(static function (): void {
    new Time(-200);
}, ValueOutOfRangeException::class);
Assert::throws(static function (): void {
    new Time('asdf');
}, InvalidDateTimeException::class);

Assert::same((new Time($timeString))->format(), $timeString);


createFromComponents:
Assert::throws(static function (): void {
    Time::createFromComponents(-1, 0, 0);
}, ValueOutOfRangeException::class);
Assert::type(Time::createFromComponents(3, 4, 5, 6), Time::class);
Assert::same(Time::createFromComponents(3, 4, 5, 6)->format(), $timeString);


createFromSeconds:
Assert::throws(static function (): void {
    Time::createFromSeconds(-1);
}, ValueOutOfRangeException::class);
Assert::type(Time::createFromSeconds((int) ($microSeconds / 1000000)), Time::class);
Assert::same(Time::createFromSeconds((int) ($microSeconds / 1000000))->format(), '03:04:05.000000');


createFromFormat:
Assert::throws(static function (): void {
    Time::createFromFormat(Time::DEFAULT_FORMAT, 'asdf');
}, InvalidDateTimeException::class);
Assert::type(Time::createFromFormat(Time::DEFAULT_FORMAT, $timeString), Time::class);
Assert::same(Time::createFromFormat(Time::DEFAULT_FORMAT, $timeString)->format(), $timeString);


normalize:
Assert::same($time->normalize()->getMicroTime(), $microSeconds);
Assert::same($denormalizedTime->normalize()->getMicroTime(), $microSeconds);


denormalize:
Assert::same($time->denormalize()->getMicroTime(), $denormalizedMicroSeconds);
Assert::same($denormalizedTime->denormalize()->getMicroTime(), $denormalizedMicroSeconds);


modify:
Assert::same($time->modify('+1 hour')->getMicroTime(), $microSeconds + 3600 * 1000000);
Assert::same($denormalizedTime->modify('+1 hour')->getMicroTime(), $denormalizedMicroSeconds + 3600 * 1000000);
// todo: overflows


getMicroTime:
Assert::same($time->getMicroTime(), $microSeconds);
Assert::same($denormalizedTime->getMicroTime(), $denormalizedMicroSeconds);


getHours:
Assert::same($time->getHours(), 3);
Assert::same($denormalizedTime->getHours(), 3);


getMinutes:
Assert::same($time->getMinutes(), 4);
Assert::same($denormalizedTime->getMinutes(), 4);


getSeconds:
Assert::same($time->getSeconds(), 5);
Assert::same($denormalizedTime->getSeconds(), 5);


getMicroseconds:
Assert::same($time->getMicroseconds(), 6);
Assert::same($denormalizedTime->getMicroseconds(), 6);

$before = new Time('02:00:00');
$after = new Time('04:00:00');


equals:
Assert::false($time->equals(new Time(0)));
Assert::true($time->equals(new Time($timeString)));


compare:
Assert::same($time->compare($before), 1);
Assert::same($time->compare($time), 0);
Assert::same($time->compare($after), -1);


isBefore:
Assert::false($time->isBefore($before));
Assert::false($time->isBefore($time));
Assert::true($time->isBefore($after));


isAfter:
Assert::true($time->isAfter($before));
Assert::false($time->isAfter($time));
Assert::false($time->isAfter($after));


isSameOrBefore:
Assert::false($time->isSameOrBefore($before));
Assert::true($time->isSameOrBefore($time));
Assert::true($time->isSameOrBefore($after));


isSameOrAfter:
Assert::true($time->isSameOrAfter($before));
Assert::true($time->isSameOrAfter($time));
Assert::false($time->isSameOrAfter($after));


isBetween:
Assert::false($time->isBetween(new Time('10:00:00'), new Time('12:00:00')));
Assert::true($time->isBetween(new Time('02:00:00'), new Time('04:00:00')));
Assert::true($time->isBetween(new Time('22:00:00'), new Time('04:00:00')));


// rounding ------------------------------------------------------------------------------------------------------------

$hour = DateTimeUnit::hour();
$minute = DateTimeUnit::minute();
$second = DateTimeUnit::second();
$milisecond = DateTimeUnit::milisecond();
$microsecond = DateTimeUnit::microsecond();
$hours = [0, 8, 16];
$minutes = [0, 15, 30, 45];
$seconds = [0, 15, 30, 45];
$miliseconds = [0, 250, 500, 750];
$microseconds = [0, 250000, 500000, 750000];
$upperTime = new Time('06:12:12.200000');
$criticalTime = new Time('23:59:59.999999');

Assert::equal((new Time('14:55:00.000000'))->roundUpTo($minute, [20]), new Time('15:20:00.000000'));


roundTo:
Assert::equal($time->roundTo($hour, $hours), new Time('00:00:00.000000'));
Assert::equal($time->roundTo($minute, $minutes), new Time('03:00:00.000000'));
Assert::equal($time->roundTo($second, $seconds), new Time('03:04:00.000000'));
Assert::equal($time->roundTo($milisecond, $miliseconds), new Time('03:04:05.000000'));
Assert::equal($time->roundTo($microsecond, $microseconds), new Time('03:04:05.000000'));

Assert::equal($upperTime->roundTo($hour, $hours), new Time('08:00:00.000000'));
Assert::equal($upperTime->roundTo($minute, $minutes), new Time('06:15:00.000000'));
Assert::equal($upperTime->roundTo($second, $seconds), new Time('06:12:15.000000'));
Assert::equal($upperTime->roundTo($milisecond, $miliseconds), new Time('06:12:12.250000'));
Assert::equal($upperTime->roundTo($microsecond, $microseconds), new Time('06:12:12.250000'));

Assert::equal($criticalTime->roundTo($hour, $hours), new Time('00:00:00.000000'));
Assert::equal($criticalTime->roundTo($minute, $minutes), new Time('00:00:00.000000'));
Assert::equal($criticalTime->roundTo($second, $seconds), new Time('00:00:00.000000'));
Assert::equal($criticalTime->roundTo($milisecond, $miliseconds), new Time('00:00:00.000000'));
Assert::equal($criticalTime->roundTo($microsecond, $microseconds), new Time('00:00:00.000000'));


roundUpTo:
Assert::equal($time->roundUpTo($hour, $hours), new Time('08:00:00.000000'));
Assert::equal($time->roundUpTo($minute, $minutes), new Time('03:15:00.000000'));
Assert::equal($time->roundUpTo($second, $seconds), new Time('03:04:15.000000'));
Assert::equal($time->roundUpTo($milisecond, $miliseconds), new Time('03:04:05.250000'));
Assert::equal($time->roundUpTo($microsecond, $microseconds), new Time('03:04:05.250000'));

Assert::equal($upperTime->roundUpTo($hour, $hours), new Time('08:00:00.000000'));
Assert::equal($upperTime->roundUpTo($minute, $minutes), new Time('06:15:00.000000'));
Assert::equal($upperTime->roundUpTo($second, $seconds), new Time('06:12:15.000000'));
Assert::equal($upperTime->roundUpTo($milisecond, $miliseconds), new Time('06:12:12.250000'));
Assert::equal($upperTime->roundUpTo($microsecond, $microseconds), new Time('06:12:12.250000'));

Assert::equal($criticalTime->roundUpTo($hour, $hours), new Time('00:00:00.000000'));
Assert::equal($criticalTime->roundUpTo($minute, $minutes), new Time('00:00:00.000000'));
Assert::equal($criticalTime->roundUpTo($second, $seconds), new Time('00:00:00.000000'));
Assert::equal($criticalTime->roundUpTo($milisecond, $miliseconds), new Time('00:00:00.000000'));
Assert::equal($criticalTime->roundUpTo($microsecond, $microseconds), new Time('00:00:00.000000'));


roundDownTo:
Assert::equal($time->roundDownTo($hour, $hours), new Time('00:00:00.000000'));
Assert::equal($time->roundDownTo($minute, $minutes), new Time('03:00:00.000000'));
Assert::equal($time->roundDownTo($second, $seconds), new Time('03:04:00.000000'));
Assert::equal($time->roundDownTo($milisecond, $miliseconds), new Time('03:04:05.000000'));
Assert::equal($time->roundDownTo($microsecond, $microseconds), new Time('03:04:05.000000'));

Assert::equal($upperTime->roundDownTo($hour, $hours), new Time('00:00:00.000000'));
Assert::equal($upperTime->roundDownTo($minute, $minutes), new Time('06:00:00.000000'));
Assert::equal($upperTime->roundDownTo($second, $seconds), new Time('06:12:00.000000'));
Assert::equal($upperTime->roundDownTo($milisecond, $miliseconds), new Time('06:12:12.000000'));
Assert::equal($upperTime->roundDownTo($microsecond, $microseconds), new Time('06:12:12.000000'));

Assert::equal($criticalTime->roundDownTo($hour, $hours), new Time('16:00:00.000000'));
Assert::equal($criticalTime->roundDownTo($minute, $minutes), new Time('23:45:00.000000'));
Assert::equal($criticalTime->roundDownTo($second, $seconds), new Time('23:59:45.000000'));
Assert::equal($criticalTime->roundDownTo($milisecond, $miliseconds), new Time('23:59:59.750000'));
Assert::equal($criticalTime->roundDownTo($microsecond, $microseconds), new Time('23:59:59.750000'));
