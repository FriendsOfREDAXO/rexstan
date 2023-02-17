<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping\Type\Time;

use Dogma\Mapping\Mapper;
use Dogma\Mapping\StaticMappingContainer;
use Dogma\Mapping\Type\Time\DateTimeHandler;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\Time;
use Dogma\Type;

require_once __DIR__ . '/../../../bootstrap.php';

$handler = new DateTimeHandler();
$mapper = new Mapper(new StaticMappingContainer([]));

$dateTimeType = Type::get(DateTime::class);
$dateType = Type::get(Date::class);
$timeType = Type::get(Time::class);


acceptType:
Assert::false($handler->acceptsType(Type::get(Assert::class)));
Assert::true($handler->acceptsType($dateTimeType));
Assert::true($handler->acceptsType($dateType));
Assert::true($handler->acceptsType($timeType));


getParameters:
Assert::same($handler->getParameters($dateTimeType), null);
Assert::same($handler->getParameters($dateType), null);
Assert::same($handler->getParameters($timeType), null);

$dateTimeString = '2000-01-02 03:04:05';
$dateString = '2000-01-02';
$timeString = '03:04:05';


createInstance:
$dateTimeInstance = $handler->createInstance($dateTimeType, $dateTimeString, $mapper);
Assert::type($dateTimeInstance, DateTime::class);
Assert::same($dateTimeInstance->format(), $dateTimeString . '.000000');

$dateInstance = $handler->createInstance($dateType, $dateString, $mapper);
Assert::type($dateInstance, Date::class);
Assert::same($dateInstance->format(), $dateString);

$timeInstance = $handler->createInstance($timeType, $timeString, $mapper);
Assert::type($timeInstance, Time::class);
Assert::same($timeInstance->format(), $timeString . '.000000');


exportInstance:
Assert::same($handler->exportInstance($dateTimeType, $dateTimeInstance, $mapper), $dateTimeString);
Assert::same($handler->exportInstance($dateType, $dateInstance, $mapper), $dateString);
Assert::same($handler->exportInstance($timeType, $timeInstance, $mapper), $timeString);
