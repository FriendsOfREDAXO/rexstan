<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: pty df hl yz

namespace Dogma\Time\Span;

use Dogma\Language\Localization\Translator;
use Dogma\Str;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Format\Formatting;
use function abs;
use function in_array;
use function number_format;
use function rtrim;
use function str_split;

class DateTimeSpanFormatter
{
    use StrictBehaviorMixin;

    public const YEARS = 'y';
    public const YEARS_FRACTION = 'Y';
    public const YEARS_WORD = 'Z';
    public const YEARS_UNIT = 'z';

    public const MONTHS = 'm';
    public const MONTHS_FRACTION = 'M';
    public const MONTHS_TOTAL = 'n';
    public const MONTHS_TOTAL_FRACTION = 'N';
    public const MONTHS_WORD = 'O';
    public const MONTHS_UNIT = 'o';

    public const WEEKS = 'w';
    public const WEEKS_FRACTION = 'W';
    public const WEEKS_TOTAL = 'x';
    public const WEEKS_TOTAL_FRACTION = 'X';
    public const WEEKS_WORD = 'A';
    public const WEEKS_UNIT = 'a';

    public const DAYS = 'd';
    public const DAYS_FRACTION = 'D';
    public const DAYS_TOTAL = 'e';
    public const DAYS_TOTAL_FRACTION = 'E';
    public const DAYS_WORD = 'F';
    public const DAYS_UNIT = 'f';

    public const HOURS = 'h';
    public const HOURS_FRACTION = 'H';
    public const HOURS_TOTAL = 'g';
    public const HOURS_TOTAL_FRACTION = 'G';
    public const HOURS_WORD = 'L';
    public const HOURS_UNIT = 'l';

    public const MINUTES = 'i';
    public const MINUTES_FRACTION = 'I';
    public const MINUTES_TOTAL = 'j';
    public const MINUTES_TOTAL_FRACTION = 'J';
    public const MINUTES_WORD = 'K';
    public const MINUTES_UNIT = 'k';

    public const SECONDS = 's';
    public const SECONDS_FRACTION = 'S';
    public const SECONDS_TOTAL = 't';
    public const SECONDS_TOTAL_FRACTION = 'T';
    public const SECONDS_WORD = 'R';
    public const SECONDS_UNIT = 'r';

    public const MILISECONDS = 'v';
    public const MILISECONDS_TOTAL = 'V';
    public const MILISECONDS_WORD = 'B';
    public const MILISECONDS_UNIT = 'b';

    public const MICROSECONDS = 'u';
    public const MICROSECONDS_TOTAL = 'U';
    public const MICROSECONDS_WORD = 'C';
    public const MICROSECONDS_UNIT = 'c';

    public const PRETTY_DOUBLE_WORDS = 'P';
    public const PRETTY_DOUBLE_UNITS = 'P';
    public const PRETTY_SINGLE_WORDS = 'Q';
    public const PRETTY_SINGLE_UNITS = 'q';

    public const FORMAT_DEFAULT = 'y-m-d h:i:S';
    public const FORMAT_FULL_WORDS = '[yZ, ][mO, ]dF[, hL][, iK][, SR]';
    public const FORMAT_FULL_UNITS = '[yz, ][mo, ]df[, hl][, ik][, Sr]';

