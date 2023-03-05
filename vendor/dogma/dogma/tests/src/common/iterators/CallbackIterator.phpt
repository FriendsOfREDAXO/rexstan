<?php declare(strict_types = 1);

namespace Dogma\Tests\CallbackIterator;

use Dogma\ArrayIterator;
use Dogma\CallbackIterator;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$power = static function ($x) {
    return $x * $x;
};

$array = new ArrayIterator([1, 2, 3]);
$powers = new ArrayIterator([1, 4, 9]);
$empty = new ArrayIterator([]);

$result = [];
foreach (new CallbackIterator($array, $power) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [1, 4, 9]);

$result = [];
foreach (new CallbackIterator($powers, 'sqrt') as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [1.0, 2.0, 3.0]);

$result = [];
foreach (new CallbackIterator($empty, $power) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, []);
