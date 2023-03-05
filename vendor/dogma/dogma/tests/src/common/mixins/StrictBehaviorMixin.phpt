<?php declare(strict_types = 1);

namespace Dogma\Tests\StrictBehaviorMixin;

use Dogma\StrictBehaviorMixin;
use Dogma\Tester\Assert;
use Dogma\UndefinedMethodException;
use Dogma\UndefinedPropertyException;

require_once __DIR__ . '/../../bootstrap.php';

class TestClass
{
    use StrictBehaviorMixin;

}

$x = new TestClass();

Assert::throws(static function (): void {
    TestClass::method();
}, UndefinedMethodException::class);

Assert::throws(static function () use ($x): void {
    $x->method();
}, UndefinedMethodException::class);

Assert::throws(static function () use ($x): void {
    $x->property;
}, UndefinedPropertyException::class);

Assert::throws(static function () use ($x): void {
    $x->property = 1;
}, UndefinedPropertyException::class);

Assert::throws(static function () use ($x): void {
    isset($x->property);
}, UndefinedPropertyException::class);

Assert::throws(static function () use ($x): void {
    unset($x->property);
}, UndefinedPropertyException::class);
