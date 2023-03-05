<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\ImmutableArray;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3, 2, 4]);
$empty = new ImmutableArray([]);


isEmpty:
Assert::true($empty->isEmpty());
Assert::false($array->isEmpty());


isNotEmpty:
Assert::true($array->isNotEmpty());
Assert::false($empty->isNotEmpty());


contains:
Assert::true($array->contains(2));
Assert::false($array->contains(5));


containsAny:
Assert::true($array->containsAny([2, 7]));
Assert::false($array->containsAny([0, 9]));


containsAll:
Assert::true($array->containsAll([1, 2]));
Assert::false($array->containsAll([1, 7]));


indexOf:
Assert::null($array->indexOf(5));
Assert::same($array->indexOf(2), 1);
Assert::same($array->indexOf(2, 2), 3);


indexesOf:
Assert::same($array->indexesOf(5)->toArray(), []);
Assert::same($array->indexesOf(2)->toArray(), [1, 3]);


lastIndexOf:
Assert::null($array->lastIndexOf(5));
Assert::same($array->lastIndexOf(2), 3);
Assert::same($array->lastIndexOf(2, 2), 1);


indexWhere:
Assert::null($array->indexWhere(static function (): bool {
    return false;
}));
Assert::same($array->indexWhere(static function (int $v): bool {
    return $v === 2;
}), 1);
Assert::same($array->indexWhere(static function (int $v): bool {
    return $v === 2;
}, 2), 3);


lastIndexWhere:
Assert::null($array->lastIndexWhere(static function (): bool {
    return false;
}));
Assert::same($array->lastIndexWhere(static function (int $v): bool {
    return $v === 2;
}), 3);
Assert::same($array->lastIndexWhere(static function (int $v): bool {
    return $v === 2;
}, 2), 1);


containsKey:
Assert::false($array->containsKey(5));
Assert::true($array->containsKey(2));


containsAnyKey:
Assert::false($array->containsAnyKey([6, 7]));
Assert::true($array->containsAnyKey([2, 7]));


containsAllKeys:
Assert::false($array->containsAllKeys([1, 7]));
Assert::true($array->containsAllKeys([1, 2]));


exists:
Assert::false($array->exists(static function (int $v): bool {
    return $v > 5;
}));
Assert::true($array->exists(static function (int $v): bool {
    return $v > 1;
}));


forAll:
Assert::false($array->forAll(static function (int $v): bool {
    return $v > 1;
}));
Assert::true($array->forAll(static function (int $v): bool {
    return $v < 5;
}));


find:
Assert::null($array->find(static function (int $v): bool {
    return $v * $v === 25;
}));
Assert::same($array->find(static function (int $v): bool {
    return $v * $v === 4;
}), 2);


prefixLength:
Assert::same((new ImmutableArray([2, 2, 2, 1]))->prefixLength(static function (int $v): bool {
    return $v === 2;
}), 3);


segmentLength:
Assert::same((new ImmutableArray([2, 2, 2, 1]))->segmentLength(static function (int $v): bool {
    return $v === 2;
}, 1), 2);
