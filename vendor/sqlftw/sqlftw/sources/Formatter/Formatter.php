<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Formatter;

use DateTimeInterface;
use Dogma\Arr;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\Time;
use LogicException;
use SqlFtw\Session\Session;
use SqlFtw\Sql\Dml\Utility\DelimiterCommand;
use SqlFtw\Sql\Expression\AllLiteral;
use SqlFtw\Sql\Expression\Literal;
use SqlFtw\Sql\Expression\PrimaryLiteral;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlMode;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\Statement;
use function array_keys;
use function array_map;
use function array_values;
use function get_class;
use function gettype;
use function implode;
use function is_numeric;
use function is_object;
use function is_string;
use function ltrim;
use function preg_match;
use function str_replace;
use function strpos;

class Formatter
{

    private const MYSQL_ESCAPES = [
        '\\' => '\\\\',
        "\x00" => '\0',
        "\x08" => '\b',
        "\n" => '\n', // 0a
        "\r" => '\r', // 0d
        "\t" => '\t', // 09
        "\x1a" => '\Z', // 1a (legacy Win EOF)
    ];

    private Session $session;

    public string $indent;

    public bool $comments;

    public bool $quoteAllNames;

    public bool $escapeWhitespace;

    /** @var list<string> */
    private array $escapeKeys;

    /** @var list<string> */
    private array $escapeValues;

    /** @var list<string> */
    private array $escapeWsKeys;

    /** @var list<string> */
    private array $escapeWsValues;

    public function __construct(
        Session $session,
        string $indent = '  ',
        bool $comments = false,
        bool $quoteAllNames = false,
        bool $escapeWhitespace = true
    ) {
        $this->session = $session;
        $this->indent = $indent;
        $this->comments = $comments;
        $this->quoteAllNames = $quoteAllNames;
        $this->escapeWhitespace = $escapeWhitespace;

        $escapes = self::MYSQL_ESCAPES;
        $this->escapeWsKeys = array_keys($escapes);
        $this->escapeWsValues = array_values($escapes);
        if (!$this->escapeWhitespace) {
            unset($escapes["\n"], $escapes["\r"], $escapes["\t"]);
        }
        $this->escapeKeys = array_keys($escapes);
        $this->escapeValues = array_values($escapes);
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function indent(string $code): string
    {
        return str_replace("\n", "\n\t", $code);
    }

    public function formatName(string $name): string
    {
        if ($name === '*') {
            return '*';
        }
        $sqlMode = $this->session->getMode();
        $quote = $sqlMode->containsAny(SqlMode::ANSI_QUOTES) ? '"' : '`';
        $name = str_replace($quote, $quote . $quote, $name);

        $needsQuoting = $this->quoteAllNames
            || strpos($name, $quote) !== false // contains quote
            || preg_match('~[\pL_]~u', $name) === 0 // does not contain letters
            || preg_match('~[\pC\pM\pS\pZ\p{Pd}\p{Pe}\p{Pf}\p{Pi}\p{Po}\p{Ps}]~u', ltrim($name, '@')) !== 0 // contains control, mark, symbols, whitespace, punctuation except _
            || $this->session->getPlatform()->isReserved($name);

        if ($needsQuoting && !$sqlMode->containsAny(SqlMode::NO_BACKSLASH_ESCAPES)) {
            $name = str_replace($this->escapeKeys, $this->escapeValues, $name);
        }

        return $needsQuoting ? $quote . $name . $quote : $name;
    }

    /**
     * @param non-empty-list<string|AllLiteral|PrimaryLiteral> $names
     */
    public function formatNamesList(array $names, string $separator = ', '): string
    {
        return implode($separator, array_map(function ($name): string {
            return $name instanceof Literal ? $name->getValue() : $this->formatName($name);
        }, $names));
    }

    /**
     * @param scalar|Date|Time|DateTimeInterface|SqlSerializable|null $value
     */
    public function formatValue($value): string
    {
        if ($value === null) {
            return Keyword::NULL;
        } elseif ($value === true) {
            return '1';
        } elseif ($value === false) {
            return '0';
        } elseif (is_string($value)) {
            return $this->formatString($value);
        } elseif (is_numeric($value)) {
            return (string) $value;
        } elseif ($value instanceof SqlSerializable) {
            return $value->serialize($this);
        } elseif ($value instanceof Date) {
            return $this->formatDate($value);
        } elseif ($value instanceof Time) {
            return $this->formatTime($value);
        } elseif ($value instanceof DateTimeInterface) {
            return $this->formatDateTime($value);
        }

        throw new LogicException('Unknown type: ' . (is_object($value) ? get_class($value) : gettype($value)));
    }

    /**
     * @param non-empty-list<scalar|Date|Time|DateTimeInterface|SqlSerializable|null> $values
     */
    public function formatValuesList(array $values, string $separator = ', '): string
    {
        return implode($separator, array_map(function ($value): string {
            return $this->formatValue($value);
        }, $values));
    }

    public function formatString(string $string): string
    {
        if (!$this->session->getMode()->containsAny(SqlMode::NO_BACKSLASH_ESCAPES)) {
            $string = str_replace($this->escapeKeys, $this->escapeValues, $string);
        }

        return "'" . str_replace("'", "''", $string) . "'";
    }

    public function formatStringForceEscapeWhitespace(string $string): string
    {
        if (!$this->session->getMode()->containsAny(SqlMode::NO_BACKSLASH_ESCAPES)) {
            $string = str_replace($this->escapeWsKeys, $this->escapeWsValues, $string);
        }

        return "'" . str_replace("'", "''", $string) . "'";
    }

    /**
     * @param non-empty-list<string> $strings
     */
    public function formatStringList(array $strings, string $separator = ', '): string
    {
        return implode($separator, array_map(function (string $string): string {
            return $this->formatString($string);
        }, $strings));
    }

    /**
     * @param non-empty-list<SqlSerializable> $serializables
     */
    public function formatSerializablesList(array $serializables, string $separator = ', '): string
    {
        return implode($separator, array_map(function (SqlSerializable $serializable): string {
            return $serializable->serialize($this);
        }, $serializables));
    }

    /**
     * @param non-empty-array<string, SqlSerializable> $serializables
     */
    public function formatSerializablesMap(array $serializables, string $separator = ', ', string $keyValueSeparator = ' = '): string
    {
        return implode($separator, Arr::mapPairs($serializables, function (string $key, SqlSerializable $value) use ($keyValueSeparator): string {
            return $key . $keyValueSeparator . $value->serialize($this);
        }));
    }

    /**
     * @param Date|DateTimeInterface $date
     */
    public function formatDate($date): string
    {
        return "'" . $date->format(Date::DEFAULT_FORMAT) . "'";
    }

    /**
     * @param Time|DateTimeInterface $time
     */
    public function formatTime($time): string
    {
        return "'" . $time->format(Time::DEFAULT_FORMAT) . "'";
    }

    public function formatDateTime(DateTimeInterface $dateTime): string
    {
        return "'" . $dateTime->format(DateTime::DEFAULT_FORMAT) . "'";
    }

    public function serialize(SqlSerializable $serializable, bool $comments = true, string $delimiter = ';'): string
    {
        if ($serializable instanceof Statement) {
            $result = ($comments ? implode('', $serializable->getCommentsBefore()) : '') . $serializable->serialize($this);
            if (!$serializable instanceof DelimiterCommand) {
                $result .= $serializable->getDelimiter() ?? $delimiter;
            }
        } else {
            $result = $serializable->serialize($this);
        }

        return $result;
    }

}
