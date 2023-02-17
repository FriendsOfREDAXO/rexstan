<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping;

use Dogma\Mapping\Mapper;
use Dogma\Mapping\Mapping;
use Dogma\Mapping\MappingStep;
use Dogma\Mapping\StaticMappingContainer;
use Dogma\Mapping\Type\ExportableHandler;
use Dogma\Mapping\Type\ScalarsHandler;
use Dogma\Mapping\Type\TypeHandler;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/data/ExportableTestClass.php';
require_once __DIR__ . '/data/OuterTestClass.php';

$step1 = new MappingStep(
    Type::get(Type::INT),
    new ScalarsHandler(),
    ['far_one' => TypeHandler::SINGLE_PARAMETER],
    'exp.one'
);
$step2 = new MappingStep(
    Type::get(Type::FLOAT),
    new ScalarsHandler(),
    ['far_two' => TypeHandler::SINGLE_PARAMETER],
    'exp.two'
);
$step3 = new MappingStep(
    Type::get(ExportableTestClass::class),
    new ExportableHandler(new MethodTypeParser()),
    ['exp.one' => 'one', 'exp.two' => 'two'],
    TypeHandler::SINGLE_PARAMETER
);

$mapping = new Mapping(Type::get(ExportableTestClass::class), [$step1, $step2, $step3]);
$mapper = new Mapper(new StaticMappingContainer([]));

$data = [
    'far_one' => '123',
    'far_two' => '1.23',
];

$exportableInstance = $mapping->mapForward($data, $mapper);
Assert::type($exportableInstance, ExportableTestClass::class);
Assert::same($exportableInstance->export(), ['one' => 123, 'two' => 1.23]);

$data = $mapping->mapBack($exportableInstance, $mapper);
ksort($data);
Assert::same($data, ['far_one' => 123, 'far_two' => 1.23]);
