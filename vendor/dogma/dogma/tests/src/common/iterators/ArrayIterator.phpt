<?php declare(strict_types = 1);

namespace Dogma\Tests\ArrayIterator;

use Dogma\ArrayIterator;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$array = [1, 2, 3];
$empty = [];

$result = [];
foreach (new ArrayIterator($array) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [1, 2, 3]);

$result = [];
foreach (new ArrayIterator($empty) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, []);
