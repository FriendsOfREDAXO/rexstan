<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Mapping\StaticMappingContainer;
use Dogma\Mapping\Type\TupleHandler;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../../bootstrap.php';

$handler = new TupleHandler();
$mapper = new Mapper(new StaticMappingContainer([]));

$tupleType = Type::tupleOf(Type::INT, Type::STRING);


acceptType:
Assert::true($handler->acceptsType($tupleType));
Assert::false($handler->acceptsType(Type::get(Type::PHP_ARRAY)));


getParameters:
Assert::same($handler->getParameters($tupleType), [Type::get(Type::INT), Type::get(Type::STRING)]);


createInstance:
$tupleInstance = $handler->createInstance($tupleType, [123, 'abc'], $mapper);
Assert::same($tupleInstance->toArray(), [123, 'abc']);


exportInstance:
Assert::same($handler->exportInstance($tupleType, $tupleInstance, $mapper), [123, 'abc']);
