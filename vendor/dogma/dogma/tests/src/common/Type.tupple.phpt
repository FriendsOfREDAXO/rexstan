<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use DateTime;
use Dogma\Tester\Assert;
use Dogma\Tuple;
use Dogma\Type;

require_once __DIR__ . '/../bootstrap.php';

$tuple = Type::tupleOf(Type::INT, Type::STRING);
$tupleNullable = Type::tupleOf(Type::INT, Type::STRING, Type::NULLABLE);


getId:
Assert::same($tuple->getId(), 'Dogma\\Tuple<int,string>');
Assert::same($tupleNullable->getId(), 'Dogma\\Tuple<int,string>?');


fromId:
Assert::same(Type::fromId('Dogma\\Tuple<int,string>'), $tuple);
Assert::same(Type::fromId('Dogma\\Tuple<int,string>?'), $tupleNullable);


getName:
Assert::same($tuple->getName(), Tuple::class);


isNullable:
Assert::false($tuple->isNullable());
Assert::true($tupleNullable->isNullable());


isSigned:
Assert::false($tuple->isSigned());


isUnsigned:
Assert::false($tuple->isUnsigned());


isFixed:
Assert::false($tuple->isFixed());


getResourceType:
Assert::null($tuple->getResourceType());


getItemType:
Assert::same($tuple->getItemType(), [Type::int(), Type::string()]);


getSize:
Assert::null($tuple->getSize());


getEncoding:
Assert::null($tuple->getEncoding());


getLocale:
Assert::null($tuple->getLocale());


isBool:
Assert::false($tuple->isBool());


isInt:
Assert::false($tuple->isInt());


isFloat:
Assert::false($tuple->isFloat());


isNumeric:
Assert::false($tuple->isNumeric());


isString:
Assert::false($tuple->isString());


isScalar:
Assert::false($tuple->isScalar());


isArray:
Assert::false($tuple->isArray());


isCollection:
Assert::false($tuple->isCollection());


isTuple:
Assert::true($tuple->isTuple());


isClass:
Assert::true($tuple->isClass());


isCallable:
Assert::false($tuple->isCallable());


isResource:
Assert::false($tuple->isResource());


is:
Assert::true($tuple->is(Tuple::class));
Assert::false($tuple->is(DateTime::class));


isImplementing:
Assert::true($tuple->isImplementing(Tuple::class));
Assert::false($tuple->isImplementing(DateTime::class));


getBaseType:
Assert::same($tuple->getBaseType(), Type::get(Tuple::class));
Assert::same($tupleNullable->getBaseType(), Type::get(Tuple::class));


getNonNullableType:
Assert::same($tuple->getNonNullableType(), $tuple);
Assert::same($tupleNullable->getNonNullableType(), $tuple);


getTypeWithoutParams:
Assert::same($tuple->getTypeWithoutParams(), $tuple);
Assert::same($tupleNullable->getTypeWithoutParams(), $tupleNullable);


getInstance:
Assert::equal($tuple->getInstance(1, 'abc'), new Tuple(1, 'abc'));
