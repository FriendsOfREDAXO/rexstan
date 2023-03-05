<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language\Locale;

use Dogma\Arr;
use Dogma\Check;
use Dogma\Country\Country;
use Dogma\InvalidArgumentException;
use Dogma\Language\Collator;
use Dogma\Language\Language;
use Dogma\Language\Script;
use Dogma\Money\Currency;
use Dogma\Str;
use Dogma\StrictBehaviorMixin;
use Dogma\Type;
use Locale as PhpLocale;
use function array_filter;
use function array_values;
use function implode;
use function is_string;
use function preg_match;
use function reset;
use function sprintf;

class Locale
{
    use StrictBehaviorMixin;

    /** @var self[] */
    private static $instances = [];

    /** @var string */
    private $value;

    /** @var string[]|string[][]|null[] */
    private $components;

    /**
     * @param string[]|string[][]|null[] $components
     */
    final private function __construct(string $value, array $components)
    {
        $this->value = $value;
        $this->components = $components;
    }

    public static function get(string $value): self
    {
        $value = PhpLocale::canonicalize($value);

        if (isset(self::$instances[$value])) {
            return self::$instances[$value];
        } else {
            $components = PhpLocale::parseLocale($value);
            $keywords = PhpLocale::getKeywords($value);
            if ($keywords) {
                $components['keywords'] = $keywords;
            }
            $instance = new self($value, $components);
            self::$instances[$value] = $instance;
            return $instance;
        }
    }

    public static function getDefault(): self
    {
        return self::get(PhpLocale::getDefault());
    }

    /**
     * @param string[] $variants
     * @param string[] $private
     * @param string[] $keywords
     * @return self
     */
    public static function create(
        Language $language,
        ?Country $country = null,
        ?Script $script = null,
        array $variants = [],
        array $private = [],
        array $keywords = []
    ): self
    {
        $components = [
            'language' => $language->getValue(),
            'region' => $country ? $country->getValue() : null,
            'script' => $script ? $script->getValue() : null,
        ];
        foreach (array_values($variants) as $n => $value) {
            $components['variant' . $n] = $value;
        }
        foreach (array_values($private) as $n => $value) {
            $components['private' . $n] = $value;
        }

        /** @var string|false $value */
        $value = PhpLocale::composeLocale(array_filter($components));
        if ($value === false) {
            throw new InvalidArgumentException('Invalid locale components.');
        }
        if ($keywords) {
            $value .= '@' . implode(';', Arr::mapPairs($keywords, static function (string $key, string $value) {
                return $key . '=' . $value;
            }));
        }
        $value = PhpLocale::canonicalize($value);

        if (isset(self::$instances[$value])) {
            return self::$instances[$value];
        } else {
            $components['keywords'] = $keywords;
            $instance = new self($value, $components);
            self::$instances[$value] = $instance;
            return $instance;
        }
    }

    public function getCollator(): Collator
    {
        return new Collator($this);
    }

    /**
     * @param string|Locale $locale
     * @return bool
     */
    public function matches($locale): bool
    {
        Check::types($locale, [Type::STRING, self::class]);

        if (is_string($locale)) {
            $locale = self::get($locale);
        }

        return PhpLocale::filterMatches($this->value, $locale->getValue(), false);
    }