    /** @var string[] */
    private static $specialCharacters = [
        self::YEARS,
        self::YEARS_FRACTION,
        self::YEARS_UNIT,
        self::YEARS_WORD,
        self::MONTHS,
        self::MONTHS_FRACTION,
        self::MONTHS_TOTAL,
        self::MONTHS_TOTAL_FRACTION,
        self::MONTHS_UNIT,
        self::MONTHS_WORD,
        self::WEEKS,
        self::WEEKS_FRACTION,
        self::WEEKS_TOTAL,
        self::WEEKS_TOTAL_FRACTION,
        self::WEEKS_UNIT,
        self::WEEKS_WORD,
        self::DAYS,
        self::DAYS_FRACTION,
        self::DAYS_TOTAL,
        self::DAYS_TOTAL_FRACTION,
        self::DAYS_UNIT,
        self::DAYS_WORD,
        self::HOURS,
        self::HOURS_FRACTION,
        self::HOURS_TOTAL,
        self::HOURS_TOTAL_FRACTION,
        self::HOURS_UNIT,
        self::HOURS_WORD,
        self::MINUTES,
        self::MINUTES_FRACTION,
        self::MINUTES_TOTAL,
        self::MINUTES_TOTAL_FRACTION,
        self::MINUTES_UNIT,
        self::MINUTES_WORD,
        self::SECONDS,
        self::SECONDS_FRACTION,
        self::SECONDS_TOTAL,
        self::SECONDS_TOTAL_FRACTION,
        self::SECONDS_UNIT,
        self::SECONDS_WORD,
        self::MILISECONDS,
        self::MILISECONDS_TOTAL,
        self::MILISECONDS_UNIT,
        self::MILISECONDS_WORD,
        self::MICROSECONDS,
        self::MICROSECONDS_TOTAL,
        self::MICROSECONDS_UNIT,
        self::MICROSECONDS_WORD,
        self::PRETTY_DOUBLE_WORDS,
        self::PRETTY_DOUBLE_UNITS,
        self::PRETTY_SINGLE_WORDS,
        self::PRETTY_SINGLE_UNITS,
    ];

    /** @var string */
    private $format;

    /** @var int */
    private $maxDecimals;

    /** @var string */
    private $decimalPoint;

    /** @var Translator|null */
    private $translator;

    public function __construct(
        string $format = self::FORMAT_DEFAULT,
        int $maxDecimals = 1,
        string $decimalPoint = '.',
        ?Translator $translator = null
    ) {
        $this->format = $format;
        $this->maxDecimals = $maxDecimals;
        $this->decimalPoint = $decimalPoint;
        $this->translator = $translator;
    }

