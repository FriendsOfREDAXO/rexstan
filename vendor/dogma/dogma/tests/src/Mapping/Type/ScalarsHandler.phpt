<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Mapping\StaticMappingContainer;
use Dogma\Mapping\Type\ScalarsHandler;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../../bootstrap.php';

$handler = new ScalarsHandler();
$mapper = new Mapper(new StaticMappingContainer([]));

$boolType = Type::get(Type::BOOL);
$intType = Type::get(Type::INT);
$floatType = Type::get(Type::FLOAT);
$numericType = Type::get(Type::NUMBER);
$stringType = Type::get(Type::STRING);


acceptType:
Assert::false($handler->acceptsType(Type::get(Assert::class)));
Assert::true($handler->acceptsType($boolType));
Assert::true($handler->acceptsType($intType));
Assert::true($handler->acceptsType($floatType));
Assert::true($handler->acceptsType($numericType));
Assert::true($handler->acceptsType($stringType));


getParameters:
Assert::same($handler->getParameters($boolType), null);
Assert::same($handler->getParameters($intType), null);
Assert::same($handler->getParameters($floatType), null);
Assert::same($handler->getParameters($numericType), null);
Assert::same($handler->getParameters($stringType), null);


createInstance:
$boolInstance = $handler->createInstance($boolType, '1', $mapper);
Assert::same($boolInstance, true);
$intInstance = $handler->createInstance($intType, '123', $mapper);
Assert::same($intInstance, 123);
$floatInstance = $handler->createInstance($floatType, '1.23', $mapper);
Assert::same($floatInstance, 1.23);
$numericIntInstance = $handler->createInstance($numericType, '123', $mapper);
Assert::same($numericIntInstance, 123);
$numericFloatInstance = $handler->createInstance($numericType, '1.23', $mapper);
Assert::same($numericFloatInstance, 1.23);
$stringInstance = $handler->createInstance($stringType, 123, $mapper);
Assert::same($stringInstance, '123');


exportInstance:
// expected behavior - does not map back since the original type is unknown. proper reverse mapping must be
// implemented either by a specialised handler (e.g. MysqlScalarsHandler) or at an another layer (connection adapter)
Assert::same($handler->exportInstance($boolType, $boolInstance, $mapper), true);
Assert::same($handler->exportInstance($intType, $intInstance, $mapper), 123);
Assert::same($handler->exportInstance($floatType, $floatInstance, $mapper), 1.23);
Assert::same($handler->exportInstance($numericType, $numericIntInstance, $mapper), 123);
Assert::same($handler->exportInstance($numericType, $numericFloatInstance, $mapper), 1.23);
Assert::same($handler->exportInstance($stringType, $stringInstance, $mapper), '123');
