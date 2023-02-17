<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\ImmutableArray;
use Dogma\Order;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3, 4]);
$empty = new ImmutableArray([]);

$f = static function (int $a, int $b): int {
    return $a < $b ? 1 : ($a > $b ? -1 : 0);
};


reverse:
Assert::same($empty->reverse()->toArray(), []);
Assert::same($array->reverse()->toArray(), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);


shuffle:
Assert::same($empty->shuffle()->toArray(), []);
$sh = $array->shuffle();
Assert::same($sh->sort()->values()->toArray(), $array->toArray());


sort:
Assert::same($empty->sort()->toArray(), []);
Assert::same($array->sort()->toArray(), [1, 2, 3, 4]);
Assert::same($array->sort(Order::ASCENDING)->toArray(), [1, 2, 3, 4]);
Assert::same($array->sort(Order::DESCENDING)->toArray(), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);


sortKeys:
Assert::same($empty->sortKeys()->toArray(), []);
Assert::same($array->sortKeys()->toArray(), [1, 2, 3, 4]);
Assert::same($array->sortKeys(Order::ASCENDING)->toArray(), [1, 2, 3, 4]);
Assert::same($array->sortKeys(Order::DESCENDING)->toArray(), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);


sortWith:
Assert::same($empty->sortWith($f)->toArray(), []);
Assert::same($array->sortWith($f)->toArray(), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);
Assert::same($array->sortWith($f, Order::ASCENDING)->toArray(), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);
Assert::same($array->sortWith($f, Order::DESCENDING)->toArray(), [1, 2, 3, 4]);


sortKeysWith:
Assert::same($empty->sortKeysWith($f)->toArray(), []);
Assert::same($array->sortKeysWith($f)->toArray(), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);
Assert::same($array->sortKeysWith($f, Order::ASCENDING)->toArray(), [3 => 4, 2 => 3, 1 => 2, 0 => 1]);
Assert::same($array->sortKeysWith($f, Order::DESCENDING)->toArray(), [1, 2, 3, 4]);


distinct:
Assert::same($empty->distinct()->toArray(), []);
Assert::same($array->distinct()->toArray(), [1, 2, 3, 4]);
Assert::same((new ImmutableArray([1, 1, 2, 2]))->distinct()->toArray(), [1, 2 => 2]);
