<?php declare(strict_types = 1);

namespace Dogma\Tests\Language\Locale;

use Dogma\Language\Encoding;
use Dogma\Tester\Assert;


require_once __DIR__ . '/../bootstrap.php';

canCheck:
Assert::true(Encoding::canCheck('latin1'));
Assert::false(Encoding::canCheck('latin666'));

check:
Assert::true(Encoding::check('foo', 'latin1'));
Assert::false(Encoding::check("foo\x84", 'latin1'));
