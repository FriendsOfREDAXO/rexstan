<?php declare(strict_types = 1);

namespace Dogma\Tests\Time;

use DateTime as PhpDateTime;
use DateTimeImmutable;
use DateTimeZone;
use Dogma\InvalidValueException;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\DayOfWeek;
use Dogma\Time\Interval\DateTimeInterval;
use Dogma\Time\InvalidDateTimeException;
use Dogma\Time\Month;

require_once __DIR__ . '/../bootstrap.php';

$dateString = '2000-01-02';
$date = new Date($dateString);
$dateTime = new PhpDateTime($dateString);
$dateTimeImmutable = new DateTimeImmutable($dateString);
$timestamp = 946782245;
$utcTimeZone = new DateTimeZone('UTC');


__construct:
Assert::throws(static function (): void {
    new Date('asdf');
}, InvalidDateTimeException::class);
Assert::type(new Date(), Date::class);
Assert::same((new Date('today'))->format(), date(Date::DEFAULT_FORMAT));


createFromDateTimeInterface:
Assert::type(Date::createFromDateTimeInterface($dateTime), Date::class);
Assert::type(Date::createFromDateTimeInterface($dateTimeImmutable), Date::class);
Assert::type(Date::createFromDateTimeInterface(new DateTime('2000-01-02')), Date::class);
Assert::same(Date::createFromDateTimeInterface($dateTime)->format(), $dateString);
Assert::same(Date::createFromDateTimeInterface($dateTimeImmutable)->format(), $dateString);
Assert::same(Date::createFromDateTimeInterface(new DateTime('2000-01-02'))->format(), $dateString);


createFromComponents:
Assert::type(Date::createFromComponents(2001, 2, 3), Date::class);
Assert::same(Date::createFromComponents(2001, 2, 3)->format('Y-m-d'), '2001-02-03');


createFromFormat:
Assert::type(Date::createFromFormat(Date::DEFAULT_FORMAT, $dateString), Date::class);
Assert::same(Date::createFromFormat(Date::DEFAULT_FORMAT, $dateString)->format(), $dateString);
Assert::exception(static function (): void {
    Date::createFromFormat('Y-m-d', '12:00:00');
}, InvalidDateTimeException::class);


format:
Assert::same($date->format('j.n.Y'), date('j.n.Y', $timestamp));


toDateTime:
Assert::equal($date->toDateTime(), new DateTime('2000-01-02 00:00:00'));


toDateTimeInterval:
Assert::equal($date->toDateTimeInterval(), new DateTimeInterval(new DateTime('2000-01-02 00:00:00'), new DateTime('2000-01-03 00:00:00')));


getJulianDay:
Assert::same($date->getJulianDay(), 2451546);
Assert::same((new Date(Date::MIN))->getJulianDay(), Date::MIN_DAY_NUMBER);
Assert::same((new Date(Date::MAX))->getJulianDay(), Date::MAX_DAY_NUMBER);


getStart:
Assert::equal($date->getStart($utcTimeZone), new DateTime('2000-01-02 00:00:00.000000', $utcTimeZone));


getEnd:
Assert::equal($date->getEnd($utcTimeZone), new DateTime('2000-01-02 23:59:59.999999', $utcTimeZone));

$today = new Date('today 12:00');
$today2 = new Date('today 13:00');
$yesterday = new Date('yesterday');
$tomorrow = new Date('tomorrow');


increment:
Assert::same($today->addDay()->format(), $tomorrow->format());


decrement:
Assert::same($today->subtractDay()->format(), $yesterday->format());


diff:
Assert::same($today->diff($today)->format('%R %y %m %d %h %i %s'), '+ 0 0 0 0 0 0');
Assert::same($today->diff($today2)->format('%R %y %m %d %h %i %s'), '+ 0 0 0 0 0 0');
Assert::same($today->diff($tomorrow)->format('%R %y %m %d %h %i %s'), '+ 0 0 1 0 0 0');
Assert::same($today->diff($yesterday)->format('%R %y %m %d %h %i %s'), '- 0 0 1 0 0 0');
Assert::same($today->diff($tomorrow, true)->format('%R %y %m %d %h %i %s'), '+ 0 0 1 0 0 0');
Assert::same($today->diff($yesterday, true)->format('%R %y %m %d %h %i %s'), '+ 0 0 1 0 0 0');


compare:
Assert::same($today->compare($yesterday), 1);
Assert::same($today->compare($today), 0);
Assert::same($today->compare($tomorrow), -1);


equals:
Assert::false($today->equals($yesterday));
Assert::false($today->equals($tomorrow));
Assert::true($today->equals($today));
Assert::true($today->equals($today2));


isBefore:
Assert::false($today->isBefore($yesterday));
Assert::false($today->isBefore($today));
Assert::true($today->isBefore($tomorrow));


isAfter:
Assert::true($today->isAfter($yesterday));
Assert::false($today->isAfter($today));
Assert::false($today->isAfter($tomorrow));


isSameOrBefore:
Assert::false($today->isSameOrBefore($yesterday));
Assert::true($today->isSameOrBefore($today));
Assert::true($today->isSameOrBefore($tomorrow));


isSameOrAfter:
Assert::true($today->isSameOrAfter($yesterday));
Assert::true($today->isSameOrAfter($today));
Assert::false($today->isSameOrAfter($tomorrow));


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

$monday = new Date('2016-11-07');
$friday = new Date('2016-11-04');
$saturday = new Date('2016-11-05');
$sunday = new Date('2016-11-06');


getDayOfWeekEnum:
Assert::equal($monday->getDayOfWeekEnum(), DayOfWeek::monday());
Assert::equal($friday->getDayOfWeekEnum(), DayOfWeek::friday());
Assert::equal($saturday->getDayOfWeekEnum(), DayOfWeek::saturday());
Assert::equal($sunday->getDayOfWeekEnum(), DayOfWeek::sunday());


isDayOfWeek:
Assert::true($monday->isDayOfWeek(1));
Assert::true($monday->isDayOfWeek(DayOfWeek::monday()));
Assert::false($monday->isDayOfWeek(7));
Assert::false($monday->isDayOfWeek(DayOfWeek::sunday()));
Assert::exception(static function () use ($monday): void {
    $monday->isDayOfWeek(8);
}, InvalidValueException::class);


isWeekend:
Assert::false($monday->isWeekend());
Assert::false($friday->isWeekend());
Assert::true($saturday->isWeekend());
Assert::true($sunday->isWeekend());


getMonthEnum:
Assert::equal($monday->getMonthEnum(), Month::november());


isMonth:
Assert::true($monday->isMonth(11));
Assert::true($monday->isMonth(Month::november()));
Assert::false($monday->isMonth(12));
Assert::false($monday->isMonth(Month::december()));
Assert::exception(static function () use ($monday): void {
    $monday->isMonth(13);
}, InvalidValueException::class);
