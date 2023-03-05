<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\Arr;
use Dogma\Order;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = [1, 2, 3, 4];
$empty = [];

$f = static function (int $a, int $b): int {
    return $a < $b ? 1 : ($a > $b ? -1 : 0);
};


reverse:
Assert::same(Arr::reverse($empty), []);
Assert::same(Arr::reverse($array), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);


shuffle:
Assert::same(Arr::shuffle($empty), []);
$sh = Arr::shuffle($array);
Assert::same(Arr::values(Arr::sort($sh)), $array);


sort:
Assert::same(Arr::sort($empty), []);
Assert::same(Arr::sort($array), [1, 2, 3, 4]);
Assert::same(Arr::sort($array, Order::ASCENDING), [1, 2, 3, 4]);
Assert::same(Arr::sort($array, Order::DESCENDING), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);


sortKeys:
Assert::same(Arr::sortKeys($empty), []);
Assert::same(Arr::sortKeys($array), [1, 2, 3, 4]);
Assert::same(Arr::sortKeys($array, Order::ASCENDING), [1, 2, 3, 4]);
Assert::same(Arr::sortKeys($array, Order::DESCENDING), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);


sortWith:
Assert::same(Arr::sortWith($empty, $f), []);
Assert::same(Arr::sortWith($array, $f), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);
Assert::same(Arr::sortWith($array, $f, Order::ASCENDING), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);
Assert::same(Arr::sortWith($array, $f, Order::DESCENDING), [1, 2, 3, 4]);


sortKeysWith:
Assert::same(Arr::sortKeysWith($empty, $f), []);
Assert::same(Arr::sortKeysWith($array, $f), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);
Assert::same(Arr::sortKeysWith($array, $f, Order::ASCENDING), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);
Assert::same(Arr::sortKeysWith($array, $f, Order::DESCENDING), [1, 2, 3, 4]);


distinct:
Assert::same(Arr::distinct($empty), []);
Assert::same(Arr::distinct($array), [1, 2, 3, 4]);
Assert::same(Arr::distinct([1, 1, 2, 2]), [1, 2 => 2]);
