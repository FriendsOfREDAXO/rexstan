<?php declare(strict_types = 1);

namespace Dogma\Tests\Time;

use DateTime as PhpDateTime;
use DateTimeImmutable;
use DateTimeZone;
use Dogma\InvalidValueException;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\DayOfWeek;
use Dogma\Time\InvalidDateTimeException;
use Dogma\Time\Month;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\Time;
use Dogma\Time\TimeZone;

require_once __DIR__ . '/../bootstrap.php';

TimeZone::setDefault(TimeZone::get(TimeZone::EUROPE_PRAGUE));

$utcTimeZone = new DateTimeZone('UTC');
$localTimeZone = TimeZone::getDefault();
$localOffsetTimeZone = new DateTimeZone('+01:00');

$dateTimeString = '2000-01-02 03:04:05.000006';
$dateTimeStringUtc = '2000-01-02 02:04:05.000006';
$timestamp = 946778645;
$floatTimestamp = 946778645.000006;
$microTimestamp = 946778645000006;
$date = new Date('2000-01-02');
$time = new Time('03:04:05.000006');
$dateTime = new DateTime($dateTimeString);
$dateTimeByOffset = new DateTime($dateTimeString, $localOffsetTimeZone);
$dateTimeNative = new PhpDateTime($dateTimeString);
$dateTimeImmutable = new DateTimeImmutable($dateTimeString);


createFromFormat:
Assert::type(DateTime::createFromFormat(DateTime::DEFAULT_FORMAT, $dateTimeString), DateTime::class);
Assert::same(DateTime::createFromFormat(DateTime::DEFAULT_FORMAT, $dateTimeString)->format(), $dateTimeString);
Assert::equal(DateTime::createFromFormat(DateTime::DEFAULT_FORMAT, $dateTimeString, $utcTimeZone)->getTimezone(), $utcTimeZone);
Assert::exception(static function (): void {
    DateTime::createFromFormat('Y-m-d', '12:00:00');
}, InvalidDateTimeException::class);


createFromAnyFormat:
$dateTimeStringOffset = '2000-01-02 03:04:05.000006+02:00';
$dateTimeStringOffset2 = '2000-01-02 03:04:05+02:00';
Assert::type(DateTime::createFromAnyFormat(DateTime::SAFE_FORMATS, $dateTimeStringOffset), DateTime::class);
Assert::same(DateTime::createFromAnyFormat(DateTime::SAFE_FORMATS, $dateTimeStringOffset)->format('Y-m-d H:i:s.uP'), $dateTimeStringOffset);
Assert::equal(DateTime::createFromAnyFormat(DateTime::SAFE_FORMATS, $dateTimeStringOffset)->format('Y-m-d H:i:sP'), $dateTimeStringOffset2);
Assert::exception(static function (): void {
    DateTime::createFromAnyFormat(DateTime::SAFE_FORMATS, '2000-01-02 12:00:00');
}, InvalidDateTimeException::class);


createFromTimestamp:
Assert::type(DateTime::createFromTimestamp($timestamp), DateTime::class);
Assert::same(DateTime::createFromTimestamp($timestamp, $utcTimeZone)->format(), '2000-01-02 02:04:05.000000');
Assert::same(DateTime::createFromTimestamp($timestamp, $localTimeZone)->format(), '2000-01-02 03:04:05.000000');
Assert::same(DateTime::createFromTimestamp($timestamp)->format(), '2000-01-02 03:04:05.000000');


createFromFloatTimestamp:
Assert::type(DateTime::createFromFloatTimestamp($floatTimestamp), DateTime::class);
Assert::same(DateTime::createFromFloatTimestamp($floatTimestamp, $utcTimeZone)->format(), $dateTimeStringUtc);
Assert::same(DateTime::createFromFloatTimestamp($floatTimestamp, $localTimeZone)->format(), $dateTimeString);
Assert::same(DateTime::createFromFloatTimestamp($floatTimestamp)->format(), $dateTimeString);


