<?php declare(strict_types = 1);

namespace Dogma\Tests\Time;

use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\DayOfYear;
use Dogma\Time\InvalidDateTimeException;
use Dogma\Time\Month;
use Dogma\ValueOutOfRangeException;

require_once __DIR__ . '/../bootstrap.php';

$dayString = '02-29';
$day = new DayOfYear($dayString);
$number = 60;
$denormalizedNumber = $number + 366;
$denormalizedDay = new DayOfYear($denormalizedNumber);


__construct:
Assert::throws(static function (): void {
    new DayOfYear(-200);
}, ValueOutOfRangeException::class);
Assert::throws(static function (): void {
    new DayOfYear(800);
}, ValueOutOfRangeException::class);
Assert::throws(static function (): void {
    new DayOfYear('asdf');
}, InvalidDateTimeException::class);
// todo: more strict
//Assert::throws(static function (): void {
//    new DayOfYear('00-01');
//}, InvalidDateTimeException::class);
Assert::throws(static function (): void {
    new DayOfYear('01-32');
}, InvalidDateTimeException::class);
// todo: more strict
//Assert::throws(static function (): void {
//    new DayOfYear('02-30');
//}, InvalidDateTimeException::class);


Assert::same((new DayOfYear($dayString))->format(), $dayString);


createFromMonthAndDay:
Assert::throws(static function (): void {
    DayOfYear::createFromMonthAndDay(-1, 1);
}, ValueOutOfRangeException::class);
Assert::throws(static function (): void {
    DayOfYear::createFromMonthAndDay(13, 1);
}, ValueOutOfRangeException::class);
Assert::throws(static function (): void {
    DayOfYear::createFromMonthAndDay(1, 0);
}, ValueOutOfRangeException::class);
Assert::throws(static function (): void {
    DayOfYear::createFromMonthAndDay(1, 32);
}, ValueOutOfRangeException::class);
Assert::type(DayOfYear::createFromMonthAndDay(2, 29), DayOfYear::class);
Assert::same(DayOfYear::createFromMonthAndDay(2, 29)->format(), $dayString);


createFromDate:
$date = new Date('2000-02-29');
Assert::type(DayOfYear::createFromDate($date), DayOfYear::class);
Assert::same(DayOfYear::createFromDate($date)->format(), $dayString);


createFromDateTime:
$dateTime = new DateTime('2000-02-29 00:00:00');
Assert::type(DayOfYear::createFromDateTime($dateTime), DayOfYear::class);
Assert::same(DayOfYear::createFromDateTime($dateTime)->format(), $dayString);


normalize:
Assert::same($day->normalize()->getNumber(), $number);
Assert::same($denormalizedDay->normalize()->getNumber(), $number);


denormalize:
Assert::same($day->denormalize()->getNumber(), $denormalizedNumber);
Assert::same($denormalizedDay->denormalize()->getNumber(), $denormalizedNumber);


modify:
Assert::same($day->modify('+1 day')->getNumber(), $number + 1);
Assert::same($denormalizedDay->modify('+1 day')->getNumber(), $denormalizedNumber + 1);
// todo: overflows


getNumber:
Assert::same($day->getNumber(), $number);
Assert::same($denormalizedDay->getNumber(), $denormalizedNumber);


getMonth:
Assert::same($day->getMonth(), 2);
Assert::same($denormalizedDay->getMonth(), 2);


getMonthEnum:
Assert::equal($day->getMonthEnum(), Month::february());
Assert::equal($denormalizedDay->getMonthEnum(), Month::february());


getDayOfMonth:
Assert::same($day->getDayOfMonth(), 29);
Assert::same($denormalizedDay->getDayOfMonth(), 29);


toDate:
Assert::equal($day->toDate(2000), new Date('2000-02-29'));
Assert::equal($denormalizedDay->toDate(2000), new Date('2001-02-29'));

$before = new DayOfYear('02-28');
$after = new DayOfYear('03-01');


equals:
Assert::false($day->equals($before));
Assert::true($day->equals(new DayOfYear($dayString)));
Assert::true($day->equals(new DayOfYear($number)));


compare:
Assert::same($day->compare($before), 1);
Assert::same($day->compare($day), 0);
Assert::same($day->compare($after), -1);


isBefore:
Assert::false($day->isBefore($before));
Assert::false($day->isBefore($day));
Assert::true($day->isBefore($after));


isAfter:
Assert::true($day->isAfter($before));
Assert::false($day->isAfter($day));
Assert::false($day->isAfter($after));


isSameOrBefore:
Assert::false($day->isSameOrBefore($before));
Assert::true($day->isSameOrBefore($day));
Assert::true($day->isSameOrBefore($after));


isSameOrAfter:
Assert::true($day->isSameOrAfter($before));
Assert::true($day->isSameOrAfter($day));
Assert::false($day->isSameOrAfter($after));


isBetween:
Assert::false($day->isBetween(new DayOfYear('12-01'), new DayOfYear('01-31')));
Assert::true($day->isBetween($before, $after));
Assert::true($day->isBetween(new DayOfYear('12-01'), $after));
