<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use ArrayAccess;
use BadMethodCallException;
use Countable;
use Dogma\ArrayIterator;
use Dogma\ImmutableArray;
use Dogma\ReverseArrayIterator;
use Dogma\Tester\Assert;
use IteratorAggregate;
use const E_WARNING;
use const PHP_VERSION_ID;

require_once __DIR__ . '/../../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3]);


__construct:
toArray:
Assert::same((new ImmutableArray([1, 2, 3]))->toArray(), [1, 2, 3]);


create:
Assert::same(ImmutableArray::create(1, 2, 3)->toArray(), [1, 2, 3]);


from:
convertToArray:
Assert::same(ImmutableArray::from([1, 2, 3])->toArray(), [1, 2, 3]);
Assert::same(ImmutableArray::from(new ImmutableArray([1, 2, 3]))->toArray(), [1, 2, 3]);
Assert::same(ImmutableArray::from(new ArrayIterator([1, 2, 3]))->toArray(), [1, 2, 3]);


combine:
Assert::same(ImmutableArray::combine([1, 2, 3], [4, 5, 6])->toArray(), [1 => 4, 2 => 5, 3 => 6]);


range:
Assert::same(ImmutableArray::range(3, 5)->toArray(), [3, 4, 5]);
Assert::same(ImmutableArray::range(5, 3)->toArray(), [5, 4, 3]);
Assert::same(ImmutableArray::range(3, 7, 2)->toArray(), [3, 5, 7]);
Assert::same(ImmutableArray::range(3, 6, 2)->toArray(), [3, 5]);


Countable:
Assert::type(new ImmutableArray([]), Countable::class);
Assert::same(count(new ImmutableArray([1, 2, 3])), 3);


IteratorAggregate:
Assert::type(new ImmutableArray([]), IteratorAggregate::class);
Assert::type((new ImmutableArray([]))->getIterator(), ArrayIterator::class);


ArrayAccess:
Assert::type(new ImmutableArray([]), ArrayAccess::class);

Assert::true($array->offsetExists(0));
Assert::false($array->offsetExists(3));

Assert::same($array->offsetGet(0), 1);
Assert::error(
    static function () use ($array): void {
        $array->offsetGet(3);
    },
    PHP_VERSION_ID < 80000 ? E_NOTICE : E_WARNING,
    PHP_VERSION_ID < 80000 ? 'Undefined offset: 3' : 'Undefined array key 3'
);

Assert::exception(static function () use ($array): void {
    $array->offsetSet(3, 4);
}, BadMethodCallException::class);

Assert::exception(static function () use ($array): void {
    $array->offsetUnset(0);
}, BadMethodCallException::class);


getReverseIterator:
Assert::type((new ImmutableArray([]))->getReverseIterator(), ReverseArrayIterator::class);


getKeys:
Assert::same($array->keys()->toArray(), [0, 1, 2]);


getValues:
Assert::same($array->values()->toArray(), [1, 2, 3]);


toArrayRecursive:
Assert::same((new ImmutableArray([1, 2, new ImmutableArray([3, 4, 5])]))->toArrayRecursive(), [1, 2, [3, 4, 5]]);


randomKey:
Assert::contains($array->keys()->toArray(), $array->randomKey());


randomValue:
Assert::contains($array->values()->toArray(), $array->randomValue());


doForEach:
$x = 0;
$array->doForEach(static function (int $v) use (&$x): void {
    $x += $v;
});
Assert::same($x, 6);
