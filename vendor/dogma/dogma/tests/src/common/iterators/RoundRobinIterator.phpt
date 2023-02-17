<?php declare(strict_types = 1);

namespace Dogma\Tests\CombineIterator;

use Dogma\RoundRobinIterator;
use Dogma\Tester\Assert;
use Dogma\UnevenIteratorSourcesException;

require_once __DIR__ . '/../../bootstrap.php';

$result = [];
foreach (new RoundRobinIterator([1, 2, 3], [4, 5, 6], [7, 8, 9]) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [1, 4, 7, 2, 5, 8, 3, 6, 9]);

Assert::exception(static function (): void {
    $result = [];
    foreach (new RoundRobinIterator([1, 2], [4, 5, 6], [7, 8, 9]) as $k => $v) {
        $result[$k] = $v;
    }
}, UnevenIteratorSourcesException::class);

Assert::exception(static function (): void {
    $result = [];
    foreach (new RoundRobinIterator([1, 2, 3], [4, 5, 6], [7, 8]) as $k => $v) {
        $result[$k] = $v;
    }
}, UnevenIteratorSourcesException::class);

Assert::exception(static function (): void {
    $result = [];
    foreach (new RoundRobinIterator([], [4, 5, 6], [7, 8, 9]) as $k => $v) {
        $result[$k] = $v;
    }
}, UnevenIteratorSourcesException::class);

Assert::exception(static function (): void {
    $result = [];
    foreach (new RoundRobinIterator([1, 2, 3], [4, 5, 6], []) as $k => $v) {
        $result[$k] = $v;
    }
    // check that first iteration does not run at all
    Assert::same($result, []);
}, UnevenIteratorSourcesException::class);


$result = [];
foreach (RoundRobinIterator::uneven([1, 2], [4, 5, 6], [7, 8, 9]) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [1, 4, 7, 2, 5, 8, 6, 9]);

$result = [];
foreach (RoundRobinIterator::uneven([1, 2, 3], [4, 5, 6], [7, 8]) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [1, 4, 7, 2, 5, 8, 3, 6]);

$result = [];
foreach (RoundRobinIterator::uneven([], [4, 5, 6], [7, 8, 9]) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [4, 7, 5, 8, 6, 9]);

$result = [];
foreach (RoundRobinIterator::uneven([1, 2, 3], [4, 5, 6], []) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [1, 4, 2, 5, 3, 6]);
