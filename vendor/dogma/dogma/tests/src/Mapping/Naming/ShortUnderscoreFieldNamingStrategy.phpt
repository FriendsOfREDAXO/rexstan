<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping\Naming;

use Dogma\Mapping\Naming\ShortUnderscoreFieldNamingStrategy;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$strategy = new ShortUnderscoreFieldNamingStrategy();

Assert::same($strategy->translateName('fieldPath.fieldName', '', '.'), 'field_name');
