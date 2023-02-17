<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping\Type\Enum;

use Dogma\Enum\IntEnum;
use Dogma\Mapping\Mapper;
use Dogma\Mapping\StaticMappingContainer;
use Dogma\Mapping\Type\Enum\EnumHandler;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Type;

require_once __DIR__ . '/../../../bootstrap.php';

class TestEnum extends IntEnum
{

    public const ONE = 1;
    public const TWO = 2;

}

$handler = new EnumHandler();
$mapper = new Mapper(new StaticMappingContainer([]));

$enumType = Type::get(TestEnum::class);


acceptType:
Assert::true($handler->acceptsType($enumType));
Assert::false($handler->acceptsType(Type::get(Date::class)));


getParameters:
Assert::equal($handler->getParameters($enumType), null);


createInstance:
$enumInstance = $handler->createInstance($enumType, 1, $mapper);
Assert::equal($enumInstance, TestEnum::get(TestEnum::ONE));


exportInstance:
Assert::same($handler->exportInstance($enumType, $enumInstance, $mapper), 1);
