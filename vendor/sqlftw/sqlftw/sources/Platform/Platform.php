<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Platform;

use LogicException;
use SqlFtw\Platform\Features\Feature;
use SqlFtw\Platform\Features\FeaturesList;
use SqlFtw\Platform\Features\MysqlFeatures;
use SqlFtw\Platform\Naming\NamingStrategy;
use SqlFtw\Sql\Command;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\BuiltInFunction;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\MysqlVariable;
use SqlFtw\Sql\SqlMode;
use function assert;
use function end;
use function explode;
use function in_array;
use function is_string;
use function ltrim;
use function strtoupper;
use function ucfirst;

class Platform
{

    public const SQL = 'sql';
    public const MYSQL = 'mysql';
    public const MARIA = 'maria';

    /** @var array<string, non-empty-list<string>> ($platform => $versions) */
    private static array $versions = [
        self::SQL => ['92', '99', '2003', '2008', '2011', '2016', '2019'],
        self::MYSQL => ['5.1', '5.5', '5.6', '5.7', '8.0'],
        self::MARIA => ['5.1', '5.2', '5.3', '5.5', '10.0', '10.1', '10.2', '10.3', '10.4', '10.5', '10.6', '10.7', '10.8'],
    ];

    /** @var array<string, string> ($platform => $version) */
    private static array $defaultVersions = [
        self::SQL => '2011',
        self::MYSQL => '8.0',
        self::MARIA => '10.8',
    ];

    /** @var array<string, list<string>> ($version => $modes) */
    public static array $defaultSqlModes = [
        'mysql-5.6' => [
            SqlMode::NO_ENGINE_SUBSTITUTION,
        ],
        'mysql-5.7' => [
            SqlMode::NO_ENGINE_SUBSTITUTION,
            SqlMode::ERROR_FOR_DIVISION_BY_ZERO,
            SqlMode::STRICT_TRANS_TABLES,
            SqlMode::ONLY_FULL_GROUP_BY,
            SqlMode::NO_ZERO_IN_DATE,
            SqlMode::NO_ZERO_DATE,
            SqlMode::NO_AUTO_CREATE_USER,
        ],
        'mysql-8.0' => [
            SqlMode::NO_ENGINE_SUBSTITUTION,
            SqlMode::ERROR_FOR_DIVISION_BY_ZERO,
            SqlMode::STRICT_TRANS_TABLES,
            SqlMode::ONLY_FULL_GROUP_BY,
            SqlMode::NO_ZERO_IN_DATE,
            SqlMode::NO_ZERO_DATE,
        ],
        'maria-10.1' => [
            SqlMode::NO_ENGINE_SUBSTITUTION,
            SqlMode::NO_AUTO_CREATE_USER,
        ],
        'maria-10.2' => [
            SqlMode::NO_ENGINE_SUBSTITUTION,
            SqlMode::ERROR_FOR_DIVISION_BY_ZERO,
            SqlMode::STRICT_TRANS_TABLES,
            SqlMode::NO_AUTO_CREATE_USER,
        ],
    ];

    /** @var array<string, self> ($version => $instance) */
    private static array $instances = [];

    /** @var self::* */
    private string $name;

    private Version $version;

    private FeaturesList $featuresList;

    /** @var list<Feature::*> */
    private ?array $features = null;

    /** @var list<Keyword::*> */
    private ?array $reserved = null;

    /** @var list<Keyword::*> */
    private ?array $nonReserved = null;

    /** @var list<Operator::*> */
    private ?array $operators = null;

    /** @var list<BaseType::*> */
    private ?array $types = null;

    /** @var list<BuiltInFunction::*> */
    private ?array $functions = null;

    /** @var list<MysqlVariable::*> */
    private ?array $variables = null;

    /** @var list<class-string<Command>> */
    private ?array $preparableCommands = null;

