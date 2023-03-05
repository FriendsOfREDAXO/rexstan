<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\Arr;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = [1, 2, 3, 4];


containsSlice:
Assert::false(Arr::containsSlice($array, [3, 4, 5]));
Assert::true(Arr::containsSlice($array, [2, 3, 4]));


indexOfSlice:
Assert::null(Arr::indexOfSlice($array, [3, 4, 5]));
Assert::same(Arr::indexOfSlice($array, [2, 3, 4]), 1);


corresponds:
Assert::false(Arr::corresponds($array, [1, 4, 9], static function ($a, $b): bool {
    return $a * $a === $b;
}));
Assert::true(Arr::corresponds($array, [1, 4, 9, 16], static function ($a, $b): bool {
    return $a * $a === $b;
}));
Assert::false(Arr::corresponds($array, [1, 4, 9, 16, 25], static function ($a, $b): bool {
    return $a * $a === $b;
}));
Assert::false(Arr::corresponds($array, [1, 2, 3, 4], static function ($a, $b): bool {
    return $a * $a === $b;
}));


hasSameElements:
Assert::false(Arr::hasSameElements($array, [1, 1, 1, 1]));
Assert::true(Arr::hasSameElements($array, [1, 2, 3, 4]));


startsWith:
Assert::false(Arr::startsWith($array, [2, 3, 4]));
Assert::true(Arr::startsWith($array, [1, 2, 3]));


endsWith:
Assert::false(Arr::endsWith($array, [1, 2, 3]));
Assert::true(Arr::endsWith($array, [2, 3, 4]));