createFromMicroTimestamp:
Assert::type(DateTime::createFromMicroTimestamp($microTimestamp), DateTime::class);
Assert::same(DateTime::createFromMicroTimestamp($microTimestamp, $utcTimeZone)->format(), $dateTimeStringUtc);
Assert::same(DateTime::createFromMicroTimestamp($microTimestamp, $localTimeZone)->format(), $dateTimeString);
Assert::same(DateTime::createFromMicroTimestamp($microTimestamp)->format(), $dateTimeString);


createFromDateTimeInterface:
Assert::type(DateTime::createFromDateTimeInterface($dateTime), DateTime::class);
Assert::same(DateTime::createFromDateTimeInterface($dateTime)->format(), $dateTimeString);
Assert::same(DateTime::createFromDateTimeInterface($dateTime, $utcTimeZone)->format(), $dateTimeStringUtc);
Assert::same(DateTime::createFromDateTimeInterface($dateTime, $localTimeZone)->format(), $dateTimeString);
Assert::type(DateTime::createFromDateTimeInterface($dateTimeNative), DateTime::class);
Assert::same(DateTime::createFromDateTimeInterface($dateTimeNative)->format(), $dateTimeString);
Assert::same(DateTime::createFromDateTimeInterface($dateTimeNative, $utcTimeZone)->format(), $dateTimeStringUtc);
Assert::same(DateTime::createFromDateTimeInterface($dateTimeNative, $localTimeZone)->format(), $dateTimeString);
Assert::type(DateTime::createFromDateTimeInterface($dateTimeImmutable), DateTime::class);
Assert::same(DateTime::createFromDateTimeInterface($dateTimeImmutable)->format(), $dateTimeString);
Assert::same(DateTime::createFromDateTimeInterface($dateTimeImmutable, $utcTimeZone)->format(), $dateTimeStringUtc);
Assert::same(DateTime::createFromDateTimeInterface($dateTimeImmutable, $localTimeZone)->format(), $dateTimeString);


createFromDateAndTime:
Assert::type(DateTime::createFromDateAndTime($date, $time), DateTime::class);
Assert::same(DateTime::createFromDateAndTime($date, $time)->format(), $dateTimeString);
Assert::same(DateTime::createFromDateAndTime($date, $time, $utcTimeZone)->format(), $dateTimeString); // there is no timestamp. timezone is set as provided
Assert::same(DateTime::createFromDateAndTime($date, $time, $localTimeZone)->format(), $dateTimeString);


format:
Assert::same((new DateTime($dateTimeString))->format(), $dateTimeString);

$today = new DateTime('today 12:00');
$today2 = new DateTime('today 13:00');
$todayDate = new Date('today');

$yesterday = new DateTime('yesterday 12:00');
$yesterdayDate = new Date('yesterday');

$tomorrow = new DateTime('tomorrow 12:00');
$tomorrowDate = new Date('tomorrow');


getDate:
Assert::type($today->getDate(), Date::class);
Assert::same($today->getDate()->format(), date(Date::DEFAULT_FORMAT));


getTime:
Assert::type($today->getTime(), Time::class);
Assert::equal($today->getTime(), new Time('12:00:00'));


setTime:
Assert::same($today->setTime(3, 4, 5, 6)->format(Time::DEFAULT_FORMAT), '03:04:05.000006');
Assert::same($today->setTime('03:04:05.000006')->format(Time::DEFAULT_FORMAT), '03:04:05.000006');
Assert::same($today->setTime(new Time('03:04:05.000006'))->format(Time::DEFAULT_FORMAT), '03:04:05.000006');


difference:
Assert::equal($today->difference($today), new DateTimeSpan(0, 0, 0));
Assert::equal($today->difference($yesterday), new DateTimeSpan(0, 0, -1));
Assert::equal($today->difference($tomorrow), new DateTimeSpan(0, 0, 1));


compare:
Assert::same($today->compare($yesterday), 1);
Assert::same($today->compare($today), 0);
Assert::same($today->compare($tomorrow), -1);


