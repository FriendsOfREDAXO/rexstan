<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use ArrayIterator;
use Dogma\ImmutableArray;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3, 4]);


containsSlice:
Assert::false($array->containsSlice([3, 4, 5]));
Assert::true($array->containsSlice([2, 3, 4]));
Assert::true($array->containsSlice(new ArrayIterator([2, 3, 4])));


indexOfSlice:
Assert::null($array->indexOfSlice([3, 4, 5]));
Assert::same($array->indexOfSlice([2, 3, 4]), 1);
Assert::same($array->indexOfSlice(new ArrayIterator([2, 3, 4])), 1);


corresponds:
Assert::false($array->corresponds([1, 4, 9], static function ($a, $b): bool {
    return $a * $a === $b;
}));
Assert::true($array->corresponds([1, 4, 9, 16], static function ($a, $b): bool {
    return $a * $a === $b;
}));
Assert::false($array->corresponds([1, 4, 9, 16, 25], static function ($a, $b): bool {
    return $a * $a === $b;
}));
Assert::false($array->corresponds([1, 2, 3, 4], static function ($a, $b): bool {
    return $a * $a === $b;
}));
Assert::true($array->corresponds(new ArrayIterator([1, 4, 9, 16]), static function ($a, $b): bool {
    return $a * $a === $b;
}));


hasSameElements:
Assert::false($array->hasSameElements([1, 1, 1, 1]));
Assert::true($array->hasSameElements([1, 2, 3, 4]));
Assert::true($array->hasSameElements(new ArrayIterator([1, 2, 3, 4])));


startsWith:
Assert::false($array->startsWith([2, 3, 4]));
Assert::true($array->startsWith([1, 2, 3]));
Assert::true($array->startsWith(new ArrayIterator([1, 2, 3])));


endsWith:
Assert::false($array->endsWith([1, 2, 3]));
Assert::true($array->endsWith([2, 3, 4]));
Assert::true($array->endsWith(new ArrayIterator([2, 3, 4])));
