<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\Arr;
use Dogma\ImmutableArray;
use Dogma\ReverseArrayIterator;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = [1, 2, 3];

toArray:
Assert::same(Arr::toArray(new ImmutableArray([1, 2, 3])), [1, 2, 3]);


combine:
Assert::same(Arr::combine([1, 2, 3], [4, 5, 6]), [1 => 4, 2 => 5, 3 => 6]);


range:
Assert::same(Arr::range(3, 5), [3, 4, 5]);
Assert::same(Arr::range(5, 3), [5, 4, 3]);
Assert::same(Arr::range(3, 7, 2), [3, 5, 7]);
Assert::same(Arr::range(3, 6, 2), [3, 5]);


backwards:
Assert::type(Arr::backwards($array), ReverseArrayIterator::class);


keys:
Assert::same(Arr::keys($array), [0, 1, 2]);


getValues:
Assert::same(Arr::values($array), [1, 2, 3]);


randomKey:
Assert::contains(Arr::keys($array), Arr::randomKey($array));


randomValue:
Assert::contains(Arr::values($array), Arr::randomValue($array));


doForEach:
$x = 0;
Arr::doForEach($array, static function (int $v) use (&$x): void {
    $x += $v;
});
Assert::same($x, 6);
