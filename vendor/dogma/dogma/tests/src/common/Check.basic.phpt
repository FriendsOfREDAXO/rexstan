<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use Dogma\ArrayIterator;
use Dogma\Check;
use Dogma\InvalidTypeException;
use Dogma\InvalidValueException;
use Dogma\Tester\Assert;
use Dogma\Tuple;
use Dogma\Type;
use Dogma\ValueOutOfRangeException;
use Exception;
use IteratorAggregate;
use SplFixedArray;
use stdClass;
use Traversable;

require_once __DIR__ . '/../bootstrap.php';


negativeZero:
$negativeZero = -(0.0);
Check::float($negativeZero);
Assert::same((string) $negativeZero, '0');

$negativeZero = -0.0;
Check::string($negativeZero);
Assert::same($negativeZero, '0');


nullables:
$null = null;

Check::nullableType($null, Type::BOOL);
Check::nullableBool($null);
Check::nullableInt($null);
Check::nullableFloat($null);
Check::nullableString($null);
Check::nullableObject($null);

$array = ['a' => 1, 'b' => 2, 'c' => 3];
$vector = [1, 2, 3];
$mixed = [1, 2, 'a', 'b'];


type:
Check::type($vector, 'array<int>');
Assert::exception(static function () use ($mixed): void {
    Check::itemsOfType($mixed, Type::INT);
}, InvalidTypeException::class);


itemsOfType:
Check::itemsOfType($array, Type::INT);
Assert::exception(static function () use ($mixed): void {
    Check::itemsOfType($mixed, Type::INT);
}, InvalidTypeException::class);


itemsOfTypes:
Check::itemsOfTypes($mixed, [Type::INT, Type::STRING]);
Assert::exception(static function () use ($mixed): void {
    Check::itemsOfTypes($mixed, [Type::INT, Type::FLOAT]);
}, InvalidTypeException::class);


traversable:
Check::traversable($array);
Check::traversable($vector);
Check::traversable(new SplFixedArray());
Check::traversable(new stdClass());
Assert::exception(static function (): void {
    Check::traversable(new Exception());
}, InvalidTypeException::class);


_array:
Check::array($array);
Assert::exception(static function () use ($null): void {
    Check::array($null);
}, InvalidTypeException::class);


plainArray:
Check::plainArray($vector);
Assert::exception(static function () use ($array): void {
    Check::plainArray($array);
}, InvalidTypeException::class);


tuple:
Check::tuple(new Tuple(123, 'abc'), [Type::INT, Type::STRING]);
Assert::exception(static function (): void {
    Check::tuple(new Tuple(123, 'abc', 789), [Type::INT, Type::STRING]);
}, ValueOutOfRangeException::class);
Assert::exception(static function (): void {
    Check::tuple(new Tuple(123), [Type::INT, Type::STRING]);
}, ValueOutOfRangeException::class);
Assert::exception(static function (): void {
    Check::tuple(new Tuple('abc', 123), [Type::INT, Type::STRING]);
}, InvalidTypeException::class);
Assert::exception(static function () use ($array): void {
    Check::tuple($array, [Type::INT, Type::STRING]);
}, InvalidTypeException::class);


object:
Check::object(new stdClass(), stdClass::class);
Assert::exception(static function () use ($array): void {
    Check::object($array, stdClass::class);
}, InvalidTypeException::class);


className:
Check::className(stdClass::class);
Assert::exception(static function (): void {
    Check::className(Type::STRING);
}, InvalidValueException::class);


typeName:
Check::typeName(stdClass::class);
Check::typeName(Type::STRING);
Assert::exception(static function (): void {
    Check::typeName('asdf');
}, InvalidValueException::class);


ranges:
$small = -100;
$big = 100;

Assert::exception(static function () use ($small): void {
    Check::int($small, 0);
}, ValueOutOfRangeException::class);

Assert::exception(static function () use ($big): void {
    Check::int($big, 0, 10);
}, ValueOutOfRangeException::class);

Assert::exception(static function () use ($small): void {
    Check::float($small, 0.0);
}, ValueOutOfRangeException::class);

Assert::exception(static function () use ($big): void {
    Check::float($big, 0.0, 10.0);
}, ValueOutOfRangeException::class);

$short = 'abc';
$long = 'abcabcabc';

Assert::exception(static function () use ($short): void {
    Check::string($short, 5);
}, ValueOutOfRangeException::class);

Assert::exception(static function () use ($long): void {
    Check::string($long, 5, 6);
}, ValueOutOfRangeException::class);


oneOf:
Check::oneOf($short);
Check::oneOf($short, $null);
Check::oneOf($null, $short);
Assert::exception(static function () use ($short): void {
    Check::oneOf($short, $short);
}, ValueOutOfRangeException::class);

class TestTraversable implements IteratorAggregate
{

    /**
     * @return Traversable<mixed>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator([]);
    }

}

class TestNonTraversable
{

}

isIterable:
Assert::true(Check::isIterable([]));
Assert::true(Check::isIterable(new stdClass()));
Assert::true(Check::isIterable(new TestTraversable()));
Assert::false(Check::isIterable(new TestNonTraversable()));


isPlainArray:
Assert::true(Check::isPlainArray([]));
Assert::true(Check::isPlainArray([1, 2, 3]));
Assert::false(Check::isPlainArray([1 => 1, 2, 3]));
Assert::false(Check::isPlainArray(['a' => 1, 2, 3]));
Assert::false(Check::isPlainArray(['a' => 1, 'b' => 2, 'c' => 3]));
