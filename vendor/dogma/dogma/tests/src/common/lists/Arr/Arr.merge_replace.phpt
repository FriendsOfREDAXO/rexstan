<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\Arr;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = [1, 2, 3, 4];
$empty = [];


append:
Assert::same(Arr::append($array, 5, 6), [1, 2, 3, 4, 5, 6]);
Assert::same(Arr::append($empty, 5, 6), [5, 6]);


appendAll:
Assert::same(Arr::appendAll($array, [5, 6]), [1, 2, 3, 4, 5, 6]);
Assert::same(Arr::appendAll($empty, [5, 6]), [5, 6]);


prepend:
Assert::same(Arr::prepend($array, 5, 6), [5, 6, 1, 2, 3, 4]);
Assert::same(Arr::prepend($empty, 5, 6), [5, 6]);


prependAll:
Assert::same(Arr::prependAll($array, [5, 6]), [5, 6, 1, 2, 3, 4]);
Assert::same(Arr::prependAll($empty, [5, 6]), [5, 6]);


replace:
Assert::same(Arr::replace($array, 0, 10), [10, 2, 3, 4]);
Assert::same(Arr::replace($empty, 0, 10), [10]);


replaceAll:
Assert::same(Arr::replaceAll($array, [0 => 10, 1 => 20]), [10, 20, 3, 4]);
Assert::same(Arr::replaceAll($empty, [0 => 10, 1 => 20]), [10, 20]);


remove:
Assert::same(Arr::remove($array, 1, 2), [1, 4]);
Assert::same(Arr::remove($empty, 1, 2), []);


patch:
Assert::same(Arr::patch($array, 1, [20, 30]), [1, 20, 30, 4]);
Assert::same(Arr::patch($array, 1, [20, 30], 1), [1, 20, 30, 3, 4]);
Assert::same(Arr::patch($empty, 1, [20, 30]), [20, 30]);
Assert::same(Arr::patch($empty, 1, [20, 30], 1), [20, 30]);


insert:
Assert::same(Arr::insert($array, 1, [20, 30]), [1, 20, 30, 2, 3, 4]);
Assert::same(Arr::insert($empty, 1, [20, 30]), [20, 30]);


merge:
Assert::same(Arr::merge($array, [5, 6]), [1, 2, 3, 4, 5, 6]);
Assert::same(Arr::merge($empty, [5, 6]), [5, 6]);
