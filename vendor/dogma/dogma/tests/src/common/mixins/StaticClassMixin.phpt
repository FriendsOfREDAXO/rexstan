<?php declare(strict_types = 1);

namespace Dogma\Tests\StaticClassMixin;

use Dogma\StaticClassException;
use Dogma\StaticClassMixin;
use Dogma\Tester\Assert;
use Dogma\UndefinedMethodException;

require_once __DIR__ . '/../../bootstrap.php';

class TestClass
{
    use StaticClassMixin;

}

Assert::throws(static function (): void {
    $x = new TestClass();
}, StaticClassException::class);

Assert::throws(static function (): void {
    TestClass::method();
}, UndefinedMethodException::class);
