<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping;

use Dogma\Mapping\Mapper;
use Dogma\Mapping\MappingStep;
use Dogma\Mapping\StaticMappingContainer;
use Dogma\Mapping\Type\Exportable;
use Dogma\Mapping\Type\ExportableHandler;
use Dogma\Mapping\Type\Time\DateTimeHandler;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Type;
use function array_key_exists;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/data/ExportableTestClass.php';
require_once __DIR__ . '/data/OuterTestClass.php';

$mapper = new Mapper(new StaticMappingContainer([]));

// single parameter mapping
$data = [
    'other' => 0,
    'dateString' => '2001-02-03',
];

$trans1 = new MappingStep(
    Type::get(Date::class),
    new DateTimeHandler(),
    ['dateString' => ''],
    'date'
);

$trans1->stepForward($data, $mapper);
Assert::count($data, 2);
Assert::false(array_key_exists('dateString', $data));
Assert::true(array_key_exists('date', $data));
Assert::true(array_key_exists('other', $data));
/** @var Date $date */
$date = $data['date'];
Assert::type($date, Date::class);
Assert::same($date->format(), '2001-02-03');

$trans1->stepBack($data, $mapper);
Assert::count($data, 2);
Assert::true(array_key_exists('dateString', $data));
Assert::false(array_key_exists('date', $data));
Assert::true(array_key_exists('other', $data));
Assert::same($data['dateString'], '2001-02-03');

// multiple parameters mapping
$data = [
    'other' => 0,
    'exp.one' => 1,
    'exp.two' => 2.0,
];

$trans2 = new MappingStep(
    Type::get(ExportableTestClass::class),
    new ExportableHandler(new MethodTypeParser()),
    ['exp.one' => 'one', 'exp.two' => 'two'],
    'exp'
);

$trans2->stepForward($data, $mapper);
Assert::count($data, 2);
Assert::false(array_key_exists('exp.one', $data));
Assert::false(array_key_exists('exp.two', $data));
Assert::true(array_key_exists('exp', $data));
Assert::true(array_key_exists('other', $data));
/** @var Exportable $exp */
$exp = $data['exp'];
Assert::type($exp, ExportableTestClass::class);
Assert::same($exp->export(), ['one' => 1, 'two' => 2.0]);

$trans2->stepBack($data, $mapper);
Assert::count($data, 3);
Assert::true(array_key_exists('exp.one', $data));
Assert::true(array_key_exists('exp.two', $data));
Assert::false(array_key_exists('exp', $data));
Assert::true(array_key_exists('other', $data));
Assert::same($data['exp.one'], 1);
Assert::same($data['exp.two'], 2.0);

// null mapping
$data = [
    'other' => 0,
    'exp.one' => null,
    'exp.two' => null,
];

$trans2->stepForward($data, $mapper);
Assert::count($data, 2);
Assert::false(array_key_exists('exp.one', $data));
Assert::false(array_key_exists('exp.two', $data));
Assert::true(array_key_exists('exp', $data));
Assert::true(array_key_exists('other', $data));
Assert::same($data['exp'], null);

$trans2->stepBack($data, $mapper);
Assert::count($data, 3);
Assert::true(array_key_exists('exp.one', $data));
Assert::true(array_key_exists('exp.two', $data));
Assert::false(array_key_exists('exp', $data));
Assert::true(array_key_exists('other', $data));
Assert::same($data['exp.one'], null);
Assert::same($data['exp.two'], null);
