<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use DateTime;
use Dogma\Tester\Assert;
use Dogma\Type;
use Error;

require_once __DIR__ . '/../bootstrap.php';

$bool = Type::bool();
$boolNullable = Type::bool(Type::NULLABLE);


getId:
Assert::same($bool->getId(), 'bool');
Assert::same($boolNullable->getId(), 'bool?');


fromId:
Assert::same(Type::fromId('bool'), $bool);
Assert::same(Type::fromId('bool?'), $boolNullable);


getName:
Assert::same($bool->getName(), Type::BOOL);


isNullable:
Assert::false($bool->isNullable());
Assert::true($boolNullable->isNullable());


isSigned:
Assert::false($bool->isSigned());


isUnsigned:
Assert::false($bool->isUnsigned());


isFixed:
Assert::false($bool->isFixed());


getResourceType:
Assert::null($bool->getResourceType());


getItemType:
Assert::null($bool->getItemType());


getSize:
Assert::null($bool->getSize());


getEncoding:
Assert::null($bool->getEncoding());


getLocale:
Assert::null($bool->getLocale());


isBool:
Assert::true($bool->isBool());


isInt:
Assert::false($bool->isInt());


isFloat:
Assert::false($bool->isFloat());


isNumeric:
Assert::false($bool->isNumeric());


isString:
Assert::false($bool->isString());


isScalar:
Assert::true($bool->isScalar());


isArray:
Assert::false($bool->isArray());


isCollection:
Assert::false($bool->isCollection());


isTuple:
Assert::false($bool->isTuple());


isClass:
Assert::false($bool->isClass());


isCallable:
Assert::false($bool->isCallable());


isResource:
Assert::false($bool->isResource());


is:
Assert::true($bool->is(Type::BOOL));
Assert::false($bool->is(DateTime::class));


isImplementing:
Assert::false($bool->isImplementing(DateTime::class));


getBaseType:
Assert::same($boolNullable->getBaseType(), $bool);


getNonNullableType:
Assert::same($boolNullable->getNonNullableType(), $bool);


getTypeWithoutParams:
Assert::same($boolNullable->getTypeWithoutParams(), $boolNullable);


getInstance:
Assert::exception(static function () use ($bool): void {
    $bool->getInstance();
}, Error::class);
