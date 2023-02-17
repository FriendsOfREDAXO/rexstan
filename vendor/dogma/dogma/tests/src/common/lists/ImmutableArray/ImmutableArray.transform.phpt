<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\ImmutableArray;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3, 4]);
$empty = new ImmutableArray([]);

$f = static function (int $a, int $b): int {
    return $a + $b;
};


fold:
Assert::same($array->fold($f, 0), 10);
Assert::same($empty->fold($f, 0), 0);
Assert::same($empty->fold($f), null);


foldLeft:
Assert::same($array->foldLeft($f, 0), 10);
Assert::same($empty->foldLeft($f, 0), 0);
Assert::same($empty->foldLeft($f), null);


foldRight:
Assert::same($array->foldRight($f, 0), 10);
Assert::same($empty->foldRight($f, 0), 0);
Assert::same($empty->foldRight($f), null);


reduce:
Assert::same($array->reduce($f), 10);
Assert::same($empty->reduce($f), null);


reduceLeft:
Assert::same($array->reduceLeft($f), 10);
Assert::same($empty->reduceLeft($f), null);


reduceRight:
Assert::same($array->reduceRight($f), 10);
Assert::same($empty->reduceRight($f), null);


scanLeft:
Assert::same($array->scanLeft($f, 0)->toArray(), [0, 1, 3, 6, 10]);


scanRight:
Assert::same($array->scanRight($f, 0)->toArray(), [10, 9, 7, 4, 0]);
