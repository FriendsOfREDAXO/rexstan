<?php declare(strict_types = 1);

namespace Dogma\Tests\ChunkIterator;

use Dogma\ChunkIterator;
use Dogma\Tester\Assert;
use Dogma\ValueOutOfRangeException;

require_once __DIR__ . '/../../bootstrap.php';

$array = range(1, 35);
$empty = [];

$result = [];
foreach (new ChunkIterator($array, 10) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [range(1, 10), range(11, 20), range(21, 30), range(31, 35)]);

$result = [];
foreach (new ChunkIterator($empty, 10) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, []);

Assert::throws(static function (): void {
    new ChunkIterator([], 0);
}, ValueOutOfRangeException::class);
