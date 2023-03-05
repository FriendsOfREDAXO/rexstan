<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\Arr;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = [1, 2, 3, 2];
$empty = [];


count:
Assert::same(Arr::count($array), 4);
Assert::same(Arr::count($array, static function (int $v): bool {
    return $v > 1;
}), 3);
Assert::same(Arr::count($empty), 0);


size:
Assert::same(Arr::size($array), 4);
Assert::same(Arr::size($empty), 0);


countValues:
Assert::same(Arr::countValues($array), [1 => 1, 2 => 2, 3 => 1]);
Assert::same(Arr::countValues($empty), []);


max:
Assert::same(Arr::max($array), 3);
Assert::null(Arr::max($empty));


min:
Assert::same(Arr::min($array), 1);
Assert::null(Arr::min($empty));


maxBy:
Assert::same(Arr::maxBy($array, static function ($v): float {
    return 1 / $v;
}), 1);


minBy:
Assert::same(Arr::minBy($array, static function (int $v): float {
    return 1 / $v;
}), 3);


product:
Assert::same(Arr::product($array), 12);
Assert::same(Arr::product($empty), 1);


sum:
Assert::same(Arr::sum($array), 8);
Assert::same(Arr::sum($empty), 0);
