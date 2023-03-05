<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping\Type;

use Dogma\Mapping\ConventionMappingBuilder;
use Dogma\Mapping\DynamicMappingContainer;
use Dogma\Mapping\Mapper;
use Dogma\Mapping\MetaData\TypeMetaDataContainer;
use Dogma\Mapping\Naming\ShortFieldNamingStrategy;
use Dogma\Mapping\Type\ArrayHandler;
use Dogma\Mapping\Type\ScalarsHandler;
use Dogma\Tester\Assert;
use Dogma\Type;
use SplFixedArray;

require_once __DIR__ . '/../../bootstrap.php';

$handler = new ArrayHandler();
$mapper = new Mapper(new DynamicMappingContainer(new ConventionMappingBuilder(
    new TypeMetaDataContainer([new ScalarsHandler()]),
    new ShortFieldNamingStrategy()
)));

$arrayType = Type::get(Type::PHP_ARRAY);
$intArrayType = Type::arrayOf(Type::INT);


acceptType:
Assert::true($handler->acceptsType($arrayType));
Assert::true($handler->acceptsType($intArrayType));
Assert::false($handler->acceptsType(Type::get(SplFixedArray::class)));


getParameters:
Assert::same($handler->getParameters($arrayType), null);
// intentionally does not return item type. items type must be mapped by ArrayHandler, since MappingBuilder
// can only handle non-iterable structures
Assert::same($handler->getParameters($intArrayType), null);


createInstance:
$arrayInstance = $handler->createInstance($arrayType, ['1', '2'], $mapper);
Assert::same($arrayInstance, ['1', '2']);
$intArrayInstance = $handler->createInstance($intArrayType, ['1', '2'], $mapper);
Assert::same($intArrayInstance, [1, 2]);


exportInstance:
Assert::same($handler->exportInstance($intArrayType, $intArrayInstance, $mapper), [1, 2]); // expected (viz ScalarsHandler)
Assert::same($handler->exportInstance($arrayType, $arrayInstance, $mapper), ['1', '2']);
