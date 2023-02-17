<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\Arr;
use Dogma\ImmutableArray;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = [1, 2, 3, 4];
$empty = [];

$f = static function (int $v): int {
    return $v % 2;
};


collect:
Assert::same(Arr::collect($array, $f), [1, 2 => 1]);
Assert::same(Arr::collect($empty, $f), []);


filter:
Assert::same(Arr::filter($array, $f), [1, 2 => 3]);
Assert::same(Arr::filter($empty, $f), []);


filterKeys:
Assert::same(Arr::filterKeys($array, $f), [1 => 2, 3 => 4]);
Assert::same(Arr::filterKeys($empty, $f), []);


filterNot:
Assert::same(Arr::filterNot($array, $f), [1 => 2, 3 => 4]);
Assert::same(Arr::filterNot($empty, $f), []);


partition:
/** @var ImmutableArray $a */
/** @var ImmutableArray $b */
[$a, $b] = Arr::partition($array, $f);
Assert::same($a, [1, 2 => 3]);
Assert::same($b, [1 => 2, 3 => 4]);
[$a, $b] = Arr::partition($empty, $f);
Assert::same($a, []);
Assert::same($b, []);
