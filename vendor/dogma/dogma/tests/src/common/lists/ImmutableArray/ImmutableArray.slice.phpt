<?php declare(strict_types = 1);

namespace Dogma\Tests\ImmutableArray;

use Dogma\ImmutableArray;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3, 4]);
$empty = new ImmutableArray([]);

$f = static function ($v): bool {
    return $v < 3;
};


head:
Assert::same($array->head(), 1);
Assert::null($empty->head());


first:
Assert::same($array->first(), 1);
Assert::null($empty->first());


last:
Assert::same($array->last(), 4);
Assert::null($empty->last());


init:
Assert::same($array->init()->toArray(), [1, 2, 3]);
Assert::same($empty->init()->toArray(), []);


tail:
Assert::same($array->tail()->toArray(), [1 => 2, 3, 4]);
Assert::same($empty->tail()->toArray(), []);


inits:
Assert::same($array->inits()->toArrayRecursive(), [[1, 2, 3, 4], [1, 2, 3], [1, 2], [1], []]);
Assert::same($empty->inits()->toArrayRecursive(), [[]]);


tails:
Assert::same($array->tails()->toArrayRecursive(), [[1, 2, 3, 4], [1 => 2, 3, 4], [2 => 3, 4], [3 => 4], []]);
Assert::same($empty->tails()->toArrayRecursive(), [[]]);


headTail:
/** @var ImmutableArray $tail */
[$head, $tail] = $array->headTail();
Assert::same($head, 1);
Assert::same($tail->toArray(), [1 => 2, 3, 4]);
[$head, $tail] = $empty->headTail();
Assert::null($head);
Assert::same($tail->toArray(), []);


initLast:
/** @var ImmutableArray $init */
[$init, $last] = $array->initLast();
Assert::same($init->toArray(), [1, 2, 3]);
Assert::same($last, 4);
[$init, $last] = $empty->initLast();
Assert::same($init->toArray(), []);
Assert::null($last);


slice:
Assert::same($array->slice(1)->toArray(), [1 => 2, 3, 4]);
Assert::same($array->slice(1, 2)->toArray(), [1 => 2, 3]);
Assert::same($array->slice(10)->toArray(), []);
Assert::same($empty->slice(1)->toArray(), []);


chunks:
Assert::same($array->chunks(2)->toArrayRecursive(), [[1, 2], [2 => 3, 4]]);
Assert::same($empty->chunks(2)->toArrayRecursive(), []);


sliding:
Assert::same($array->sliding(2)->toArrayRecursive(), [[1, 2], [1 => 2, 3], [2 => 3, 4]]);
Assert::same($array->sliding(3)->toArrayRecursive(), [[1, 2, 3], [1 => 2, 3, 4]]);
Assert::same($array->sliding(2, 2)->toArrayRecursive(), [[1, 2], [2 => 3, 4]]);
Assert::same($empty->sliding(2)->toArrayRecursive(), []);


drop:
Assert::same($array->drop(2)->toArray(), [2 => 3, 4]);
Assert::same($empty->drop(2)->toArray(), []);


dropRight:
Assert::same($array->dropRight(2)->toArray(), [1, 2]);
Assert::same($empty->dropRight(2)->toArray(), []);


dropWhile:
Assert::same($array->dropWhile($f)->toArray(), [2 => 3, 4]);
Assert::same($empty->dropWhile($f)->toArray(), []);


padTo:
Assert::same($array->padTo(7, 6)->toArray(), [1, 2, 3, 4, 6, 6, 6]);
Assert::same($empty->padTo(3, 6)->toArray(), [6, 6, 6]);


span:
[$l, $r] = $array->span($f);
Assert::same($l->toArray(), [1, 2]);
Assert::same($r->toArray(), [2 => 3, 4]);
[$l, $r] = $empty->span($f);
Assert::same($l->toArray(), []);
Assert::same($r->toArray(), []);


take:
Assert::same($array->take(2)->toArray(), [1, 2]);
Assert::same($empty->take(2)->toArray(), []);


takeRight:
Assert::same($array->takeRight(2)->toArray(), [2 => 3, 4]);
Assert::same($empty->takeRight(2)->toArray(), []);


takeWhile:
Assert::same($array->takeWhile($f)->toArray(), [1, 2]);
Assert::same($empty->takeWhile($f)->toArray(), []);

$array = new ImmutableArray([1, 2, 3, 4, 5, 6]);


rotateLeft:
Assert::same($empty->rotateLeft(2)->toArray(), []);
Assert::same($array->rotateLeft(2)->toArray(), [3, 4, 5, 6, 1, 2]);
Assert::same($array->rotateLeft(8)->toArray(), [3, 4, 5, 6, 1, 2]);


rotateRight:
Assert::same($empty->rotateRight(2)->toArray(), []);
Assert::same($array->rotateRight(2)->toArray(), [5, 6, 1, 2, 3, 4]);
Assert::same($array->rotateRight(8)->toArray(), [5, 6, 1, 2, 3, 4]);