    /**
     * Letter assignment:
     *  w µs ms day__ hour minute h month pty second µs ms week year
     *  A B  C  D E F G H  I J K  L M N O P Q R S T  U  V  W X  Y Z
     *
     * Use "[" and "]" for optional groups. Group containing a value of zero will not be part of output. Eg:
     * - "[m O, ]d F" will print "5 months, 10 days" or "10 days" if months are 0.
     * - "[m O, d F, ]h l" will print "0 months, 5 days, 2 hours" because both values inside group must be 0 to skip group.
     *
     * Ch.  Description                                 Example values
     * ---- ------------------------------------------- --------------
     * Escaping:
     * %    Escape character. Use %% for printing "%"
     *
     * Skip groups:
     * [    Group start, skip if zero
     * ]    Group end, skip if zero
     *
     * Modifiers:
     * ^    -   Upper case letters
     * !    -   Starts with upper case letter
     *
     * Objects:
     * Y    Years fraction                              1.5, 3.2, -3.2
     * y    Years                                       1, 3, -3
     * Z    'years' word                                year, years, years
     * z    'y' for years                               y, y, y
     *
     * M    Months fraction (based on 30 days month)    1.5, 3.2, -3.2
     * m    Months                                      1, 3, -3
     * N    Months total fraction                       1.5, 27.2, -3.2
     * n    Months total                                1, 27, -3
     * O    'months' word                               month, months, months
     * o    'm' for months                              m, m, m
     *
     * W    Weeks fraction (calculated from days)       1.5, 3.2, -3.2
     * w    Weeks (calculated from days)                1, 3, -3
     * X    Weeks total fraction (calculated from days) 1.5, 55.2, -3.2
     * x    Weeks total (calculated from days)          1, 55, -3
     * A    'weeks' word                                week, weeks, weeks
     * a    'w' for weeks                               w, w, w
     *
     * D    Days fraction                               1.5, 3.2, -3.2
     * d    Days                                        1, 3, -3
     * E    Days total fraction                         1.5, 33.2, -3.2
     * e    Days total                                  1, 33, -3
     * F    'days' word                                 day, days, days
     * f    'd' for days                                d, d, d
     *
     * H    Hours fraction                              1.5, 3.2, -3.2
     * h    Hours                                       1, 3, -3
     * G    Hours total fraction                        1.5, 27.3, -3.2
     * g    Hours total                                 1, 27, -3
     * L    'hours' word                                hour, hours, hours
     * l    'h' for hours                               h, h, h
     *
     * I    Minutes fraction                            1.5, 3.2, -3.2
     * i    Minutes                                     1, 3, -2
     * J    Minutes total fraction                      1.5, 63.2, -3.2
     * j    Minutes total                               1.5, 63, -3
     * K    'minutes' word                              minute, minutes, minutes
     * k    'm' for minutes                             m, m, m
     *
     * S    Seconds fraction                            1.5, 3.2, -3.2
     * s    Seconds                                     1, 3, -3
     * T    Seconds total fraction                      1.5, 63.2, -3.2
     * t    Seconds total                               1, 63, -3
     * R    'seconds' word                              second, seconds, seconds
     * r    's' for seconds                             s, s, s
     *
     * V    Miliseconds total (calculated from µs)      7, 108952
     * v    Miliseconds (calculated from µs)            7, 52
     * B    'miliseconds' word                          miliseconds
     * b    'ms' for miliseconds                        ms
     *
     * U    Microseconds total                          7701, 108952738
     * u    Microseconds                                7701, 52738
     * C    'microseconds' word                         microseconds
     * c    'µs' for microseconds                       µs
     *
     * P    Pretty, two values                          "2 weeks, 1 day", "2 weeks", "2 weeks, -1 day"
     * p    Pretty, two values, units                   "2w, 1d", "2w", "2w, -1d"
     * Q    Pretty, single value                        "15 days", "2 weeks", "13 days"
     * q    Pretty, single value, units                 "15d", "2w", "13d"
     *
     * @return string
     */
    public function format(
        DateTimeSpan $span,
        ?string $format = null,
        ?int $maxDecimals = null,
        ?string $decimalPoint = null
    ): string
    {
        $format = $format ?? $this->format;
        $maxDecimals = $maxDecimals ?? $this->maxDecimals;
        $decimalPoint = $decimalPoint ?? $this->decimalPoint;

        $result = $group = '';
        $last = null;
        $escaped = $groupValid = $hasWeeks = false;
        $characters = str_split($format);
        $characters[] = '';
        foreach ($characters as $i => $character) {
            if ($character === '%' && !$escaped) {
                $escaped = true;
            } elseif ($escaped === false && in_array($character, self::$specialCharacters, true)) {
                switch ($character) {
                    case self::YEARS:
                        // years ---------------------------------------------------------------------------------------
                        $years = $span->getYears();
                        $group .= $years;
                        if ($years !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::YEARS_FRACTION:
                        $years = $span->getYearsFraction();
                        $group .= $this->formatNumber($years, $maxDecimals, $decimalPoint);
                        if ($years !== 0.0) {
                            $groupValid = true;
                        }
                        break;
                    case self::YEARS_WORD:
                        $group .= $this->formatUnit(self::YEARS, $characters[$i + 1], $span->getYears());
                        break;
                    case self::YEARS_UNIT:
                        $group .= $this->formatUnit(self::YEARS, $characters[$i + 1]);
                        break;
                    case self::MONTHS:
                        // months --------------------------------------------------------------------------------------
                        $months = $span->getMonths();
                        $group .= $months;
                        if ($months !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::MONTHS_FRACTION:
                        $months = $span->getMonthsFraction();
                        $group .= $this->formatNumber($months, $maxDecimals, $decimalPoint);
                        if ($months !== 0.0) {
                            $groupValid = true;
                        }
                        break;
                    case self::MONTHS_TOTAL:
                        $months = (int) $span->getMonthsTotal();
                        $group .= $months;
                        if ($months !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::MONTHS_TOTAL_FRACTION:
                        $group .= $this->formatNumber($span->getMonthsTotal(), $maxDecimals, $decimalPoint);
                        break;
                    case self::MONTHS_WORD:
                        $months = $last === self::MONTHS_TOTAL || $last === self::MONTHS_TOTAL_FRACTION
                            ? (int) $span->getMonthsTotal()
                            : $span->getMonths();
                        $group .= $this->formatUnit(self::MONTHS, $characters[$i + 1], $months);
                        break;
                    case self::MONTHS_UNIT:
                        $group .= $this->formatUnit(self::MONTHS, $characters[$i + 1]);
                        break;
                    case self::WEEKS:
                        // weeks ---------------------------------------------------------------------------------------
                        $weeks = $span->getWeeks();
                        $group .= $weeks;
                        if ($weeks !== 0) {
                            $groupValid = true;
                        }
                        $hasWeeks = true;
                        break;
                    case self::WEEKS_FRACTION:
                        $weeks = $span->getWeeksFraction();
                        $group .= $this->formatNumber($weeks, $maxDecimals, $decimalPoint);
                        if ($weeks !== 0.0) {
                            $groupValid = true;
                        }
                        $hasWeeks = true;
                        break;
                    case self::WEEKS_TOTAL:
                        $weeks = (int) $span->getWeeksTotal();
                        $group .= $weeks;
                        if ($weeks !== 0) {
                            $groupValid = true;
                        }
                        $hasWeeks = true;
                        break;
                    case self::WEEKS_TOTAL_FRACTION:
                        $weeks = $span->getWeeksTotal();
                        $group .= $this->formatNumber($weeks, $maxDecimals, $decimalPoint);
                        if ($weeks !== 0.0) {
                            $groupValid = true;
                        }
                        $hasWeeks = true;
                        break;
                    case self::WEEKS_WORD:
                        $weeks = $last === self::WEEKS_TOTAL || $last === self::WEEKS_TOTAL_FRACTION
                            ? (int) $span->getWeeksTotal()
                            : $span->getWeeks();
                        $group .= $this->formatUnit(self::WEEKS, $characters[$i + 1], $weeks);
                        break;
                    case self::WEEKS_UNIT:
                        $group .= $this->formatUnit(self::WEEKS, $characters[$i + 1]);
                        break;
                    case self::DAYS:
                        // days ----------------------------------------------------------------------------------------
                        $days = $hasWeeks ? ($span->getDays() - $span->getWeeks() * 7) : $span->getDays();
                        $group .= $days;
                        if ($days !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::DAYS_FRACTION:
                        $days = $hasWeeks ? ($span->getDaysFraction() - $span->getWeeks() * 7) : $span->getDaysFraction();
                        $group .= $this->formatNumber($days, $maxDecimals, $decimalPoint);
                        if ($days !== 0.0) {
                            $groupValid = true;
                        }
                        break;
                    case self::DAYS_TOTAL:
                        $days = (int) $span->getDaysTotal();
                        $group .= $days;
                        if ($days !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::DAYS_TOTAL_FRACTION:
                        $days = $span->getDaysTotal();
                        $group .= $this->formatNumber($days, $maxDecimals, $decimalPoint);
                        if ($days !== 0.0) {
                            $groupValid = true;
                        }
                        break;
                    case self::DAYS_WORD:
                        $days = $last === self::DAYS_TOTAL || $last === self::DAYS_TOTAL_FRACTION
                            ? (int) ($hasWeeks ? ($span->getDaysFraction() - $span->getWeeks() * 7) : $span->getDaysFraction())
                            : ($hasWeeks ? ($span->getDays() - $span->getWeeks() * 7) : $span->getDays());
                        $group .= $this->formatUnit(self::DAYS, $characters[$i + 1], $days);
                        break;
                    case self::DAYS_UNIT:
                        $group .= $this->formatUnit(self::DAYS, $characters[$i + 1]);
                        break;
                    case self::HOURS:
                        // hours ---------------------------------------------------------------------------------------
                        $hours = $span->getHours();
                        $group .= $hours;
                        if ($hours !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::HOURS_FRACTION:
                        $hours = $span->getHoursFraction();
                        $group .= $this->formatNumber($hours, $maxDecimals, $decimalPoint);
                        if ($hours !== 0.0) {
                            $groupValid = true;
                        }
                        break;
                    case self::HOURS_TOTAL:
                        $hours = (int) $span->getHoursTotal();
                        $group .= $hours;
                        if ($hours !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::HOURS_TOTAL_FRACTION:
                        $hours = $span->getHoursTotal();
                        $group .= $this->formatNumber($hours, $maxDecimals, $decimalPoint);
                        if ($hours !== 0.0) {
                            $groupValid = true;
                        }
                        break;
                    case self::HOURS_WORD:
                        $hours = $last === self::HOURS_TOTAL || $last === self::HOURS_TOTAL_FRACTION
                            ? (int) $span->getHoursTotal()
                            : $span->getHours();
                        $group .= $this->formatUnit(self::HOURS, $characters[$i + 1], $hours);
                        break;
                    case self::HOURS_UNIT:
                        $group .= $this->formatUnit(self::HOURS, $characters[$i + 1]);
                        break;
                    case self::MINUTES:
                        // minutes -------------------------------------------------------------------------------------
                        $minutes = $span->getMinutes();
                        $group .= $minutes;
                        if ($minutes !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::MINUTES_FRACTION:
                        $minutes = $span->getMinutesFraction();
                        $group .= $this->formatNumber($minutes, $maxDecimals, $decimalPoint);
                        if ($minutes !== 0.0) {
                            $groupValid = true;
                        }
                        break;
                    case self::MINUTES_TOTAL:
                        $minutes = (int) $span->getMinutesTotal();
                        $group .= $minutes;
                        if ($minutes !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::MINUTES_TOTAL_FRACTION:
                        $minutes = $span->getMinutesTotal();
                        $group .= $this->formatNumber($minutes, $maxDecimals, $decimalPoint);
                        if ($minutes !== 0.0) {
                            $groupValid = true;
                        }
                        break;
                    case self::MINUTES_WORD:
                        $minutes = $last === self::MINUTES_TOTAL || $last === self::MINUTES_TOTAL_FRACTION
                            ? (int) $span->getMinutesTotal()
                            : $span->getMinutes();
                        $group .= $this->formatUnit(self::MINUTES, $characters[$i + 1], $minutes);
                        break;
                    case self::MINUTES_UNIT:
                        $group .= $this->formatUnit(self::MINUTES, $characters[$i + 1]);
                        break;
                    case self::SECONDS:
                        // seconds -------------------------------------------------------------------------------------
                        $seconds = $span->getSeconds();
                        $group .= $seconds;
                        if ($seconds !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::SECONDS_FRACTION:
                        $seconds = $span->getSecondsFraction();
                        $group .= $this->formatNumber($seconds, $maxDecimals, $decimalPoint);
                        if ($seconds !== 0.0) {
                            $groupValid = true;
                        }
                        break;
                    case self::SECONDS_TOTAL:
                        $seconds = (int) $span->getSecondsTotal();
                        $group .= $seconds;
                        if ($seconds !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::SECONDS_TOTAL_FRACTION:
                        $seconds = $span->getSecondsTotal();
                        $group .= $this->formatNumber($seconds, $maxDecimals, $decimalPoint);
                        if ($seconds !== 0.0) {
                            $groupValid = true;
                        }
                        break;
                    case self::SECONDS_WORD:
                        $seconds = $last === self::SECONDS_TOTAL || $last === self::SECONDS_TOTAL_FRACTION
                            ? (int) $span->getSecondsTotal()
                            : $span->getSeconds();
                        $group .= $this->formatUnit(self::SECONDS, $characters[$i + 1], $seconds);
                        break;
                    case self::SECONDS_UNIT:
                        $group .= $this->formatUnit(self::SECONDS, $characters[$i + 1]);
                        break;
                    case self::MILISECONDS:
                        // miliseconds ---------------------------------------------------------------------------------
                        $microseconds = (int) ($span->getMicroseconds() / 1000);
                        $group .= $microseconds;
                        if ($microseconds !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::MILISECONDS_TOTAL:
                        $microseconds = (int) ($span->getMicrosecondsTotal() / 1000);
                        $group .= $microseconds;
                        if ($microseconds !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::MILISECONDS_WORD:
                        $group .= $this->formatUnit(self::MILISECONDS, $characters[$i + 1], (int) ($span->getMicroseconds() / 1000));
                        break;
                    case self::MILISECONDS_UNIT:
                        $group .= $this->formatUnit(self::MILISECONDS, $characters[$i + 1]);
                        break;
                    case self::MICROSECONDS:
                        // microseconds --------------------------------------------------------------------------------
                        $microseconds = $span->getMicroseconds();
                        $group .= $microseconds;
                        if ($microseconds !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::MICROSECONDS_TOTAL:
                        $microseconds = $span->getMicrosecondsTotal();
                        $group .= $microseconds;
                        if ($microseconds !== 0) {
                            $groupValid = true;
                        }
                        break;
                    case self::MICROSECONDS_WORD:
                        $group .= $this->formatUnit(self::MICROSECONDS, $characters[$i + 1], $span->getMicroseconds());
                        break;
                    case self::MICROSECONDS_UNIT:
                        $group .= $this->formatUnit(self::MICROSECONDS, $characters[$i + 1]);
                        break;
                    case Formatting::NO_ZEROS_GROUP_START:
                        // groups --------------------------------------------------------------------------------------
                        $result .= $group;
                        $group = '';
                        $groupValid = false;
                        break;
                    case Formatting::NO_ZEROS_GROUP_END:
                        if ($groupValid) {
                            $result .= $group;
                        }
                        $group = '';
                        $groupValid = false;
                        break;
                }
                $last = $character;
                $escaped = false;
            } else {
                $group .= $character;
            }
        }

        return $result . $group;
    }

    private function formatNumber(float $number, int $maxDecimals, string $decimalPoint): string
    {
        $number = number_format($number, $maxDecimals, $decimalPoint, '');

        return rtrim(rtrim($number, '0'), $decimalPoint);
    }

    private function formatUnit(string $unit, string $modifier, ?int $number = null): string
    {
        $unit = $this->getUnit($unit, $number);
        switch ($modifier) {
            case Formatting::UPPER_MODIFIER:
                return Str::upper($unit);
            case Formatting::CAPITALIZE_MODIFIER:
                return Str::firstUpper($unit);
            default:
                return $unit;
        }
    }

    /**
     * (Override for other plural rules or use Translator.)
     * @return string
     */
    protected function getUnit(string $unit, ?int $number = null): string
    {
        $words = $this->getVocabulary();

        if ($number === null) {
            if ($this->translator !== null) {
                return $this->translator->translate($words[$unit][0]);
            } else {
                return $words[$unit][0];
            }
        }

        if ($this->translator !== null) {
            return $this->translator->translate($words[$unit][1], $number);
        }

        if (abs($number) < 2) {
            return $words[$unit][1];
        } else {
            return $words[$unit][2];
        }
    }

    /**
     * (Override for other default translations.)
     * @return string[][]
     */
    protected function getVocabulary(): array
    {
        return [
            self::YEARS => ['y', 'year', 'years'],
            self::MONTHS => ['m', 'month', 'months'],
            self::WEEKS => ['w', 'week', 'weeks'],
            self::DAYS => ['d', 'day', 'days'],
            self::HOURS => ['h', 'hour', 'hours'],
            self::MINUTES => ['m', 'minute', 'minutes'],
            self::SECONDS => ['s', 'second', 'seconds'],
            self::MILISECONDS => ['ms', 'milisecond', 'miliseconds'],
            self::MICROSECONDS => ['µs', 'microsecond', 'microseconds'],
        ];
    }

}
