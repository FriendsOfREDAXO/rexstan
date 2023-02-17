<?php declare(strict_types = 1);

namespace SqlFtw\Tests\Sql;

use SqlFtw\Sql\SqlEnum;
use SqlFtw\Tests\Assert;

require __DIR__ . '/../bootstrap.php';

class TestEnum extends SqlEnum
{

    public const LOWER = 'lower';
    public const UPPER = 'UPPER';
    public const MIXED = 'MixEd';

}

$lower = new TestEnum('Lower');
Assert::same($lower->getValue(), 'lower');
Assert::true($lower->equalsValue('Lower'));
Assert::true(TestEnum::isValidValue('Lower'));

$upper = new TestEnum('Upper');
Assert::same($upper->getValue(), 'UPPER');
Assert::true($upper->equalsValue('Upper'));
Assert::true(TestEnum::isValidValue('Upper'));

$mixed = new TestEnum('Mixed');
Assert::same($mixed->getValue(), 'MixEd');
Assert::true($mixed->equalsValue('Mixed'));
Assert::true(TestEnum::isValidValue('Mixed'));
