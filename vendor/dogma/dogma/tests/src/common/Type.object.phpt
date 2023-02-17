<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use DateTime;
use DateTimeImmutable;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../bootstrap.php';

$datetime = Type::get(DateTime::class);
$datetimeNullable = Type::get(DateTime::class, Type::NULLABLE);


getId:
Assert::same($datetime->getId(), 'DateTime');
Assert::same($datetimeNullable->getId(), 'DateTime?');


fromId:
Assert::same(Type::fromId('DateTime'), $datetime);
Assert::same(Type::fromId('DateTime?'), $datetimeNullable);


getName:
Assert::same($datetime->getName(), DateTime::class);


isNullable:
Assert::false($datetime->isNullable());
Assert::true($datetimeNullable->isNullable());


isSigned:
Assert::false($datetime->isSigned());


isUnsigned:
Assert::false($datetime->isUnsigned());


isFixed:
Assert::false($datetime->isFixed());


getResourceType:
Assert::null($datetime->getResourceType());


getItemType:
Assert::null($datetime->getItemType());


getSize:
Assert::null($datetime->getSize());


getEncoding:
Assert::null($datetime->getEncoding());


getLocale:
Assert::null($datetime->getLocale());


isBool:
Assert::false($datetime->isBool());


isInt:
Assert::false($datetime->isInt());


isFloat:
Assert::false($datetime->isFloat());


isNumeric:
Assert::false($datetime->isNumeric());


isString:
Assert::false($datetime->isString());


isScalar:
Assert::false($datetime->isScalar());


isArray:
Assert::false($datetime->isArray());


isCollection:
Assert::false($datetime->isCollection());


isTuple:
Assert::false($datetime->isTuple());


isClass:
Assert::true($datetime->isClass());


isCallable:
Assert::false($datetime->isCallable());


isResource:
Assert::false($datetime->isResource());


is:
Assert::true($datetime->is(DateTime::class));
Assert::false($datetime->is(DateTimeImmutable::class));


isImplementing:
Assert::true($datetime->isImplementing(DateTime::class));
Assert::false($datetime->isImplementing(DateTimeImmutable::class));


getBaseType:
Assert::same($datetime->getBaseType(), $datetime);
Assert::same($datetimeNullable->getBaseType(), $datetime);


getNonNullableType:
Assert::same($datetime->getNonNullableType(), $datetime);
Assert::same($datetimeNullable->getNonNullableType(), $datetime);


getTypeWithoutParams:
Assert::same($datetime->getTypeWithoutParams(), $datetime);
Assert::same($datetimeNullable->getTypeWithoutParams(), $datetimeNullable);


getInstance:
Assert::equal($datetime->getInstance('2016-01-01 00:00:00'), new DateTime('2016-01-01 00:00:00'));
