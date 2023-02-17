<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use DateTime;
use Dogma\Country\Country;
use Dogma\Language\Encoding;
use Dogma\Language\Language;
use Dogma\Language\Locale\Locale;
use Dogma\Length;
use Dogma\Tester\Assert;
use Dogma\Type;
use Error;

require_once __DIR__ . '/../bootstrap.php';

$utf8 = Encoding::get(Encoding::UTF_8);
$czech = Locale::create(Language::get(Language::CZECH), Country::get(Country::CZECHIA));

$string = Type::string();
$stringLength = Type::string(32);
$stringFixed = Type::string(Length::FIXED);
$stringEncoding = Type::string($utf8);
$stringLocale = Type::string($czech);
$stringNullable = Type::string(Type::NULLABLE);
$stringAllParams = Type::string(32, Length::FIXED, $utf8, $czech, Type::NULLABLE);


getId:
Assert::same($string->getId(), 'string');
Assert::same($stringLength->getId(), 'string(32)');
Assert::same($stringFixed->getId(), 'string(fixed)');
Assert::same($stringEncoding->getId(), 'string(UTF-8)');
Assert::same($stringLocale->getId(), 'string(cs_CZ)');
Assert::same($stringNullable->getId(), 'string?');
Assert::same($stringAllParams->getId(), 'string(32,fixed,UTF-8,cs_CZ)?');


fromId:
Assert::same(Type::fromId('string'), $string);
Assert::same(Type::fromId('string(32)'), $stringLength);
Assert::same(Type::fromId('string(fixed)'), $stringFixed);
Assert::same(Type::fromId('string(f)'), $stringFixed);
Assert::same(Type::fromId('string(32f)'), Type::string(32, Length::FIXED));
Assert::same(Type::fromId('string(UTF-8)'), $stringEncoding);
Assert::same(Type::fromId('string(cs_CZ)'), $stringLocale);
Assert::same(Type::fromId('string?'), $stringNullable);
Assert::same(Type::fromId('string(32,fixed,UTF-8,cs_CZ)?'), $stringAllParams);


getName:
Assert::same($string->getName(), Type::STRING);


isNullable:
Assert::false($string->isNullable());
Assert::true($stringNullable->isNullable());


isSigned:
Assert::false($string->isSigned());


isUnsigned:
Assert::false($string->isUnsigned());


isFixed:
Assert::false($string->isFixed());
Assert::true($stringFixed->isFixed());
Assert::true($stringAllParams->isFixed());


getResourceType:
Assert::null($string->getResourceType());


getItemType:
Assert::null($string->getItemType());


getSize:
Assert::null($string->getSize());
Assert::null($stringFixed->getSize());
Assert::same($stringLength->getSize(), 32);
Assert::same($stringAllParams->getSize(), 32);


getEncoding:
Assert::null($string->getEncoding());
Assert::same($stringEncoding->getEncoding(), $utf8);


getLocale:
Assert::null($string->getLocale());
Assert::same($stringLocale->getLocale(), $czech);


isBool:
Assert::false($string->isBool());


isInt:
Assert::false($string->isInt());


isFloat:
Assert::false($string->isFloat());


isNumeric:
Assert::false($string->isNumeric());


isString:
Assert::true($string->isString());


isScalar:
Assert::true($string->isScalar());


isArray:
Assert::false($string->isArray());


isCollection:
Assert::false($string->isCollection());


isTuple:
Assert::false($string->isTuple());


isClass:
Assert::false($string->isClass());


isCallable:
Assert::false($string->isCallable());


isResource:
Assert::false($string->isResource());


is:
Assert::true($string->is(Type::STRING));
Assert::false($string->is(DateTime::class));


isImplementing:
Assert::false($string->isImplementing(DateTime::class));


getBaseType:
Assert::same($stringNullable->getBaseType(), $string);
Assert::same($stringAllParams->getBaseType(), $string);


getNonNullableType:
Assert::same($stringNullable->getNonNullableType(), $string);
Assert::same($stringAllParams->getNonNullableType(), Type::fromId('string(32,fixed,UTF-8,cs_CZ)'));


getTypeWithoutParams:
Assert::same($stringNullable->getTypeWithoutParams(), $stringNullable);
Assert::same($stringAllParams->getTypeWithoutParams(), $stringNullable);


getInstance:
Assert::exception(static function () use ($string): void {
    $string->getInstance();
}, Error::class);
