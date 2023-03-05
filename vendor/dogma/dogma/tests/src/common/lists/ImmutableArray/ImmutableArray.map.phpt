<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\ImmutableArray;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3, 4]);
$arr2 = new ImmutableArray([[1, 2], [3, 4]]);
$empty = new ImmutableArray([]);

$f = static function (int $v): int {
    return $v % 2;
};


flatMap:
Assert::same($arr2->flatMap(static function (array $v): array {
    return array_reverse($v);
})->toArray(), [2, 1, 4, 3]);
Assert::same($empty->flatMap($f)->toArray(), []);


flatten:
Assert::same($arr2->flatten()->toArray(), [1, 2, 3, 4]);
Assert::same($empty->flatten()->toArray(), []);


groupBy:
Assert::same($array->groupBy($f)->toArrayRecursive(), [1 => [1, 2 => 3], 0 => [1 => 2, 3 => 4]]);
Assert::same($empty->groupBy($f)->toArrayRecursive(), []);


map:
Assert::same($array->map($f)->toArray(), [1, 0, 1, 0]);
Assert::same($empty->map($f)->toArray(), []);


mapPairs:
Assert::same($array->mapPairs(static function (int $k, int $v): int {
    return $k + $v;
})->toArray(), [1, 3, 5, 7]);
Assert::same($empty->mapPairs($f)->toArray(), []);


remap:
Assert::same($array->remap(static function (int $k, int $v): array {
    return [$v => $k];
})->toArray(), [1 => 0, 1, 2, 3]);
Assert::same($empty->remap($f)->toArray(), []);


flip:
Assert::same($array->flip()->toArray(), [1 => 0, 1, 2, 3]);
Assert::same($empty->flip()->toArray(), []);


transpose:
Assert::same($arr2->transpose()->toArrayRecursive(), [[1, 3], [2, 4]]);
Assert::same($empty->transpose()->toArrayRecursive(), []);


column:
Assert::same($arr2->column(0)->toArray(), [1, 3]);
Assert::same($arr2->column(0, 1)->toArray(), [2 => 1, 4 => 3]);
Assert::same($empty->column(0)->toArray(), []);
