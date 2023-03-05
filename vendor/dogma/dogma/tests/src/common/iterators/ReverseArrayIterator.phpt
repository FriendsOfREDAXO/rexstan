<?php declare(strict_types = 1);

namespace Dogma\Tests\ReverseArrayIterator;

use Dogma\ReverseArrayIterator;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$array = [1, 2, 3];
$empty = [];

$result = [];
foreach (new ReverseArrayIterator($array) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [2 => 3, 1 => 2, 0 => 1]);

$result = [];
foreach (new ReverseArrayIterator($empty) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, []);
