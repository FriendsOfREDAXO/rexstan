<?php declare(strict_types = 1);

namespace Dogma\Tests\Tuple;

use BadMethodCallException;
use Dogma\Tester\Assert;
use Dogma\Tuple;

require_once __DIR__ . '/../bootstrap.php';


$tuple = new Tuple(123, 'abc');

Assert::same($tuple->toArray(), [123, 'abc']);

Assert::same(count($tuple), 2);

Assert::true($tuple->offsetExists(0));
Assert::true($tuple->offsetExists(1));
Assert::false($tuple->offsetExists(2));
Assert::same($tuple[0], 123);
Assert::same($tuple[1], 'abc');
Assert::exception(static function () use ($tuple): void {
    $tuple[0] = 456;
}, BadMethodCallException::class);
Assert::exception(static function () use ($tuple): void {
    unset($tuple[0]);
}, BadMethodCallException::class);
