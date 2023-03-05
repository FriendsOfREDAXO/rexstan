<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\Arr;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = [1, 2, 3, 4];
$arr2 = [[1, 2], [3, 4]];
$empty = [];

$f = static function (int $v): int {
    return $v % 2;
};


flatMap:
Assert::same(Arr::flatMap($arr2, static function (array $v): array {
    return array_reverse($v);
}), [2, 1, 4, 3]);
Assert::same(Arr::flatMap($empty, $f), []);


flatten:
Assert::same(Arr::flatten($arr2), [1, 2, 3, 4]);
Assert::same(Arr::flatten($empty), []);


groupBy:
Assert::same(Arr::groupBy($array, $f), [1 => [1, 2 => 3], 0 => [1 => 2, 3 => 4]]);
Assert::same(Arr::groupBy($empty, $f), []);


map:
Assert::same(Arr::map($array, $f), [1, 0, 1, 0]);
Assert::same(Arr::map($empty, $f), []);


mapPairs:
Assert::same(Arr::mapPairs($array, static function (int $k, int $v): int {
    return $k + $v;
}), [1, 3, 5, 7]);
Assert::same(Arr::mapPairs($empty, $f), []);


remap:
Assert::same(Arr::remap($array, static function (int $k, int $v): array {
    return [$v => $k];
}), [1 => 0, 1, 2, 3]);
Assert::same(Arr::remap($empty, $f), []);


flip:
Assert::same(Arr::flip($array), [1 => 0, 1, 2, 3]);
Assert::same(Arr::flip($empty), []);


transpose:
Assert::same(Arr::transpose($arr2), [[1, 3], [2, 4]]);
Assert::same(Arr::transpose($empty), []);


column:
Assert::same(Arr::column($arr2, 0), [1, 3]);
Assert::same(Arr::column($arr2, 0, 1), [2 => 1, 4 => 3]);
Assert::same(Arr::column($empty, 0), []);
