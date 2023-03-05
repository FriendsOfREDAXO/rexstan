<?php declare(strict_types = 1);

// phpcs:disable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration

namespace Dogma\Tests\Enum;

use Dogma\Call;
use Dogma\Country\Country;
use Dogma\Country\CountryEu;
use Dogma\Country\CountryEurope;
use Dogma\Enum\Enum;
use Dogma\Http\HttpHeader;
use Dogma\Http\HttpMethod;
use Dogma\Http\HttpOrCurlStatus;
use Dogma\Http\HttpResponseStatus;
use Dogma\Io\ContentType\BaseContentType;
use Dogma\Io\ContentType\ContentType;
use Dogma\Io\FilePosition;
use Dogma\Io\LineEndings;
use Dogma\Io\LockType;
use Dogma\Language\Encoding;
use Dogma\Language\Language;
use Dogma\Language\Locale\LocaleCalendar;
use Dogma\Language\Locale\LocaleColAlternate;
use Dogma\Language\Locale\LocaleColBackwards;
use Dogma\Language\Locale\LocaleColCaseFirst;
use Dogma\Language\Locale\LocaleColCaseLevel;
use Dogma\Language\Locale\LocaleColHiraganaQuaternary;
use Dogma\Language\Locale\LocaleCollation;
use Dogma\Language\Locale\LocaleColNormalization;
use Dogma\Language\Locale\LocaleColNumeric;
use Dogma\Language\Locale\LocaleColStrength;
use Dogma\Language\Locale\LocaleKeyword;
use Dogma\Language\Locale\LocaleNumbers;
use Dogma\Language\Locale\LocaleVariant;
use Dogma\Language\Script;
use Dogma\Language\UnicodeCharacterCategory;
use Dogma\Money\Currency;
use Dogma\ResourceType;
use Dogma\System\Error\LinuxError;
use Dogma\System\Error\UnixError;
use Dogma\System\Error\WindowsError;
use Dogma\System\Os;
use Dogma\System\Port;
use Dogma\System\Sapi;
use Dogma\Tester\Assert;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\DayOfWeek;
use Dogma\Time\Month;
use Dogma\Time\TimeZone;
use Dogma\Web\Tld;
use Dogma\Web\UriScheme;

require_once __DIR__ . '/../bootstrap.php';

$enums = [
    ResourceType::class,
    Country::class,
    CountryEu::class,
    CountryEurope::class,
    HttpHeader::class,
    HttpMethod::class,
    HttpOrCurlStatus::class,
    HttpResponseStatus::class,
    BaseContentType::class,
    ContentType::class,
    FilePosition::class,
    LineEndings::class,
    LockType::class,
    Encoding::class,
    Language::class,
    Script::class,
    UnicodeCharacterCategory::class,
    LocaleCalendar::class,
    LocaleColAlternate::class,
    LocaleColBackwards::class,
    LocaleColCaseFirst::class,
    LocaleColCaseLevel::class,
    LocaleColHiraganaQuaternary::class,
    LocaleCollation::class,
    LocaleColNormalization::class,
    LocaleColNumeric::class,
    LocaleColStrength::class,
    LocaleKeyword::class,
    LocaleNumbers::class,
    LocaleVariant::class,
    Currency::class,
    Os::class,
    Sapi::class,
    LinuxError::class,
    UnixError::class,
    WindowsError::class,
    Port::class,
    DateTimeUnit::class,
    DayOfWeek::class,
    Month::class,
    TimeZone::class,
    Tld::class,
    UriScheme::class,
];

// just load everything
Call::with(static function (string $class): void {
    /** @var Enum $class */
    $values = $class::getAllowedValues();
    Assert::notSame($values, []);
}, $enums);
