<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use ArrayIterator;
use Dogma\ImmutableArray;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3, 4]);
$empty = new ImmutableArray([]);


append:
Assert::same($array->append(5, 6)->toArray(), [1, 2, 3, 4, 5, 6]);
Assert::same($empty->append(5, 6)->toArray(), [5, 6]);


appendAll:
Assert::same($array->appendAll([5, 6])->toArray(), [1, 2, 3, 4, 5, 6]);
Assert::same($array->appendAll(new ImmutableArray([5, 6]))->toArray(), [1, 2, 3, 4, 5, 6]);
Assert::same($array->appendAll(new ArrayIterator([5, 6]))->toArray(), [1, 2, 3, 4, 5, 6]);
Assert::same($empty->appendAll([5, 6])->toArray(), [5, 6]);


prepend:
Assert::same($array->prepend(5, 6)->toArray(), [5, 6, 1, 2, 3, 4]);
Assert::same($empty->prepend(5, 6)->toArray(), [5, 6]);


prependAll:
Assert::same($array->prependAll([5, 6])->toArray(), [5, 6, 1, 2, 3, 4]);
Assert::same($array->prependAll(new ImmutableArray([5, 6]))->toArray(), [5, 6, 1, 2, 3, 4]);
Assert::same($array->prependAll(new ArrayIterator([5, 6]))->toArray(), [5, 6, 1, 2, 3, 4]);
Assert::same($empty->prependAll([5, 6])->toArray(), [5, 6]);


replace:
Assert::same($array->replace(0, 10)->toArray(), [10, 2, 3, 4]);
Assert::same($empty->replace(0, 10)->toArray(), [10]);


replaceAll:
Assert::same($array->replaceAll([0 => 10, 1 => 20])->toArray(), [10, 20, 3, 4]);
Assert::same($array->replaceAll(new ImmutableArray([0 => 10, 1 => 20]))->toArray(), [10, 20, 3, 4]);
Assert::same($array->replaceAll(new ArrayIterator([0 => 10, 1 => 20]))->toArray(), [10, 20, 3, 4]);
Assert::same($empty->replaceAll([0 => 10, 1 => 20])->toArray(), [10, 20]);


remove:
Assert::same($array->remove(1, 2)->toArray(), [1, 4]);
Assert::same($empty->remove(1, 2)->toArray(), []);


patch:
Assert::same($array->patch(1, [20, 30])->toArray(), [1, 20, 30, 4]);
Assert::same($array->patch(1, [20, 30], 1)->toArray(), [1, 20, 30, 3, 4]);
Assert::same($empty->patch(1, [20, 30])->toArray(), [20, 30]);
Assert::same($empty->patch(1, [20, 30], 1)->toArray(), [20, 30]);


insert:
Assert::same($array->insert(1, [20, 30])->toArray(), [1, 20, 30, 2, 3, 4]);
Assert::same($empty->insert(1, [20, 30])->toArray(), [20, 30]);


merge:
Assert::same($array->merge([5, 6])->toArray(), [1, 2, 3, 4, 5, 6]);
Assert::same($empty->merge([5, 6])->toArray(), [5, 6]);