equals:
Assert::false($today->equals($yesterday));
Assert::false($today->equals($tomorrow));
Assert::false($today->equals($today2));
Assert::true($today->equals($today));

Assert::true($dateTime->equals($dateTimeByOffset));


equalsUpTo:
Assert::false($dateTime->equalsUpTo(new DateTime('2001-02-03 04:05:06.007008'), DateTimeUnit::year()));
Assert::true($dateTime->equalsUpTo(new DateTime('2000-02-03 04:05:06.007008'), DateTimeUnit::year()));
Assert::false($dateTime->equalsUpTo(new DateTime('2000-02-03 04:05:06.007008'), DateTimeUnit::month()));
Assert::true($dateTime->equalsUpTo(new DateTime('2000-01-03 04:05:06.007008'), DateTimeUnit::month()));
Assert::false($dateTime->equalsUpTo(new DateTime('2000-01-03 04:05:06.007008'), DateTimeUnit::day()));
Assert::true($dateTime->equalsUpTo(new DateTime('2000-01-02 04:05:06.007008'), DateTimeUnit::day()));
Assert::false($dateTime->equalsUpTo(new DateTime('2000-01-02 04:05:06.007008'), DateTimeUnit::hour()));
Assert::true($dateTime->equalsUpTo(new DateTime('2000-01-02 03:05:06.007008'), DateTimeUnit::hour()));
Assert::false($dateTime->equalsUpTo(new DateTime('2000-01-02 03:05:06.007008'), DateTimeUnit::minute()));
Assert::true($dateTime->equalsUpTo(new DateTime('2000-01-02 03:04:06.007008'), DateTimeUnit::minute()));
Assert::false($dateTime->equalsUpTo(new DateTime('2000-01-02 03:04:06.007008'), DateTimeUnit::second()));
Assert::true($dateTime->equalsUpTo(new DateTime('2000-01-02 03:04:05.007008'), DateTimeUnit::second()));
Assert::false($dateTime->equalsUpTo(new DateTime('2000-01-02 03:04:05.007008'), DateTimeUnit::milisecond()));
Assert::true($dateTime->equalsUpTo(new DateTime('2000-01-02 03:04:05.000008'), DateTimeUnit::milisecond()));
Assert::false($dateTime->equalsUpTo(new DateTime('2000-01-02 03:04:05.000008'), DateTimeUnit::microsecond()));
Assert::true($dateTime->equalsUpTo(new DateTime('2000-01-02 03:04:05.000006'), DateTimeUnit::microsecond()));

Assert::false($dateTime->equalsUpTo(new DateTime('2000-04-02 03:04:05.000006'), DateTimeUnit::quarter()));
Assert::true($dateTime->equalsUpTo(new DateTime('2000-03-02 03:04:05.000006'), DateTimeUnit::quarter()));
Assert::false($dateTime->equalsUpTo(new DateTime('2000-01-03 03:04:05.000006'), DateTimeUnit::week()));
Assert::true($dateTime->equalsUpTo(new DateTime('2000-01-01 03:04:05.000006'), DateTimeUnit::week()));


timeOffsetEquals:
Assert::false($dateTime->timeOffsetEquals(new DateTime($dateTimeString, $utcTimeZone)));
Assert::true($dateTime->timeOffsetEquals(new DateTime($dateTimeString, $localTimeZone)));
Assert::true($dateTime->timeOffsetEquals(new DateTime($dateTimeString, $localOffsetTimeZone)));


isBefore:
Assert::false($today->isBefore($yesterday));
Assert::false($today->isBefore($today));
Assert::true($today->isBefore($tomorrow));


isAfter:
Assert::true($today->isAfter($yesterday));
Assert::false($today->isAfter($today));
Assert::false($today->isAfter($tomorrow));


isBetween:
Assert::false($yesterday->isBetween($today, $tomorrow));
Assert::false($tomorrow->isBetween($today, $yesterday));
Assert::true($yesterday->isBetween($yesterday, $tomorrow));
Assert::true($today->isBetween($yesterday, $tomorrow));
Assert::true($tomorrow->isBetween($yesterday, $tomorrow));


