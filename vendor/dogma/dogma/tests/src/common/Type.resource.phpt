<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use DateTime;
use Dogma\ResourceType;
use Dogma\Tester\Assert;
use Dogma\Type;
use Error;

require_once __DIR__ . '/../bootstrap.php';

$aspell = ResourceType::get(ResourceType::ASPELL);

$resource = Type::resource();
$resourceAspell = Type::resource($aspell);
$resourceNullable = Type::resource(Type::NULLABLE);


getId:
Assert::same($resource->getId(), 'resource');
Assert::same($resourceAspell->getId(), 'resource(aspell)');
Assert::same($resourceNullable->getId(), 'resource?');


fromId:
Assert::same(Type::fromId('resource'), $resource);
Assert::same(Type::fromId('resource(aspell)'), $resourceAspell);
Assert::same(Type::fromId('resource?'), $resourceNullable);


getName:
Assert::same($resource->getName(), Type::RESOURCE);


isNullable:
Assert::false($resource->isNullable());
Assert::true($resourceNullable->isNullable());


isSigned:
Assert::false($resource->isSigned());


isUnsigned:
Assert::false($resource->isUnsigned());


isFixed:
Assert::false($resource->isFixed());


getResourceType:
Assert::null($resource->getResourceType());
Assert::equal($resourceAspell->getResourceType(), $aspell);


getItemType:
Assert::null($resource->getItemType());


getSize:
Assert::null($resource->getSize());


getEncoding:
Assert::null($resource->getEncoding());


getLocale:
Assert::null($resource->getLocale());


isBool:
Assert::false($resource->isBool());


isInt:
Assert::false($resource->isInt());


isFloat:
Assert::false($resource->isFloat());


isNumeric:
Assert::false($resource->isNumeric());


isString:
Assert::false($resource->isString());


isScalar:
Assert::false($resource->isScalar());


isArray:
Assert::false($resource->isArray());


isCollection:
Assert::false($resource->isCollection());


isTuple:
Assert::false($resource->isTuple());


isClass:
Assert::false($resource->isClass());


isCallable:
Assert::false($resource->isCallable());


isResource:
Assert::true($resource->isResource());


is:
Assert::true($resource->is(Type::RESOURCE));
Assert::false($resource->is(DateTime::class));


isImplementing:
Assert::false($resource->isImplementing(DateTime::class));


getBaseType:
Assert::same($resourceNullable->getBaseType(), $resource);
Assert::same($resourceAspell->getBaseType(), $resource);


getNonNullableType:
Assert::same($resourceNullable->getNonNullableType(), $resource);
Assert::same($resourceAspell->getNonNullableType(), $resourceAspell);


getTypeWithoutParams:
Assert::same($resourceNullable->getTypeWithoutParams(), $resourceNullable);
Assert::same($resourceAspell->getTypeWithoutParams(), $resource);


getInstance:
Assert::exception(static function () use ($resource): void {
    $resource->getInstance('abc');
}, Error::class);
