<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: suf

namespace Dogma;

use Dogma\Language\Encoding;
use Dogma\Language\Locale\Locale;
use Throwable;
use function array_pop;
use function count;
use function end;
use function explode;
use function func_get_args;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function is_subclass_of;
use function preg_match;
use function str_replace;
use function strlen;

/**
 * Type metadata
 */
class Type
{
    use StrictBehaviorMixin;
    use NonCloneableMixin;
    use NonSerializableMixin;

    // types
    public const BOOL = 'bool';
    public const INT = 'int';
    public const FLOAT = 'float';
    public const STRING = 'string';
    public const PHP_ARRAY = 'array';
    public const OBJECT = 'object';
    public const PHP_CALLABLE = 'callable';
    public const RESOURCE = 'resource';

    // pseudotypes
    public const NULL = 'null';
    public const NUMBER = 'number';
    public const SCALAR = 'scalar';
    public const MIXED = 'mixed';
    public const VOID = 'void';

    // strict type checks flag
    public const STRICT = true;

    // nullable type flag
    public const NULLABLE = true;
    public const NOT_NULLABLE = false;

    /** @var Type[] (string $id => $type) */
    private static $instances = [];

    /** @var string */
    private $id;

    /** @var string */
    private $type;

    /** @var Type|Type[]|null */
    private $itemType;

    /** @var bool */
    private $nullable;

    /** @var int|int[]|null */
    private $size;

    /** @var string|null */
    private $specific;

    /** @var Encoding|null */
    private $encoding;

    /** @var Locale|null */
    private $locale;