isFuture:
Assert::false($yesterday->isFuture());
Assert::true($tomorrow->isFuture());


isPast:
Assert::false($tomorrow->isPast());
Assert::true($yesterday->isPast());


isSameDay:
Assert::false($today->isSameDay($yesterday));
Assert::false($today->isSameDay($yesterdayDate));
Assert::false($today->isSameDay($tomorrow));
Assert::false($today->isSameDay($tomorrowDate));
Assert::true($today->isSameDay($today));
Assert::true($today->isSameDay($today2));
Assert::true($today->isSameDay($todayDate));


isBeforeDay:
Assert::false($today->isBeforeDay($yesterday));
Assert::false($today->isBeforeDay($yesterdayDate));
Assert::false($today->isBeforeDay($today));
Assert::false($today->isBeforeDay($today2));
Assert::false($today->isBeforeDay($todayDate));
Assert::true($today->isBeforeDay($tomorrow));
Assert::true($today->isBeforeDay($tomorrowDate));


isAfterDay:
Assert::true($today->isAfterDay($yesterday));
Assert::true($today->isAfterDay($yesterdayDate));
Assert::false($today->isAfterDay($today));
Assert::false($today->isAfterDay($today2));
Assert::false($today->isAfterDay($tomorrowDate));
Assert::false($today->isAfterDay($tomorrow));
Assert::false($today->isAfterDay($tomorrowDate));


isBetweenDays:
Assert::false($yesterday->isBetweenDays($today, $tomorrow));
Assert::false($yesterday->isBetweenDays($todayDate, $todayDate));
Assert::false($tomorrow->isBetweenDays($today, $yesterday));
Assert::false($tomorrow->isBetweenDays($todayDate, $yesterdayDate));
Assert::true($yesterday->isBetweenDays($yesterday, $tomorrow));
Assert::true($yesterday->isBetweenDays($yesterdayDate, $tomorrowDate));
Assert::true($today->isBetweenDays($yesterday, $tomorrow));
Assert::true($today->isBetweenDays($yesterdayDate, $tomorrowDate));
Assert::true($tomorrow->isBetweenDays($yesterday, $tomorrow));
Assert::true($tomorrow->isBetweenDays($yesterdayDate, $tomorrowDate));


isToday:
Assert::false($yesterday->isToday());
Assert::false($tomorrow->isToday());
Assert::true($today->isToday());


isYesterday:
Assert::false($tomorrow->isYesterday());
Assert::false($today->isYesterday());
Assert::true($yesterday->isYesterday());


isTomorrow:
Assert::false($yesterday->isTomorrow());
Assert::false($today->isTomorrow());
Assert::true($tomorrow->isTomorrow());


$monday = new Date('2016-11-07');
$friday = new Date('2016-11-04');
$saturday = new Date('2016-11-05');
$sunday = new Date('2016-11-06');


isDayOfWeek:
Assert::false($dateTime->isDayOfWeek(DayOfWeek::monday()));
Assert::false($dateTime->isDayOfWeek(DayOfWeek::tuesday()));
Assert::false($dateTime->isDayOfWeek(DayOfWeek::wednesday()));
Assert::false($dateTime->isDayOfWeek(DayOfWeek::thursday()));
Assert::false($dateTime->isDayOfWeek(DayOfWeek::friday()));
Assert::false($dateTime->isDayOfWeek(DayOfWeek::saturday()));
Assert::true($dateTime->isDayOfWeek(DayOfWeek::sunday()));

Assert::true($monday->isDayOfWeek(1));
Assert::true($monday->isDayOfWeek(DayOfWeek::monday()));
Assert::false($monday->isDayOfWeek(7));
Assert::false($monday->isDayOfWeek(DayOfWeek::sunday()));
Assert::exception(static function () use ($monday): void {
    $monday->isDayOfWeek(8);
}, InvalidValueException::class);


