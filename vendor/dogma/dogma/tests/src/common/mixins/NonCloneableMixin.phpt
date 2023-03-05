<?php declare(strict_types = 1);

namespace Dogma\Tests\NonCloneableMixin;

use Dogma\NonCloneableMixin;
use Dogma\NonCloneableObjectException;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

class TestClass
{
    use NonCloneableMixin;

}

Assert::throws(static function (): void {
    $x = new TestClass();
    $y = clone($x);
}, NonCloneableObjectException::class);