    /**
     * @param self::* $name
     */
    final private function __construct(string $name, Version $version)
    {
        $this->name = $name;
        $this->version = $version;
        switch ($name) {
            case self::MYSQL:
                $this->featuresList = new MysqlFeatures();
                break;
            default:
                throw new LogicException('Only MySQL platform is supported for now.');
        }
    }

    /**
     * @param self::* $name
     * @param int|string|null $version
     */
    public static function get(string $name, $version = null): self
    {
        if (!isset(self::$versions[$name])) {
            throw new LogicException("Unknown platform {$name}.");
        }
        $version = new Version($version ?? self::$defaultVersions[$name]);
        if (!in_array($version->getMajorMinor(), self::$versions[$name], true)) {
            throw new LogicException("Unknown version {$version->format()} of platform {$name}.");
        }

        $key = $name . $version->getId();
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($name, $version);
        }

        return self::$instances[$key];
    }

    /**
     * @param self::* $name
     */
    public static function fromTag(string $name, string $tag): self
    {
        $parts = explode('-', $tag);

        return self::get($name, end($parts));
    }

    /**
     * @return self::*
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-list<string>
     */
    public function getVersions(): array
    {
        return self::$versions[$this->name];
    }

    public function getFamilyId(): string
    {
        return $this->name . $this->version->getMajorMinor();
    }

    public function getDefaultVersion(): string
    {
        return self::$defaultVersions[$this->name];
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function setVersion(Version $version): void
    {
        $this->version = $version;
    }

    public function format(): string
    {
        return $this->name . ' ' . $this->version->format();
    }

    public function matches(?string $name, ?int $minVersion = null, ?int $maxVersion = null): bool
    {
        if ($name !== null && $this->name !== $name) {
            return false;
        }

        $thisId = $this->version->getId();

        if ($minVersion !== null && $thisId < $minVersion) {
            return false;
        } elseif ($maxVersion !== null && $thisId > $maxVersion) {
            return false;
        } else {
            return true;
        }
    }

    public function interpretOptionalComment(string $versionId): bool
    {
        $maria = $versionId !== '' && strtoupper($versionId[0]) === 'M';
        $versionId = (int) ltrim($versionId, 'Mm');

        if ($this->name !== self::MYSQL && $this->name !== self::MARIA) {
            // no support for optional comments
            return false;
        } elseif ($versionId === 0) {
            // MySQL and Maria only, no version limit
            return true;
        } elseif ($maria && $this->name !== self::MARIA) {
            // Maria only
            return false;
        } elseif (!$maria && $this->name === self::MARIA && $versionId >= 50700 && $this->version->getId() >= 100007) {
            // Starting from MariaDB 10.0.7, MariaDB ignores MySQL-style executable comments that have a version number in the range 50700..99999.
            return false;
        } elseif ($versionId >= $this->version->getId()) {
            // version mismatch
            return false;
        } else {
            return true;
        }
    }

    public function userDelimiter(): bool
    {
        return $this->name === self::MYSQL || $this->name === self::MARIA;
    }

    public function getDefaultMode(): SqlMode
    {
        if ($this->name === self::MYSQL || $this->name === self::MARIA) {
            $default = MysqlVariable::getDefault(MysqlVariable::SQL_MODE);
            assert(is_string($default));

            return SqlMode::getFromString($default);
        } else {
            return SqlMode::getFromString(SqlMode::ANSI);
        }
    }

    /**
     * @return list<string>
     */
    public function getDefaultModes(): array
    {
        $family = $this->getFamilyId();
        if (isset(self::$defaultSqlModes[$family])) {
            return self::$defaultSqlModes[$family];
        } elseif ($this->name === self::MARIA && $family >= 'maria-10.2') {
            return self::$defaultSqlModes['maria-10.2'];
        } else {
            return [];
        }
    }

    public function getNamingStrategy(): NamingStrategy
    {
        /** @var class-string<NamingStrategy> $class */
        $class = 'SqlFtw\\Platform\\Naming\\NamingStrategy' . ucfirst($this->name);

        return new $class();
    }

    // features --------------------------------------------------------------------------------------------------------

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->getFeatures(), true);
    }

    /**
     * @return list<Feature::*>
     */
    public function getFeatures(): array
    {
        if ($this->features === null) {
            /** @var list<Feature::*> $filtered */
            $filtered = $this->filterForVersion($this->featuresList->features, $this->version->getId());
            $this->features = $filtered;
        }

        return $this->features;
    }

    public function isReserved(string $word): bool
    {
        return in_array($word, $this->getReserved(), true);
    }

    /**
     * @return list<Keyword::*>
     */
    public function getReserved(): array
    {
        if ($this->reserved === null) {
            /** @var list<Keyword::*> $filtered */
            $filtered = $this->filterForVersion($this->featuresList->reserved, $this->version->getId());
            $this->reserved = $filtered;
        }

        return $this->reserved;
    }

    public function isKeyword(string $word, int $version): bool
    {
        return in_array($word, $this->getReserved(), true) || in_array($word, $this->getNonReserved(), true);
    }

    /**
     * @return list<Keyword::*>
     */
    public function getNonReserved(): array
    {
        if ($this->nonReserved === null) {
            /** @var list<Keyword::*> $filtered */
            $filtered = $this->filterForVersion($this->featuresList->nonReserved, $this->version->getId());
            $this->nonReserved = $filtered;
        }

        return $this->nonReserved;
    }

    /**
     * @return list<Operator::*>
     */
    public function getOperators(): array
    {
        if ($this->operators === null) {
            /** @var list<Operator::*> $filtered */
            $filtered = $this->filterForVersion($this->featuresList->operators, $this->version->getId());
            $this->operators = $filtered;
        }

        return $this->operators;
    }

    public function isType(string $word): bool
    {
        return in_array($word, $this->getTypes(), true);
    }

    /**
     * @return list<BaseType::*>
     */
    public function getTypes(): array
    {
        if ($this->types === null) {
            /** @var list<BaseType::*> $filtered */
            $filtered = $this->filterForVersion($this->featuresList->types, $this->version->getId());
            $this->types = $filtered;
        }

        return $this->types;
    }

    /**
     * @return list<BuiltInFunction::*>
     */
    public function getBuiltInFunctions(): array
    {
        if ($this->functions === null) {
            /** @var list<BuiltInFunction::*> $filtered */
            $filtered = $this->filterForVersion($this->featuresList->functions, $this->version->getId());
            $this->functions = $filtered;
        }

        return $this->functions;
    }

    /**
     * @return list<MysqlVariable::*>
     */
    public function getSystemVariables(): array
    {
        if ($this->variables === null) {
            /** @var list<MysqlVariable::*> $filtered */
            $filtered = $this->filterForVersion($this->featuresList->variables, $this->version->getId());
            $this->variables = $filtered;
        }

        return $this->variables;
    }

    /**
     * @return list<class-string<Command>>
     */
    public function getPreparableCommands(): array
    {
        if ($this->preparableCommands === null) {
            /** @var list<class-string<Command>> $filtered */
            $filtered = $this->filterForVersion($this->featuresList->preparableCommands, $this->version->getId());
            $this->preparableCommands = $filtered;
        }

        return $this->preparableCommands;
    }

    /**
     * @return array<EntityType::*, int>
     */
    public function getMaxLengths(): array
    {
        return $this->featuresList->maxLengths;
    }

    /**
     * @template T
     * @param list<array{T, int, int}> $values
     * @return list<T>
     */
    private function filterForVersion(array $values, int $version): array
    {
        $result = [];
        foreach ($values as [$value, $since, $until]) {
            if ($version >= $since && $version <= $until) {
                $result[] = $value;
            }
        }

        return $result;
    }

}
