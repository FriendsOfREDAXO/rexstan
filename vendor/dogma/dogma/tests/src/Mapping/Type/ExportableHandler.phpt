<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Mapping\StaticMappingContainer;
use Dogma\Mapping\Type\ExportableHandler;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Tester\Assert;
use Dogma\Tests\Mapping\ExportableTestClass;
use Dogma\Time\Date;
use Dogma\Type;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../data/ExportableTestClass.php';
require_once __DIR__ . '/../data/OuterTestClass.php';

$paramsParser = new MethodTypeParser();
$handler = new ExportableHandler($paramsParser);
$mapper = new Mapper(new StaticMappingContainer([]));

$exportableType = Type::get(ExportableTestClass::class);


acceptType:
Assert::true($handler->acceptsType($exportableType));
Assert::false($handler->acceptsType(Type::get(Date::class)));


getParameters:
Assert::equal($handler->getParameters($exportableType), [
    'one' => Type::get(Type::INT),
    'two' => Type::get(Type::FLOAT),
]);


createInstance:
$data = ['one' => 1, 'two' => 1.23];
$exportableInstance = $handler->createInstance($exportableType, $data, $mapper);
Assert::type($exportableInstance, ExportableTestClass::class);


exportInstance:
Assert::same($handler->exportInstance($exportableType, $exportableInstance, $mapper), $data);
