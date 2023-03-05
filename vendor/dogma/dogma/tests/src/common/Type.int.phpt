<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use DateTime;
use Dogma\Sign;
use Dogma\Tester\Assert;
use Dogma\Type;
use Error;

require_once __DIR__ . '/../bootstrap.php';

$int = Type::int();
$intNullable = Type::int(Type::NULLABLE);
$intSigned = Type::int(Sign::SIGNED);
$intUnsigned = Type::int(Sign::UNSIGNED);
$intUnsignedNullable = Type::int(Sign::UNSIGNED, Type::NULLABLE);
$intSize = Type::int(32);
$intSizeUnsignedNullable = Type::int(32, Sign::UNSIGNED, Type::NULLABLE);

Assert::same($int, $intSigned);


getId:
Assert::same($int->getId(), 'int');
Assert::same($intNullable->getId(), 'int?');
Assert::same($intSigned->getId(), 'int');
Assert::same($intUnsigned->getId(), 'int(unsigned)');
Assert::same($intUnsignedNullable->getId(), 'int(unsigned)?');
Assert::same($intSize->getId(), 'int(32)');
Assert::same($intSizeUnsignedNullable->getId(), 'int(32,unsigned)?');


fromId:
Assert::same(Type::fromId('int'), $int);
Assert::same(Type::fromId('int?'), $intNullable);
Assert::same(Type::fromId('int(unsigned)'), $intUnsigned);
Assert::same(Type::fromId('int(signed)'), $intSigned);
Assert::same(Type::fromId('int(u)'), $intUnsigned);
Assert::same(Type::fromId('int(s)'), $intSigned);
Assert::same(Type::fromId('int(unsigned)?'), $intUnsignedNullable);
Assert::same(Type::fromId('int(signed)?'), $intNullable);
Assert::same(Type::fromId('int(u)?'), $intUnsignedNullable);
Assert::same(Type::fromId('int(s)?'), $intNullable);
Assert::same(Type::fromId('int(32)'), $intSize);
Assert::same(Type::fromId('int(32,unsigned)?'), $intSizeUnsignedNullable);


getName:
Assert::same($int->getName(), Type::INT);


isNullable:
Assert::false($int->isNullable());
Assert::true($intNullable->isNullable());


isSigned:
Assert::true($int->isSigned());
Assert::false($intUnsigned->isSigned());


isUnsigned:
Assert::false($int->isUnsigned());
Assert::true($intUnsigned->isUnsigned());


isFixed:
Assert::false($int->isFixed());


getResourceType:
Assert::null($int->getResourceType());


getItemType:
Assert::null($int->getItemType());


getSize:
Assert::null($int->getSize());
Assert::same($intSize->getSize(), 32);


getEncoding:
Assert::null($int->getEncoding());


getLocale:
Assert::null($int->getLocale());


isBool:
Assert::false($int->isBool());


isInt:
Assert::true($int->isInt());


isFloat:
Assert::false($int->isFloat());


isNumeric:
Assert::true($int->isNumeric());


isString:
Assert::false($int->isString());


isScalar:
Assert::true($int->isScalar());


isArray:
Assert::false($int->isArray());


isCollection:
Assert::false($int->isCollection());


isTuple:
Assert::false($int->isTuple());


isClass:
Assert::false($int->isClass());


isCallable:
Assert::false($int->isCallable());


isResource:
Assert::false($int->isResource());


is:
Assert::true($int->is(Type::INT));
Assert::false($int->is(DateTime::class));


isImplementing:
Assert::false($int->isImplementing(DateTime::class));


getBaseType:
Assert::same($intNullable->getBaseType(), $int);


getNonNullableType:
Assert::same($intNullable->getNonNullableType(), $int);
Assert::same($intSizeUnsignedNullable->getNonNullableType(), Type::fromId('int(32u)'));


getTypeWithoutParams:
Assert::same($intNullable->getTypeWithoutParams(), $intNullable);
Assert::same($intSizeUnsignedNullable->getTypeWithoutParams(), $intNullable);


getInstance:
Assert::exception(static function () use ($int): void {
    $int->getInstance();
}, Error::class);
