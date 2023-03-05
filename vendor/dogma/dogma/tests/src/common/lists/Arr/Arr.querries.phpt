<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\Arr;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = [1, 2, 3, 2, 4];
$empty = [];


isEmpty:
Assert::true(Arr::isEmpty($empty));
Assert::false(Arr::isEmpty($array));


isNotEmpty:
Assert::true(Arr::isNotEmpty($array));
Assert::false(Arr::isNotEmpty($empty));


contains:
Assert::true(Arr::contains($array, 2));
Assert::false(Arr::contains($array, 5));


containsAny:
Assert::true(Arr::containsAny($array, [2, 7]));
Assert::false(Arr::containsAny($array, [6, 7]));


containsAll:
Assert::true(Arr::containsAll($array, [1, 2]));
Assert::false(Arr::containsAll($array, [2, 7]));


indexOf:
Assert::null(Arr::indexOf($array, 5));
Assert::same(Arr::indexOf($array, 2), 1);
Assert::same(Arr::indexOf($array, 2, 2), 3);


indexesOf:
Assert::same(Arr::indexesOf($array, 5), []);
Assert::same(Arr::indexesOf($array, 2), [1, 3]);


lastIndexOf:
Assert::null(Arr::lastIndexOf($array, 5));
Assert::same(Arr::lastIndexOf($array, 2), 3);
Assert::same(Arr::lastIndexOf($array, 2, 2), 1);


indexWhere:
Assert::null(Arr::indexWhere($array, static function (): bool {
    return false;
}));
Assert::same(Arr::indexWhere($array, static function (int $v): bool {
    return $v === 2;
}), 1);
Assert::same(Arr::indexWhere($array, static function (int $v): bool {
    return $v === 2;
}, 2), 3);


lastIndexWhere:
Assert::null(Arr::lastIndexWhere($array, static function (): bool {
    return false;
}));
Assert::same(Arr::lastIndexWhere($array, static function (int $v): bool {
    return $v === 2;
}), 3);
Assert::same(Arr::lastIndexWhere($array, static function (int $v): bool {
    return $v === 2;
}, 2), 1);


containsKey:
Assert::false(Arr::containsKey($array, 5));
Assert::true(Arr::containsKey($array, 2));


containsAnyKey:
Assert::false(Arr::containsAnyKey($array, [5, 6]));
Assert::true(Arr::containsAnyKey($array, [5, 4]));


containsAllKeys:
Assert::false(Arr::containsAllKeys($array, [2, 7]));
Assert::true(Arr::containsAllKeys($array, [0, 1]));


exists:
Assert::false(Arr::exists($array, static function (int $v): bool {
    return $v > 5;
}));
Assert::true(Arr::exists($array, static function (int $v): bool {
    return $v > 1;
}));


forAll:
Assert::false(Arr::forAll($array, static function (int $v): bool {
    return $v > 1;
}));
Assert::true(Arr::forAll($array, static function (int $v): bool {
    return $v < 5;
}));


find:
Assert::null(Arr::find($array, static function (int $v): bool {
    return $v * $v === 25;
}));
Assert::same(Arr::find($array, static function (int $v): bool {
    return $v * $v === 4;
}), 2);


prefixLength:
Assert::same(Arr::prefixLength([2, 2, 2, 1], static function (int $v): bool {
    return $v === 2;
}), 3);


segmentLength:
Assert::same(Arr::segmentLength([2, 2, 2, 1], static function (int $v): bool {
    return $v === 2;
}, 1), 2);
