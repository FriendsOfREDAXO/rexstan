<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\Arr;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = [1, 2, 3, 4];
$empty = [];

$f = static function (int $a, int $b): int {
    return $a + $b;
};


fold:
Assert::same(Arr::fold($array, $f, 0), 10);
Assert::same(Arr::fold($empty, $f, 0), 0);
Assert::same(Arr::fold($empty, $f), null);


foldLeft:
Assert::same(Arr::foldLeft($array, $f, 0), 10);
Assert::same(Arr::foldLeft($empty, $f, 0), 0);
Assert::same(Arr::foldLeft($empty, $f), null);


foldRight:
Assert::same(Arr::foldRight($array, $f, 0), 10);
Assert::same(Arr::foldRight($empty, $f, 0), 0);
Assert::same(Arr::foldRight($empty, $f), null);


reduce:
Assert::same(Arr::reduce($array, $f), 10);
Assert::same(Arr::reduce($empty, $f), null);


reduceLeft:
Assert::same(Arr::reduceLeft($array, $f), 10);
Assert::same(Arr::reduceLeft($empty, $f), null);


reduceRight:
Assert::same(Arr::reduceRight($array, $f), 10);
Assert::same(Arr::reduceRight($empty, $f), null);


scanLeft:
Assert::same(Arr::scanLeft($array, $f, 0), [0, 1, 3, 6, 10]);


scanRight:
Assert::same(Arr::scanRight($array, $f, 0), [10, 9, 7, 4, 0]);
