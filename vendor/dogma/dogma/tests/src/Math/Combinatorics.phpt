<?php declare(strict_types = 1);

namespace Dogma\Tests\Math;

use Dogma\Math\Combinatorics;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


sumFactorize:
Assert::same(Combinatorics::sumFactorize(4), [
    [4],
    [3, 1],
    [2, 2],
    [2, 1, 1],
    [1, 3],
    [1, 2, 1],
    [1, 1, 2],
    [1, 1, 1, 1],
]);


getAllSubstringCombinations:
Assert::same(Combinatorics::getAllSubstringCombinations('abcd'), [
    ['abcd'],
    ['abc', 'd'],
    ['ab', 'cd'],
    ['ab', 'c', 'd'],
    ['a', 'bcd'],
    ['a', 'bc', 'd'],
    ['a', 'b', 'cd'],
    ['a', 'b', 'c', 'd'],
]);
