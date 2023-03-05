<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\Arr;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = [1, 2, 3, 4, 5, 6];
$diff1 = [1 => 2, 3];
$diff2 = [3 => 4, 5];
$int1 = [1 => 2, 3, 4, 5];
$int2 = [2 => 3, 4, 5, 6];

$f = static function (int $a, int $b): int {
    return $a > $b ? 1 : ($a < $b ? -1 : 0);
};


diff:
Assert::same(Arr::diff($array, $diff1), [1, 3 => 4, 5, 6]);
Assert::same(Arr::diff($array, $diff1, $diff2), [1, 5 => 6]);


diffWith:
Assert::same(Arr::diffWith($array, $f, $diff1), [1, 3 => 4, 5, 6]);
Assert::same(Arr::diffWith($array, $f, $diff1, $diff2), [1, 5 => 6]);


diffKeys:
Assert::same(Arr::diffKeys($array, $diff1), [1, 3 => 4, 5, 6]);
Assert::same(Arr::diffKeys($array, $diff1, $diff2), [1, 5 => 6]);


diffKeysWith:
Assert::same(Arr::diffKeysWith($array, $f, $diff1), [1, 3 => 4, 5, 6]);
Assert::same(Arr::diffKeysWith($array, $f, $diff1, $diff2), [1, 5 => 6]);


diffPairs:
Assert::same(Arr::diffPairs($array, $diff1), [1, 3 => 4, 5, 6]);
Assert::same(Arr::diffPairs($array, $diff1, $diff2), [1, 5 => 6]);


diffPairsWith:
Assert::same(Arr::diffPairsWith($array, $f, $f, $diff1), [1, 3 => 4, 5, 6]);
Assert::same(Arr::diffPairsWith($array, $f, $f, $diff1, $diff2), [1, 5 => 6]);
Assert::same(Arr::diffPairsWith($array, null, $f, $diff1), [1, 3 => 4, 5, 6]);
Assert::same(Arr::diffPairsWith($array, null, $f, $diff1, $diff2), [1, 5 => 6]);
Assert::same(Arr::diffPairsWith($array, $f, null, $diff1), [1, 3 => 4, 5, 6]);
Assert::same(Arr::diffPairsWith($array, $f, null, $diff1, $diff2), [1, 5 => 6]);
Assert::same(Arr::diffPairsWith($array, null, null, $diff1), [1, 3 => 4, 5, 6]);
Assert::same(Arr::diffPairsWith($array, null, null, $diff1, $diff2), [1, 5 => 6]);


intersect:
Assert::same(Arr::intersect($array, $int1), [1 => 2, 3, 4, 5]);
Assert::same(Arr::intersect($array, $int1, $int2), [2 => 3, 4, 5]);


intersectWith:
Assert::same(Arr::intersectWith($array, $f, $int1), [1 => 2, 3, 4, 5]);
Assert::same(Arr::intersectWith($array, $f, $int1, $int2), [2 => 3, 4, 5]);


intersectKeys:
Assert::same(Arr::intersectKeys($array, $int1), [1 => 2, 3, 4, 5]);
Assert::same(Arr::intersectKeys($array, $int1, $int2), [2 => 3, 4, 5]);


intersectKeysWith:
Assert::same(Arr::intersectKeysWith($array, $f, $int1), [1 => 2, 3, 4, 5]);
Assert::same(Arr::intersectKeysWith($array, $f, $int1, $int2), [2 => 3, 4, 5]);


intersectPairs:
Assert::same(Arr::intersectPairs($array, $int1), [1 => 2, 3, 4, 5]);
Assert::same(Arr::intersectPairs($array, $int1, $int2), [2 => 3, 4, 5]);


intersectPairsWith:
Assert::same(Arr::intersectPairsWith($array, $f, $f, $int1), [1 => 2, 3, 4, 5]);
Assert::same(Arr::intersectPairsWith($array, $f, $f, $int1, $int2), [2 => 3, 4, 5]);
Assert::same(Arr::intersectPairsWith($array, null, $f, $int1), [1 => 2, 3, 4, 5]);
Assert::same(Arr::intersectPairsWith($array, null, $f, $int1, $int2), [2 => 3, 4, 5]);
Assert::same(Arr::intersectPairsWith($array, $f, null, $int1), [1 => 2, 3, 4, 5]);
Assert::same(Arr::intersectPairsWith($array, $f, null, $int1, $int2), [2 => 3, 4, 5]);
Assert::same(Arr::intersectPairsWith($array, null, null, $int1), [1 => 2, 3, 4, 5]);
Assert::same(Arr::intersectPairsWith($array, null, null, $int1, $int2), [2 => 3, 4, 5]);
