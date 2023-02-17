<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use DateTime;
use Dogma\BitSize;
use Dogma\Tester\Assert;
use Dogma\Type;
use Error;

require_once __DIR__ . '/../bootstrap.php';

$float = Type::float();
$single = Type::float(BitSize::BITS_32);
$floatNullable = Type::float(Type::NULLABLE);


getId:
Assert::same($float->getId(), 'float');
Assert::same($single->getId(), 'float(32)');
Assert::same($floatNullable->getId(), 'float?');


fromId:
Assert::same(Type::fromId('float'), $float);
Assert::same(Type::fromId('float(32)'), $single);
Assert::same(Type::fromId('float?'), $floatNullable);


getName:
Assert::same($float->getName(), Type::FLOAT);


isNullable:
Assert::false($float->isNullable());
Assert::true($floatNullable->isNullable());


isSigned:
Assert::false($float->isSigned());


isUnsigned:
Assert::false($float->isUnsigned());


isFixed:
Assert::false($float->isFixed());


getResourceType:
Assert::null($float->getResourceType());


getItemType:
Assert::null($float->getItemType());


getSize:
Assert::null($float->getSize());
Assert::same($single->getSize(), 32);
Assert::null($floatNullable->getSize());


getEncoding:
Assert::null($float->getEncoding());


getLocale:
Assert::null($float->getLocale());


isBool:
Assert::false($float->isBool());


isInt:
Assert::false($float->isInt());


isFloat:
Assert::true($float->isFloat());


isNumeric:
Assert::true($float->isNumeric());


isString:
Assert::false($float->isString());


isScalar:
Assert::true($float->isScalar());


isArray:
Assert::false($float->isArray());


isCollection:
Assert::false($float->isCollection());


isTuple:
Assert::false($float->isTuple());


isClass:
Assert::false($float->isClass());


isCallable:
Assert::false($float->isCallable());


isResource:
Assert::false($float->isResource());


is:
Assert::true($float->is(Type::FLOAT));
Assert::false($float->is(DateTime::class));


isImplementing:
Assert::false($float->isImplementing(DateTime::class));


getBaseType:
Assert::same($floatNullable->getBaseType(), $float);
Assert::same($single->getBaseType(), $float);


getNonNullableType:
Assert::same($floatNullable->getNonNullableType(), $float);
Assert::same($single->getNonNullableType(), $single);


getTypeWithoutParams:
Assert::same($floatNullable->getTypeWithoutParams(), $floatNullable);
Assert::same($single->getTypeWithoutParams(), $float);


getInstance:
Assert::exception(static function () use ($float): void {
    $float->getInstance();
}, Error::class);
