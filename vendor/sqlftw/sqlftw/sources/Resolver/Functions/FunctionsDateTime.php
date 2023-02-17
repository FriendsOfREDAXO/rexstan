<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver\Functions;

use DateTimeZone;
use Dogma\Time\DateTime;
use Dogma\Time\TimeZone;
use SqlFtw\Sql\Expression\Value;

trait FunctionsDateTime
{

    // ADDDATE() - Add time values (intervals) to a date value
    // ADDTIME() - Add time
    // CONVERT_TZ() - Convert from one time zone to another
    // CURDATE() - Return the current date
    // CURRENT_DATE(), CURRENT_DATE - Synonyms for CURDATE()
    // CURRENT_TIME(), CURRENT_TIME - Synonyms for CURTIME()
    // CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP - Synonyms for NOW()
    // CURTIME() - Return the current time
    // DATE() - Extract the date part of a date or datetime expression
    // DATE_ADD() - Add time values (intervals) to a date value
    // DATE_FORMAT() - Format date as specified
    // DATE_SUB() - Subtract a time value (interval) from a date
    // DATEDIFF() - Subtract two dates
    // DAY() - Synonym for DAYOFMONTH()
    // DAYNAME() - Return the name of the weekday
    // DAYOFMONTH() - Return the day of the month (0-31)
    // DAYOFWEEK() - Return the weekday index of the argument
    // DAYOFYEAR() - Return the day of the year (1-366)
    // EXTRACT() - Extract part of a date
    // FROM_DAYS() - Convert a day number to a date
    // FROM_UNIXTIME() - Format Unix timestamp as a date
    // GET_FORMAT() - Return a date format string
    // HOUR() - Extract the hour
    // LAST_DAY - Return the last day of the month for the argument
    // LOCALTIME(), LOCALTIME - Synonym for NOW()
    // LOCALTIMESTAMP, LOCALTIMESTAMP() - Synonym for NOW()
    // MAKEDATE() - Create a date from the year and day of year
    // MAKETIME() - Create time from hour, minute, second
    // MICROSECOND() - Return the microseconds from argument
    // MINUTE() - Return the minute from the argument
    // MONTH() - Return the month from the date passed
    // MONTHNAME() - Return the name of the month
    // NOW() - Return the current date and time
    // PERIOD_ADD() - Add a period to a year-month
    // PERIOD_DIFF() - Return the number of months between periods
    // QUARTER() - Return the quarter from a date argument
    // SEC_TO_TIME() - Converts seconds to 'hh:mm:ss' format
    // SECOND() - Return the second (0-59)
    // STR_TO_DATE() - Convert a string to a date
    // SUBDATE() - Synonym for DATE_SUB() when invoked with three arguments
    // SUBTIME() - Subtract times
    // SYSDATE() - Return the time at which the function executes
    // TIME() - Extract the time portion of the expression passed
    // TIME_FORMAT() - Format as time
    // TIME_TO_SEC() - Return the argument converted to seconds
    // TIMEDIFF() - Subtract time
    // TIMESTAMP() - With a single argument, this function returns the date or datetime expression; with two arguments, the sum of the arguments
    // TIMESTAMPADD() - Add an interval to a datetime expression
    // TIMESTAMPDIFF() - Subtract an interval from a datetime expression
    // TO_DAYS() - Return the date argument converted to days
    // TO_SECONDS() - Return the date or datetime argument converted to seconds since Year 0

    /**
     * UNIX_TIMESTAMP() - Return a Unix timestamp
     *
     * @param scalar|Value|null $value
     */
    public function unix_timestamp($value = 'now'): ?int
    {
        $value = $this->cast->toString($value);
        if ($value === null) {
            return null;
        }

        return (new DateTime($value, new DateTimeZone(TimeZone::UTC)))->getTimestamp();
    }

    // UTC_DATE() - Return the current UTC date
    // UTC_TIME() - Return the current UTC time
    // UTC_TIMESTAMP() - Return the current UTC date and time
    // WEEK() - Return the week number
    // WEEKDAY() - Return the weekday index
    // WEEKOFYEAR() - Return the calendar week of the date (1-53)
    // YEAR() - Return the year
    // YEARWEEK() - Return the year and week

}