isWeekend:
Assert::false((new DateTime('2000-01-03'))->isWeekend());
Assert::true($dateTime->isWeekend());

Assert::false($monday->isWeekend());
Assert::false($friday->isWeekend());
Assert::true($saturday->isWeekend());
Assert::true($sunday->isWeekend());


isMonth:
Assert::true($dateTime->isMonth(Month::january()));
Assert::false($dateTime->isMonth(Month::february()));
Assert::false($dateTime->isMonth(Month::march()));
Assert::false($dateTime->isMonth(Month::april()));
Assert::false($dateTime->isMonth(Month::may()));
Assert::false($dateTime->isMonth(Month::june()));
Assert::false($dateTime->isMonth(Month::july()));
Assert::false($dateTime->isMonth(Month::august()));
Assert::false($dateTime->isMonth(Month::september()));
Assert::false($dateTime->isMonth(Month::october()));
Assert::false($dateTime->isMonth(Month::november()));
Assert::false($dateTime->isMonth(Month::december()));

Assert::true($monday->isMonth(11));
Assert::true($monday->isMonth(Month::november()));
Assert::false($monday->isMonth(12));
Assert::false($monday->isMonth(Month::december()));
Assert::exception(static function () use ($monday): void {
    $monday->isMonth(13);
}, InvalidValueException::class);


getDayOfWeekEnum:
Assert::equal($monday->getDayOfWeekEnum(), DayOfWeek::monday());
Assert::equal($friday->getDayOfWeekEnum(), DayOfWeek::friday());
Assert::equal($saturday->getDayOfWeekEnum(), DayOfWeek::saturday());
Assert::equal($sunday->getDayOfWeekEnum(), DayOfWeek::sunday());


getMonthEnum:
Assert::equal($monday->getMonthEnum(), Month::november());


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
$upperTime = new DateTime('2000-01-02 06:12:12.200000');
$criticalTime = new DateTime('2000-01-02 23:59:59.999999');

Assert::equal((new DateTime('2018-07-17 14:55:00.000000'))->roundUpTo($minute, [20]), new DateTime('2018-07-17 15:20:00.000000'));


roundTo:
Assert::equal($dateTime->roundTo($hour, $hours), new DateTime('2000-01-02 00:00:00.000000'));
Assert::equal($dateTime->roundTo($minute, $minutes), new DateTime('2000-01-02 03:00:00.000000'));
Assert::equal($dateTime->roundTo($second, $seconds), new DateTime('2000-01-02 03:04:00.000000'));
Assert::equal($dateTime->roundTo($milisecond, $miliseconds), new DateTime('2000-01-02 03:04:05.000000'));
Assert::equal($dateTime->roundTo($microsecond, $microseconds), new DateTime('2000-01-02 03:04:05.000000'));

Assert::equal($upperTime->roundTo($hour, $hours), new DateTime('2000-01-02 08:00:00.000000'));
Assert::equal($upperTime->roundTo($minute, $minutes), new DateTime('2000-01-02 06:15:00.000000'));
Assert::equal($upperTime->roundTo($second, $seconds), new DateTime('2000-01-02 06:12:15.000000'));
Assert::equal($upperTime->roundTo($milisecond, $miliseconds), new DateTime('2000-01-02 06:12:12.250000'));
Assert::equal($upperTime->roundTo($microsecond, $microseconds), new DateTime('2000-01-02 06:12:12.250000'));

Assert::equal($criticalTime->roundTo($hour, $hours), new DateTime('2000-01-03 00:00:00.000000'));
Assert::equal($criticalTime->roundTo($minute, $minutes), new DateTime('2000-01-03 00:00:00.000000'));
Assert::equal($criticalTime->roundTo($second, $seconds), new DateTime('2000-01-03 00:00:00.000000'));
Assert::equal($criticalTime->roundTo($milisecond, $miliseconds), new DateTime('2000-01-03 00:00:00.000000'));
Assert::equal($criticalTime->roundTo($microsecond, $microseconds), new DateTime('2000-01-03 00:00:00.000000'));


