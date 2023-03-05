<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use Dogma\Tester\Assert;
use Dogma\Time\DateTime;
use Dogma\Type;
use Error;

require_once __DIR__ . '/../bootstrap.php';

$int = Type::int();

$array = Type::get(Type::PHP_ARRAY);
$arrayNullable = Type::get(Type::PHP_ARRAY, Type::NULLABLE);
$arrayOfInt = Type::arrayOf($int);
$arrayOfIntNullable = Type::arrayOf($int, Type::NULLABLE);


getId:
Assert::same($array->getId(), 'array<mixed>');
Assert::same($arrayNullable->getId(), 'array<mixed>?');
Assert::same($arrayOfInt->getId(), 'array<int>');
Assert::same($arrayOfIntNullable->getId(), 'array<int>?');


fromId:
Assert::same(Type::fromId('array'), $array);
Assert::same(Type::fromId('array?'), $arrayNullable);
Assert::same(Type::fromId('array<int>'), $arrayOfInt);
Assert::same(Type::fromId('array<int>?'), $arrayOfIntNullable);


getName:
Assert::same($array->getName(), Type::PHP_ARRAY);


isNullable:
Assert::false($array->isNullable());
Assert::true($arrayNullable->isNullable());


isSigned:
Assert::false($array->isSigned());


isUnsigned:
Assert::false($array->isUnsigned());


isFixed:
Assert::false($array->isFixed());


getResourceType:
Assert::null($array->getResourceType());


getItemType:
Assert::same($array->getItemType(), Type::get(Type::MIXED));
Assert::same($arrayOfInt->getItemType(), Type::int());


getSize:
Assert::null($array->getSize());


getEncoding:
Assert::null($array->getEncoding());


getLocale:
Assert::null($array->getLocale());


isBool:
Assert::false($array->isBool());


isInt:
Assert::false($array->isInt());


isFloat:
Assert::false($array->isFloat());


isNumeric:
Assert::false($array->isNumeric());


isString:
Assert::false($array->isString());


isScalar:
Assert::false($array->isScalar());


isArray:
Assert::true($array->isArray());
Assert::true($arrayOfInt->isArray());


isCollection:
Assert::false($array->isCollection());
Assert::false($arrayOfInt->isCollection());


isTuple:
Assert::false($array->isTuple());


isClass:
Assert::false($array->isClass());


isCallable:
Assert::false($array->isCallable());


isResource:
Assert::false($array->isResource());


is:
Assert::true($array->is(Type::PHP_ARRAY));
Assert::false($array->is(DateTime::class));


isImplementing:
Assert::false($array->isImplementing(DateTime::class));
Assert::false($array->is(DateTime::class));


getBaseType:
Assert::same($arrayNullable->getBaseType(), $array);
Assert::same($array->getBaseType(), $array);


getNonNullableType:
Assert::same($arrayNullable->getNonNullableType(), $array);
Assert::same($array->getNonNullableType(), $array);


getTypeWithoutParams:
Assert::same($arrayNullable->getTypeWithoutParams(), $arrayNullable);
Assert::same($array->getTypeWithoutParams(), $array);


getInstance:
Assert::exception(static function () use ($array): void {
    $array->getInstance('abc');
}, Error::class);
