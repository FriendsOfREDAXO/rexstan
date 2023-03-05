<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use DateTime;
use Dogma\Tester\Assert;
use Dogma\Type;
use Error;

require_once __DIR__ . '/../bootstrap.php';

$callable = Type::callable();
$callableNullable = Type::callable(Type::NULLABLE);


getId:
Assert::same($callable->getId(), 'callable');
Assert::same($callableNullable->getId(), 'callable?');


fromId:
Assert::same(Type::fromId('callable'), $callable);
Assert::same(Type::fromId('callable?'), $callableNullable);


getName:
Assert::same($callable->getName(), Type::PHP_CALLABLE);


isNullable:
Assert::false($callable->isNullable());
Assert::true($callableNullable->isNullable());


isSigned:
Assert::false($callable->isSigned());


isUnsigned:
Assert::false($callable->isUnsigned());


isFixed:
Assert::false($callable->isFixed());


getResourceType:
Assert::null($callable->getResourceType());


getItemType:
Assert::null($callable->getItemType());


getSize:
Assert::null($callable->getSize());


getEncoding:
Assert::null($callable->getEncoding());


getLocale:
Assert::null($callable->getLocale());


isBool:
Assert::false($callable->isBool());


isInt:
Assert::false($callable->isInt());


isFloat:
Assert::false($callable->isFloat());


isNumeric:
Assert::false($callable->isNumeric());


isString:
Assert::false($callable->isString());


isScalar:
Assert::false($callable->isScalar());


isArray:
Assert::false($callable->isArray());


isCollection:
Assert::false($callable->isCollection());


isTuple:
Assert::false($callable->isTuple());


isClass:
Assert::false($callable->isClass());


isCallable:
Assert::true($callable->isCallable());


isResource:
Assert::false($callable->isResource());


is:
Assert::true($callable->is(Type::PHP_CALLABLE));
Assert::false($callable->is(DateTime::class));


isImplementing:
Assert::false($callable->isImplementing(DateTime::class));


getBaseType:
Assert::same($callable->getBaseType(), $callable);
Assert::same($callableNullable->getBaseType(), $callable);


getNonNullableType:
Assert::same($callable->getNonNullableType(), $callable);
Assert::same($callableNullable->getNonNullableType(), $callable);


getTypeWithoutParams:
Assert::same($callable->getTypeWithoutParams(), $callable);
Assert::same($callableNullable->getTypeWithoutParams(), $callableNullable);


getInstance:
Assert::exception(static function () use ($callable): void {
    $callable->getInstance();
}, Error::class);