roundUpTo:
Assert::equal($dateTime->roundUpTo($hour, $hours), new DateTime('2000-01-02 08:00:00.000000'));
Assert::equal($dateTime->roundUpTo($minute, $minutes), new DateTime('2000-01-02 03:15:00.000000'));
Assert::equal($dateTime->roundUpTo($second, $seconds), new DateTime('2000-01-02 03:04:15.000000'));
Assert::equal($dateTime->roundUpTo($milisecond, $miliseconds), new DateTime('2000-01-02 03:04:05.250000'));
Assert::equal($dateTime->roundUpTo($microsecond, $microseconds), new DateTime('2000-01-02 03:04:05.250000'));

Assert::equal($upperTime->roundUpTo($hour, $hours), new DateTime('2000-01-02 08:00:00.000000'));
Assert::equal($upperTime->roundUpTo($minute, $minutes), new DateTime('2000-01-02 06:15:00.000000'));
Assert::equal($upperTime->roundUpTo($second, $seconds), new DateTime('2000-01-02 06:12:15.000000'));
Assert::equal($upperTime->roundUpTo($milisecond, $miliseconds), new DateTime('2000-01-02 06:12:12.250000'));
Assert::equal($upperTime->roundUpTo($microsecond, $microseconds), new DateTime('2000-01-02 06:12:12.250000'));

Assert::equal($criticalTime->roundUpTo($hour, $hours), new DateTime('2000-01-03 00:00:00.000000'));
Assert::equal($criticalTime->roundUpTo($minute, $minutes), new DateTime('2000-01-03 00:00:00.000000'));
Assert::equal($criticalTime->roundUpTo($second, $seconds), new DateTime('2000-01-03 00:00:00.000000'));
Assert::equal($criticalTime->roundUpTo($milisecond, $miliseconds), new DateTime('2000-01-03 00:00:00.000000'));
Assert::equal($criticalTime->roundUpTo($microsecond, $microseconds), new DateTime('2000-01-03 00:00:00.000000'));


roundDownTo:
Assert::equal($dateTime->roundDownTo($hour, $hours), new DateTime('2000-01-02 00:00:00.000000'));
Assert::equal($dateTime->roundDownTo($minute, $minutes), new DateTime('2000-01-02 03:00:00.000000'));
Assert::equal($dateTime->roundDownTo($second, $seconds), new DateTime('2000-01-02 03:04:00.000000'));
Assert::equal($dateTime->roundDownTo($milisecond, $miliseconds), new DateTime('2000-01-02 03:04:05.000000'));
Assert::equal($dateTime->roundDownTo($microsecond, $microseconds), new DateTime('2000-01-02 03:04:05.000000'));

Assert::equal($upperTime->roundDownTo($hour, $hours), new DateTime('2000-01-02 00:00:00.000000'));
Assert::equal($upperTime->roundDownTo($minute, $minutes), new DateTime('2000-01-02 06:00:00.000000'));
Assert::equal($upperTime->roundDownTo($second, $seconds), new DateTime('2000-01-02 06:12:00.000000'));
Assert::equal($upperTime->roundDownTo($milisecond, $miliseconds), new DateTime('2000-01-02 06:12:12.000000'));
Assert::equal($upperTime->roundDownTo($microsecond, $microseconds), new DateTime('2000-01-02 06:12:12.000000'));

Assert::equal($criticalTime->roundDownTo($hour, $hours), new DateTime('2000-01-02 16:00:00.000000'));
Assert::equal($criticalTime->roundDownTo($minute, $minutes), new DateTime('2000-01-02 23:45:00.000000'));
Assert::equal($criticalTime->roundDownTo($second, $seconds), new DateTime('2000-01-02 23:59:45.000000'));
Assert::equal($criticalTime->roundDownTo($milisecond, $miliseconds), new DateTime('2000-01-02 23:59:59.750000'));
Assert::equal($criticalTime->roundDownTo($microsecond, $microseconds), new DateTime('2000-01-02 23:59:59.750000'));
