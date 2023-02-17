<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping\Naming;

use Dogma\Mapping\Naming\ShortFieldNamingStrategy;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$strategy = new ShortFieldNamingStrategy();

Assert::same($strategy->translateName('fieldPath.fieldName', '', '.'), 'fieldName');
