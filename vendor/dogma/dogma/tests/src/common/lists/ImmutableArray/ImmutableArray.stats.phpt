<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\ImmutableArray;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3, 2]);
$empty = new ImmutableArray([]);


count:
Assert::same($array->count(), 4);
Assert::same($array->count(static function (int $v): bool {
    return $v > 1;
}), 3);
Assert::same($empty->count(), 0);


size:
Assert::same($array->size(), 4);
Assert::same($empty->size(), 0);


countValues:
Assert::same($array->countValues()->toArray(), [1 => 1, 2 => 2, 3 => 1]);
Assert::same($empty->countValues()->toArray(), []);


max:
Assert::same($array->max(), 3);
Assert::null($empty->max());


min:
Assert::same($array->min(), 1);
Assert::null($empty->min());


maxBy:
Assert::same($array->maxBy(static function ($v): float {
    return 1 / $v;
}), 1);


minBy:
Assert::same($array->minBy(static function (int $v): float {
    return 1 / $v;
}), 3);


product:
Assert::same($array->product(), 12);
Assert::same($empty->product(), 1);


sum:
Assert::same($array->sum(), 8);
Assert::same($empty->sum(), 0);
