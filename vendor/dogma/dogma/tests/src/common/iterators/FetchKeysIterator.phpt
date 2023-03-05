<?php declare(strict_types = 1);

namespace Dogma\Tests\FetchKeysIterator;

use Dogma\FetchKeysIterator;
use Dogma\InvalidTypeException;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$empty = [];
$array = [['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4], ['a' => 5, 'b' => 6]];
$invalid = [1, 2, 3];

$result = [];
foreach (new FetchKeysIterator($empty, 'a', 'b') as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, []);

$result = [];
foreach (new FetchKeysIterator($array, 'a', 'b') as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [1 => 2, 3 => 4, 5 => 6]);

$result = [];
foreach (new FetchKeysIterator($array, null, 'b') as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [2, 4, 6]);

$result = [];
foreach (new FetchKeysIterator($array, 'a', null) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [1 => ['a' => 1, 'b' => 2], 3 => ['a' => 3, 'b' => 4], 5 => ['a' => 5, 'b' => 6]]);

$result = [];
foreach (new FetchKeysIterator($array, null, null) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, $array);

$result = [];
Assert::throws(static function () use ($invalid, $result): void {
    foreach (new FetchKeysIterator($invalid, 'a', null) as $k => $v) {
        $result[$k] = $v;
    }
}, InvalidTypeException::class);
Assert::same($result, []);

$result = [];
Assert::throws(static function () use ($invalid, $result): void {
    foreach (new FetchKeysIterator($invalid, null, 'b') as $k => $v) {
        $result[$k] = $v;
    }
}, InvalidTypeException::class);
Assert::same($result, []);
