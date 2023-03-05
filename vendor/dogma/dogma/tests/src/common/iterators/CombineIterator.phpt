<?php declare(strict_types = 1);

namespace Dogma\Tests\CombineIterator;

use Dogma\CombineIterator;
use Dogma\Tester\Assert;
use Dogma\UnevenIteratorSourcesException;

require_once __DIR__ . '/../../bootstrap.php';

$values = [1, 2, 3];
$keys = [4, 5, 6];
$result = [];
foreach (new CombineIterator($keys, $values) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [4 => 1, 5 => 2, 6 => 3]);

Assert::exception(static function (): void {
    $values = [1, 2, 3];
    $keys = [4, 5];
    $result = [];
    foreach (new CombineIterator($keys, $values) as $k => $v) {
        $result[$k] = $v;
    }
}, UnevenIteratorSourcesException::class);

Assert::exception(static function (): void {
    $values = [1, 2];
    $keys = [4, 5, 6];
    $result = [];
    foreach (new CombineIterator($keys, $values) as $k => $v) {
        $result[$k] = $v;
    }
}, UnevenIteratorSourcesException::class);

Assert::exception(static function (): void {
    $values = [1, 2, 3];
    $keys = [];
    $result = [];
    foreach (new CombineIterator($keys, $values) as $k => $v) {
        $result[$k] = $v;
    }
}, UnevenIteratorSourcesException::class);

Assert::exception(static function (): void {
    $values = [];
    $keys = [4, 5, 6];
    $result = [];
    foreach (new CombineIterator($keys, $values) as $k => $v) {
        $result[$k] = $v;
    }
    Assert::same($result, []);
}, UnevenIteratorSourcesException::class);
