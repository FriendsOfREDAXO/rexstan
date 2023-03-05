<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use DateTimeInterface;
use Dogma\Check;
use Dogma\Cls;
use Dogma\Comparable;
use Dogma\Dumpable;
use Dogma\Equalable;
use Dogma\Obj;
use Dogma\Str;
use Dogma\StrictBehaviorMixin;
use Throwable;
use function explode;
use function implode;
use function intval;
use function preg_match;
use function sprintf;
use function substr;

class YearMonth implements Comparable, Equalable, Dumpable
{
    use StrictBehaviorMixin;

    public const DEFAULT_FORMAT = 'Y-m';

    /** @var string */
    private $value;

    /**
     * @param string|Date|DateTimeInterface|null $value
     */
    final public function __construct($value = null)
    {
        if ($value === null) {
            $this->value = (new Date())->format(self::DEFAULT_FORMAT);
            return;
        }

        if ($value instanceof Date || $value instanceof DateTimeInterface) {
            $this->value = $value->format(self::DEFAULT_FORMAT);
            return;
        }

        try {
            $dateTime = preg_match('/\\d+-\\d+/', $value)
                ? DateTime::createFromFormat('Y-m-d', $value . '-01')
                : new DateTime($value);
            $this->value = $dateTime->format(self::DEFAULT_FORMAT);
        } catch (Throwable $e) {
            throw new InvalidDateTimeException($value, $e);
        }
    }

    public static function createFromIntValue(int $value): self
    {
        Check::range($value, 1, 999912);

        $value = Str::padLeft((string) $value, 6, '0');

        return new static(substr($value, 0, 4) . '-' . substr($value, 4, 2));
    }

    public static function createFromComponents(int $year, int $month): self
    {
        return new static($year . '-' . $month);
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): string
    {
        return sprintf('%s(%s #%s)', Cls::short(static::class), $this->format(), Obj::dumpHash($this));
    }

    public function add(int $months, int $years = 0): self
    {
        $year = $this->getYear() + $years + intval($months / 12);
        $month = $this->getMonth() + ($months % 12);
        if ($month > 12) {
            $month -= 12;
            $year++;
        }

        return self::createFromComponents($year, $month);
    }

    public function subtract(int $months, int $years = 0): self
    {
        $year = $this->getYear() - $years - intval($months / 12);
        $month = $this->getMonth() - ($months % 12);
        if ($month < 1) {
            $month += 12;
            $year--;
        }

        return self::createFromComponents($year, $month);
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->value <=> $other->value;
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $this->value === $other->value;
    }

    public function isBefore(self $other): bool
    {
        return $this->value < $other->value;
    }

    public function isAfter(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function getYear(): int
    {
        return (int) explode('-', $this->value)[0];
    }

    public function getMonth(): int
    {
        return (int) explode('-', $this->value)[1];
    }

    public function getMonthEnum(): Month
    {
        return Month::get((int) explode('-', $this->value)[1]);
    }

    public function getIntValue(): int
    {
        return (int) implode('', explode('-', $this->value));
    }

    public function getStart(): Date
    {
        return new Date($this->value);
    }

    public function getEnd(): Date
    {
        return $this->getStart()->modify('last day of this month');
    }

    public function format(string $format = self::DEFAULT_FORMAT): string
    {
        return $this->getStart()->format($format);
    }

    public function next(): self
    {
        return new static($this->getStart()->modify('+1 month'));
    }

    public function previous(): self
    {
        return new static($this->getStart()->modify('-1 month'));
    }

}