    /**
     * @param Type|Type[]|null $itemType
     * @param int|int[]|null $size
     */
    final private function __construct(
        string $id,
        string $type,
        bool $nullable = false,
        $itemType = null,
        $size = null,
        ?string $specific = null,
        ?Encoding $encoding = null,
        ?Locale $locale = null
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->nullable = $nullable;
        $this->itemType = $itemType;
        $this->size = $size;
        $this->specific = $specific;
        $this->encoding = $encoding;
        $this->locale = $locale;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int|int[]|null $size [optional]
     * @param string|null $specific [optional]
     * @param Encoding|null $encoding [optional]
     * @param Locale|null $locale [optional]
     * @param bool $nullable [optional]
     * @return self
     */
    public static function get(
        string $type,
        $size = null,
        $specific = null,
        $encoding = null,
        $locale = null,
        ?bool $nullable = null
    ): self
    {
        $args = func_get_args();
        $size = $specific = $encoding = $locale = $nullable = null;
        foreach ($args as $i => $arg) {
            switch (true) {
                case $i < 1:
                    break;
                case $nullable === null && is_bool($arg):
                    $nullable = $arg;
                    break;
                case $size === null && (is_int($arg) || is_array($arg)):
                    $size = $arg;
                    if (is_int($size)) {
                        self::checkSize($type, $size);
                    }
                    break;
                case $specific === null && (is_string($arg) || $arg instanceof ResourceType):
                    $specific = $arg instanceof ResourceType ? $arg->getValue() : $arg;
                    self::checkSpecific($type, $specific);
                    if ($specific === Sign::SIGNED) {
                        $specific = null;
                    }
                    break;
                case $encoding === null && $arg instanceof Encoding:
                    $encoding = $arg;
                    break;
                case $locale === null && $arg instanceof Locale:
                    $locale = $arg;
                    break;
                case $arg === null:
                    continue 2;
                default:
                    $value = ExceptionValueFormatter::format($arg);
                    throw new InvalidArgumentException("Unexpected or duplicate argument $value at position $i.");
            }
        }

        if ($nullable === null) {
            $nullable = false;
        }

        // normalize "array" to "array<mixed>"
        if ($type === self::PHP_ARRAY) {
            return self::collectionOf(self::PHP_ARRAY, self::get(self::MIXED), $nullable);
        }

        $id = $type;
        if ($size !== null || $specific !== null || $encoding !== null || $locale !== null) {
            $id .= '(' . implode(',', Arr::flatten(Arr::filter(
                [$size, $specific, $encoding ? $encoding->getValue() : null, $locale ? $locale->getValue() : null]
            ))) . ')';
        }
        if ($nullable) {
            $id .= '?';
        }
        if (empty(self::$instances[$id])) {
            $that = new self($id, $type, $nullable, null, $size, $specific, $encoding, $locale);
            self::$instances[$id] = $that;
        }

        return self::$instances[$id];
    }

    private static function checkSize(string $type, int $size): void
    {
        if ($type === self::INT) {
            BitSize::checkIntSize($size);
            return;
        } elseif ($type === self::FLOAT) {
            BitSize::checkFloatSize($size);
            return;
        } elseif ($type === self::STRING && $size > 0) {
            return;
        }
        throw new InvalidSizeException($type, $size, []);
    }

    private static function checkSpecific(string $type, ?string $specific = null): void
    {
        if ($type === self::INT && ($specific === Sign::SIGNED || $specific === Sign::UNSIGNED)) {
            return;
        }
        if ($type === self::STRING && $specific === Length::FIXED) {
            return;
        }
        if ($type === self::RESOURCE && ResourceType::isValid((string) $specific)) {
            return;
        }
        throw new InvalidTypeException($type, "$type($specific)");
    }

    public static function bool(bool $nullable = false): self
    {
        return self::get(self::BOOL, $nullable);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int|null $size
     * @param string|null $sign
     * @return self
     */
    public static function int($size = null, $sign = null, ?bool $nullable = null): self
    {
        return self::get(self::INT, $size, $sign, $nullable);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int|null $size
     * @return self
     */
    public static function uint($size = null, ?bool $nullable = null): self
    {
        return self::get(self::INT, $size, Sign::UNSIGNED, $nullable);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int|null $size
     * @return self
     */
    public static function float($size = null, ?bool $nullable = null): self
    {
        return self::get(self::FLOAT, $size, $nullable);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int|null $size
     * @param string|null $fixed
     * @param Encoding|null $encoding
     * @param Locale|null $locale
     * @return self
     */
    public static function string($size = null, $fixed = null, $encoding = null, $locale = null, ?bool $nullable = null): self
    {
        return self::get(self::STRING, $size, $fixed, $encoding, $locale, $nullable);
    }

    public static function callable(?bool $nullable = null): self
    {
        return self::get(self::PHP_CALLABLE, $nullable);
    }

    /**
     * @param ResourceType|string|null $resourceType
     * @return self
     */
    public static function resource($resourceType = null, ?bool $nullable = null): self
    {
        return self::get(self::RESOURCE, $resourceType, $nullable);
    }

    /**
     * @param string|self $itemType
     * @return self
     */
    public static function arrayOf($itemType, bool $nullable = false): self
    {
        return self::collectionOf(self::PHP_ARRAY, $itemType, $nullable);
    }

    /**
     * @param string|self $itemType
     * @return self
     */
    public static function collectionOf(string $type, $itemType, bool $nullable = false): self
    {
        Check::types($itemType, [self::STRING, self::class]);

        if (!$itemType instanceof self) {
            $itemType = self::get($itemType);
        }

        $id = $type . '<' . $itemType->getId() . '>' . ($nullable ? '?' : '');
        if (empty(self::$instances[$id])) {
            $that = new self($id, $type, $nullable, $itemType);
            self::$instances[$id] = $that;
        }

        return self::$instances[$id];
    }

    /**
     * @param string|self|bool ...$itemTypes
     * @return self
     */
    public static function tupleOf(...$itemTypes): self
    {
        $nullable = false;
        if (is_bool(end($itemTypes))) {
            /** @var bool $nullable */
            $nullable = array_pop($itemTypes);
        }

        Check::itemsOfTypes($itemTypes, [self::STRING, self::class]);

        $itemIds = [];
        foreach ($itemTypes as &$type) {
            if (!$type instanceof self) {
                $itemIds[] = $type;
                $type = self::get($type);
            } else {
                $itemIds[] = $type->getId();
            }
        }

        /** @var self[] $itemTypes */
        $itemTypes = $itemTypes;

        $id = Tuple::class . '<' . implode(',', $itemIds) . '>' . ($nullable ? '?' : '');
        if (empty(self::$instances[$id])) {
            $that = new self($id, Tuple::class, $nullable, $itemTypes);
            self::$instances[$id] = $that;
        }

        return self::$instances[$id];
    }

    /**
     * Converts string in syntax like "Foo<Bar,Baz<int>>" to a Type instance
     * @return self
     */
    public static function fromId(string $id): self
    {
        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        }

        if (!preg_match('/^([^(<?]+)(?:\\(([^)]+)\\))?(?:<(.*)>)?(\\?)?$/', $id, $match)) {
            throw new InvalidTypeDefinitionException($id);
        }
        $match = Arr::padTo($match, 5, false);
        [, $baseId, $params, $itemIds, $nullable] = $match;
        $nullable = (bool) $nullable;

        $size = $specific = $encoding = $locale = null;
        if ($params) {
            foreach (explode(',', $params) as $param) {
                switch (true) {
                    case $size === null && preg_match('/([0-9]+)?([suf])?/', $param, $match):
                        $size = (int) $match[1];
                        if ($size) {
                            self::checkSize($baseId, $size);
                        } else {
                            $size = null;
                        }
                        if (isset($match[2])) {
                            $specific = ['s' => Sign::SIGNED, 'u' => Sign::UNSIGNED, 'f' => Length::FIXED][$match[2]];
                        }
                        break;
                    case $specific === null && ($param === Sign::SIGNED || $param === Sign::UNSIGNED || $param === Length::FIXED || ResourceType::isValid($param)):
                        $specific = $param;
                        break;
                    case $encoding === null && preg_match('/^' . Encoding::getValueRegexp() . '$/', $param):
                        $encoding = Encoding::get($param);
                        break;
                    case $locale === null && preg_match('/^' . Locale::getValueRegexp() . '$/', $param):
                        $locale = Locale::get($param);
                        break;
                    default:
                        throw new InvalidTypeDefinitionException($id);
                }
            }
            if ($specific) {
                self::checkSpecific($baseId, $specific);
            }
        }

        if (!$itemIds) {
            return self::get($baseId, $size, $specific, $encoding, $locale, $nullable);
        }

        $itemIds = Str::split($itemIds, '/(?<![0-9]),/');
        $last = 0;
        $counter = 0;
        foreach ($itemIds as $i => $type) {
            $carry = strlen($type) - strlen(str_replace(['<', '>'], ['', '  '], $type));
            if ($counter === 0 && $carry > 0) {
                $last = $i;
            } elseif ($counter > 0) {
                unset($itemIds[$i]);
                $itemIds[$last] .= ',' . $type;
            }
            $counter += $carry;
        }

        $itemTypes = [];
        foreach ($itemIds as $itemId) {
            $itemTypes[] = self::fromId($itemId);
        }

        if ($baseId === Tuple::class) {
            if ($nullable) {
                $itemTypes[] = $nullable;
            }
            return self::tupleOf(...$itemTypes);
        } else {
            if (count($itemTypes) !== 1) {
                throw new InvalidTypeDefinitionException($id);
            }
            if ($baseId === self::PHP_ARRAY) {
                return self::arrayOf($itemTypes[0], $nullable);
            } else {
                return self::collectionOf($baseId, $itemTypes[0], $nullable);
            }
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string|class-string
     */
    public function getName(): string
    {
        return $this->type;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isSigned(): bool
    {
        return $this->type === self::INT && $this->specific === null;
    }

    public function isUnsigned(): bool
    {
        return $this->specific === Sign::UNSIGNED;
    }

    public function isFixed(): bool
    {
        return $this->specific === Length::FIXED;
    }

    public function getResourceType(): ?ResourceType
    {
        return $this->type === self::RESOURCE && $this->specific ? ResourceType::get($this->specific) : null;
    }

    /**
     * Returns type of items or array of types for Tuple
     * @return self|self[]|null
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * Returns bit-size for numeric types and length for string
     *
     * @return int|int[]|null
     */
    public function getSize()
    {
        return $this->size;
    }

    public function getEncoding(): ?Encoding
    {
        return $this->encoding;
    }

    public function getLocale(): ?Locale
    {
        return $this->locale;
    }

    public function isBool(): bool
    {
        return $this->type === self::BOOL;
    }

    public function isInt(): bool
    {
        return $this->type === self::INT;
    }

    public function isFloat(): bool
    {
        return $this->type === self::FLOAT;
    }

    public function isNumeric(): bool
    {
        return $this->type === self::INT || $this->type === self::FLOAT || $this->type === self::NUMBER;
    }

    public function isString(): bool
    {
        return $this->type === self::STRING;
    }

    public function isScalar(): bool
    {
        return in_array($this->type, self::listScalarTypes(), true);
    }

    public function isArray(): bool
    {
        return $this->type === self::PHP_ARRAY;
    }

    public function isCollection(): bool
    {
        return $this->itemType && $this->type !== self::PHP_ARRAY && $this->type !== Tuple::class;
    }

    public function isTuple(): bool
    {
        return $this->type === Tuple::class;
    }

    public function isClass(): bool
    {
        return !in_array($this->type, self::listTypes(), true);
    }

    public function isCallable(): bool
    {
        return $this->type === self::PHP_CALLABLE;
    }

    public function isResource(): bool
    {
        return $this->type === self::RESOURCE;
    }

    public function is(string $typeName): bool
    {
        return $this->type === $typeName;
    }

    public function isImplementing(string $interfaceName): bool
    {
        return $this->type === $interfaceName || is_subclass_of($this->type, $interfaceName);
    }

    /**
     * Returns base of the type (without nullable, items and parameters)
     */
    public function getBaseType(): self
    {
        return self::get($this->type);
    }

    /**
     * Returns non-nullable version of self
     */
    public function getNonNullableType(): self
    {
        switch (true) {
            case !$this->nullable:
                return $this;
            case $this->isArray():
            case $this->isCollection():
                /** @var self $itemType */
                $itemType = $this->itemType;

                return self::collectionOf($this->type, $itemType);
            case $this->isTuple():
                /** @var mixed[] $itemType */
                $itemType = $this->itemType;

                return self::tupleOf(...$itemType);
            default:
                return self::get($this->type, $this->size, $this->specific, $this->encoding, $this->locale);
        }
    }

    /**
     * Returns type without size, sign, encoding etc.
     */
    public function getTypeWithoutParams(): self
    {
        switch (true) {
            case $this->isArray():
            case $this->isCollection():
                /** @var self $itemType */
                $itemType = $this->itemType;

                return self::collectionOf($this->type, $itemType->getTypeWithoutParams(), $this->nullable);
            case $this->isTuple():
                /** @var self[] $itemType */
                $itemType = $this->itemType;

                $itemTypes = [];
                foreach ($itemType as $itemType) {
                    $itemTypes[] = $itemType->getTypeWithoutParams();
                }
                if ($this->nullable) {
                    $itemTypes[] = $this->nullable;
                }
                return self::tupleOf(...$itemTypes);
            default:
                return self::get($this->type, $this->nullable);
        }
    }

    /**
     * Returns new instance of type. Works only on simple class types with public constructor.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     * @param mixed ...$arguments
     * @return object
     */
    public function getInstance(...$arguments)
    {
        $className = $this->type;

        return new $className(...$arguments);
    }

    /**
     * List of types and pseudotypes, that can be used in annotations. Does not include 'null' and 'void'
     * @return string[]
     */
    public static function listTypes(): array
    {
        return [
            self::BOOL,
            self::INT,
            self::FLOAT,
            self::NUMBER,
            self::STRING,
            self::SCALAR,
            self::MIXED,
            self::PHP_ARRAY,
            self::OBJECT,
            self::PHP_CALLABLE,
            self::RESOURCE,
        ];
    }

    /**
     * List of native PHP types. Does not include 'null'.
     * @return string[]
     */
    public static function listNativeTypes(): array
    {
        return [
            self::BOOL,
            self::INT,
            self::FLOAT,
            self::STRING,
            self::PHP_ARRAY,
            self::OBJECT,
            self::PHP_CALLABLE,
            self::RESOURCE,
        ];
    }

    /**
     * List of native PHP scalar types and pseudotype 'numeric'.
     * @return string[]
     */
    public static function listScalarTypes(): array
    {
        return [
            self::BOOL,
            self::INT,
            self::FLOAT,
            self::NUMBER,
            self::STRING,
        ];
    }

    public static function isType(string $type): bool
    {
        try {
            self::fromId($type);
            return true;
        } catch (Throwable $t) {
            return false;
        }
    }

    /**
     * @return self[]
     */
    public static function getDefinedTypes(): array
    {
        return self::$instances;
    }

}