    /**
     * @param Locale[]|string[] $locales
     * @param Locale|string $default
     * @return self|null
     */
    public function findBestMatch(array $locales, $default = null): ?self
    {
        Check::types($default, [Type::STRING, self::class, Type::NULL]);

        if ($default === null) {
            // work around bug when lookup does not work at all without default value
            $default = reset($locales);
        }
        if (is_string($default)) {
            $default = self::get($default);
        }

        /** @var Locale|string $locale */
        foreach ($locales as $i => $locale) {
            Check::types($locale, [Type::STRING, self::class]);
            if (is_string($locale)) {
                $locales[$i] = PhpLocale::canonicalize($locale);
            } else {
                $locale[$i] = $locale->getValue();
            }
        }

        $match = PhpLocale::lookup($locales, $this->value, false, $default->getValue());

        return $match ? self::get($match) : null;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLanguage(): Language
    {
        /** @var string $language */
        $language = $this->components['language'];

        return Language::get($language);
    }

    public function getScript(): ?Script
    {
        if (empty($this->components['script'])) {
            return null;
        }
        /** @var string $script */
        $script = $this->components['script'];

        return Script::get($script);
    }

    public function getCountry(): ?Country
    {
        if (empty($this->components['region'])) {
            return null;
        }
        /** @var string $country */
        $country = $this->components['region'];

        return Country::get($country);
    }

    /**
     * @return string[]
     */
    public function getVariants(): array
    {
        return PhpLocale::getAllVariants($this->value);
    }

    public function getVariant(int $n): ?string
    {
        $key = 'variant' . $n;
        if (empty($this->components[$key])) {
            return null;
        }
        /** @var string $variant */
        $variant = $this->components[$key];

        return $variant;
    }

    public function hasVariant(string $variant): bool
    {
        return Arr::contains($this->getVariants(), $variant);
    }

    /**
     * @return string[]
     */
    public function getPrivates(): array
    {
        $privates = [];
        /** @var string $component */
        foreach ($this->components as $key => $component) {
            if (preg_match('/^private\\d+$/', $key)) {
                // work around bug, when last private variant is returned with keywords
                $privates[] = Str::toFirst($component, '@');
            }
        }

        return $privates;
    }

    public function getPrivate(int $n): ?string
    {
        $key = 'private' . $n;
        if (empty($this->components[$key])) {
            return null;
        }
        /** @var string $private */
        $private = $this->components[$key];

        // work around bug, when last private variant is returned with keywords
        return Str::toFirst($private, '@');
    }

    /**
     * @return string[]
     */
    public function getKeywords(): array
    {
        /** @var string[] $keywords */
        $keywords = $this->components['keywords'] ?? [];

        return $keywords;
    }

    public function getKeyword(string $keyword): ?string
    {
        /** @var string[] $keywords */
        $keywords = $this->components['keywords'];

        return $keywords[$keyword] ?? null;
    }

    public function getCurrency(): ?Currency
    {
        $value = $this->getKeyword(LocaleKeyword::CURRENCY);

        return $value ? Currency::get($value) : null;
    }

    public function getNumbers(): ?LocaleNumbers
    {
        $value = $this->getKeyword(LocaleKeyword::NUMBERS);

        return $value ? LocaleNumbers::get($value) : null;
    }

    public function getCalendar(): ?LocaleCalendar
    {
        $value = $this->getKeyword(LocaleKeyword::CALENDAR);

        return $value ? LocaleCalendar::get($value) : null;
    }

    public function getCollation(): ?LocaleCollation
    {
        $value = $this->getKeyword(LocaleKeyword::COLLATION);

        return $value ? LocaleCollation::get($value) : null;
    }

    /**
     * @return LocaleCollationOption[]
     */
    public function getCollationOptions(): array
    {
        $options = [];
        /** @var LocaleCollationOption $class */
        foreach (LocaleKeyword::getCollationOptions() as $keyword => $class) {
            $value = $this->getKeyword($keyword);
            if ($value !== null) {
                $options[$keyword] = $class::get($value);
            }
        }
        /** @var LocaleCollationOption[] $options */
        $options = $options;

        return $options;
    }

    public function removeCollation(): self
    {
        $keywords = Arr::diffKeys($this->getKeywords(), LocaleKeyword::getCollationOptions());
        unset($keywords[LocaleKeyword::COLLATION]);

        return self::create(
            $this->getLanguage(),
            $this->getCountry(),
            $this->getScript(),
            $this->getVariants(),
            $this->getPrivates(),
            $keywords
        );
    }

    public static function getValueRegexp(): string
    {
        static $regexp;

        if (!$regexp) {
            // language_Script_COUNTRY_VARIANT_VARIANT...@keyword=value;keyword=value...
            $regexp = sprintf(
                '%s(?:_(?:%s))?(?:_(?:%s))?(?:_(?:%s))*(?:@(?:%s)=[a-zA-Z0-9-](?:;(?:%s)=[a-zA-Z0-9-])*)?',
                Language::getValueRegexp(),
                Script::getValueRegexp(),
                Country::getValueRegexp(),
                LocaleVariant::getValueRegexp(),
                LocaleKeyword::getValueRegexp(),
                LocaleKeyword::getValueRegexp()
            );
        }

        return $regexp;
    }

}
