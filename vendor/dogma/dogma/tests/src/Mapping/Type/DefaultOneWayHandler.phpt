<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping\Type;

use DateTime;
use DateTimeZone;
use Dogma\Mapping\Mapper;
use Dogma\Mapping\StaticMappingContainer;
use Dogma\Mapping\Type\DefaultOneWayHandler;
use Dogma\Mapping\Type\OneWayHandlerException;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Tester\Assert;
use Dogma\Type;
use const PHP_VERSION_ID;

require_once __DIR__ . '/../../bootstrap.php';

$paramsParser = new MethodTypeParser();
$handler = new DefaultOneWayHandler($paramsParser);
$mapper = new Mapper(new StaticMappingContainer([]));

$dateTimeType = Type::get(DateTime::class);


acceptType:
Assert::true($handler->acceptsType($dateTimeType));
Assert::true($handler->acceptsType(Type::get('Any')));


getParameters:
if (PHP_VERSION_ID < 80000) {
    Assert::equal($handler->getParameters($dateTimeType), [
        'time' => Type::get(Type::MIXED),
        'timezone' => Type::get(Type::MIXED),
    ]);
} else {
    Assert::equal($handler->getParameters($dateTimeType), [
        'datetime' => Type::get(Type::STRING),
        'timezone' => Type::get(DateTimeZone::class, true),
    ]);
}


createInstance:
if (PHP_VERSION_ID < 80000) {
    $dateInstance = $handler->createInstance($dateTimeType, [
        'time' => '2001-02-03 04:05:06',
        'timezone' => new DateTimeZone('+01:00'),
    ], $mapper);
} else {
    $dateInstance = $handler->createInstance($dateTimeType, [
        'datetime' => '2001-02-03 04:05:06',
        'timezone' => new DateTimeZone('+01:00'),
    ], $mapper);
}
Assert::type($dateInstance, DateTime::class);
Assert::same($dateInstance->format('Y-m-d H:i:s'), '2001-02-03 04:05:06');


exportInstance:
Assert::throws(static function () use ($handler, $mapper, $dateTimeType): void {
    $handler->exportInstance($dateTimeType, new DateTime(), $mapper);
}, OneWayHandlerException::class);
