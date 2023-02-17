<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\ImmutableArray;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3, 4]);
$empty = new ImmutableArray([]);

$f = static function (int $v): int {
    return $v % 2;
};


collect:
Assert::same($array->collect($f)->toArray(), [1, 2 => 1]);
Assert::same($empty->collect($f)->toArray(), []);


filter:
Assert::same($array->filter($f)->toArray(), [1, 2 => 3]);
Assert::same($empty->filter($f)->toArray(), []);


filterKeys:
Assert::same($array->filterKeys($f)->toArray(), [1 => 2, 3 => 4]);
Assert::same($empty->filterKeys($f)->toArray(), []);


filterNot:
Assert::same($array->filterNot($f)->toArray(), [1 => 2, 3 => 4]);
Assert::same($empty->filterNot($f)->toArray(), []);


partition:
/** @var ImmutableArray $a */
/** @var ImmutableArray $b */
[$a, $b] = $array->partition($f);
Assert::same($a->toArray(), [1, 2 => 3]);
Assert::same($b->toArray(), [1 => 2, 3 => 4]);
[$a, $b] = $empty->partition($f);
Assert::same($a->toArray(), []);
Assert::same($b->toArray(), []);
