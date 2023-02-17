<?php declare(strict_types = 1);

namespace Dogma\Tests\ArrayIterator;

use Dogma\InfiniteArrayIterator;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$array = [1, 2, 3];
$empty = [];

$result = [];
foreach (new InfiniteArrayIterator($array) as $k => $v) {
    $result[] = $v;
    if (count($result) > 8) {
        break;
    }
}
Assert::same($result, [1, 2, 3, 1, 2, 3, 1, 2, 3]);

$result = [];
foreach (new InfiniteArrayIterator($empty) as $k => $v) {
    $result[] = $v;
    if (count($result) > 8) {
        break;
    }
}
Assert::same($result, []);
