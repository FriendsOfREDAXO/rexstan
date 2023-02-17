<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\ImmutableArray;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3, 4, 5, 6]);
$diff1 = new ImmutableArray([1 => 2, 3]);
$diff2 = new ImmutableArray([3 => 4, 5]);
$int1 = new ImmutableArray([1 => 2, 3, 4, 5]);
$int2 = new ImmutableArray([2 => 3, 4, 5, 6]);

$f = static function (int $a, int $b): int {
    return $a > $b ? 1 : ($a < $b ? -1 : 0);
};


diff:
Assert::same($array->diff($diff1)->toArray(), [1, 3 => 4, 5, 6]);
Assert::same($array->diff($diff1, $diff2)->toArray(), [1, 5 => 6]);


diffWith:
Assert::same($array->diffWith($f, $diff1)->toArray(), [1, 3 => 4, 5, 6]);
Assert::same($array->diffWith($f, $diff1, $diff2)->toArray(), [1, 5 => 6]);


diffKeys:
Assert::same($array->diffKeys($diff1)->toArray(), [1, 3 => 4, 5, 6]);
Assert::same($array->diffKeys($diff1, $diff2)->toArray(), [1, 5 => 6]);


diffKeysWith:
Assert::same($array->diffKeysWith($f, $diff1)->toArray(), [1, 3 => 4, 5, 6]);
Assert::same($array->diffKeysWith($f, $diff1, $diff2)->toArray(), [1, 5 => 6]);


diffPairs:
Assert::same($array->diffPairs($diff1)->toArray(), [1, 3 => 4, 5, 6]);
Assert::same($array->diffPairs($diff1, $diff2)->toArray(), [1, 5 => 6]);


diffPairsWith:
Assert::same($array->diffPairsWith($f, $f, $diff1)->toArray(), [1, 3 => 4, 5, 6]);
Assert::same($array->diffPairsWith($f, $f, $diff1, $diff2)->toArray(), [1, 5 => 6]);
Assert::same($array->diffPairsWith(null, $f, $diff1)->toArray(), [1, 3 => 4, 5, 6]);
Assert::same($array->diffPairsWith(null, $f, $diff1, $diff2)->toArray(), [1, 5 => 6]);
Assert::same($array->diffPairsWith($f, null, $diff1)->toArray(), [1, 3 => 4, 5, 6]);
Assert::same($array->diffPairsWith($f, null, $diff1, $diff2)->toArray(), [1, 5 => 6]);
Assert::same($array->diffPairsWith(null, null, $diff1)->toArray(), [1, 3 => 4, 5, 6]);
Assert::same($array->diffPairsWith(null, null, $diff1, $diff2)->toArray(), [1, 5 => 6]);


intersect:
Assert::same($array->intersect($int1)->toArray(), [1 => 2, 3, 4, 5]);
Assert::same($array->intersect($int1, $int2)->toArray(), [2 => 3, 4, 5]);


intersectWith:
Assert::same($array->intersectWith($f, $int1)->toArray(), [1 => 2, 3, 4, 5]);
Assert::same($array->intersectWith($f, $int1, $int2)->toArray(), [2 => 3, 4, 5]);


intersectKeys:
Assert::same($array->intersectKeys($int1)->toArray(), [1 => 2, 3, 4, 5]);
Assert::same($array->intersectKeys($int1, $int2)->toArray(), [2 => 3, 4, 5]);


intersectKeysWith:
Assert::same($array->intersectKeysWith($f, $int1)->toArray(), [1 => 2, 3, 4, 5]);
Assert::same($array->intersectKeysWith($f, $int1, $int2)->toArray(), [2 => 3, 4, 5]);


intersectPairs:
Assert::same($array->intersectPairs($int1)->toArray(), [1 => 2, 3, 4, 5]);
Assert::same($array->intersectPairs($int1, $int2)->toArray(), [2 => 3, 4, 5]);


intersectPairsWith:
Assert::same($array->intersectPairsWith($f, $f, $int1)->toArray(), [1 => 2, 3, 4, 5]);
Assert::same($array->intersectPairsWith($f, $f, $int1, $int2)->toArray(), [2 => 3, 4, 5]);
Assert::same($array->intersectPairsWith(null, $f, $int1)->toArray(), [1 => 2, 3, 4, 5]);
Assert::same($array->intersectPairsWith(null, $f, $int1, $int2)->toArray(), [2 => 3, 4, 5]);
Assert::same($array->intersectPairsWith($f, null, $int1)->toArray(), [1 => 2, 3, 4, 5]);
Assert::same($array->intersectPairsWith($f, null, $int1, $int2)->toArray(), [2 => 3, 4, 5]);
Assert::same($array->intersectPairsWith(null, null, $int1)->toArray(), [1 => 2, 3, 4, 5]);
Assert::same($array->intersectPairsWith(null, null, $int1, $int2)->toArray(), [2 => 3, 4, 5]);
