<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

namespace SqlFtw\Resolver\Functions;

use SqlFtw\Sql\Expression\Value;
use function array_combine;
use function array_unique;
use function explode;
use function implode;
use function number_format;
use function str_replace;

/**
 * Functions from `sys` schema
 */
trait FunctionsSys
{

    /// sys.extract_schema_from_file_name
    /// sys.extract_table_from_file_name

    /**
     * sys.format_bytes() - Takes a raw bytes value, and converts it to a human-readable format.
     *
     * @param scalar|Value|null $bytes
     */
    public function sys__format_bytes($bytes): ?string
    {
        $bytes = $this->cast->toInt($bytes);

        if ($bytes === null) {
            return null;
        }

        $bytes = (float) $bytes;

        if ($bytes >= 2 ** 50) {
            $divider = 2 ** 50;
            $unit = ' EiB';
        } elseif ($bytes >= 2 ** 40) {
            $divider = 2 ** 40;
            $unit = ' TiB';
        } elseif ($bytes >= 2 ** 30) {
            $divider = 2 ** 30;
            $unit = ' GiB';
        } elseif ($bytes >= 2 ** 20) {
            $divider = 2 ** 20;
            $unit = ' MiB';
        } elseif ($bytes >= 2 ** 10) {
            $divider = 2 ** 10;
            $unit = ' kiB';
        } else {
            $divider = 1;
            $unit = ' bytes';
        }

        return number_format($bytes / $divider, 2) . $unit;
    }

    /// sys.format_path() - Takes a list, and a value to add to the list, and returns the resulting list.
    /// sys.format_statement() - Formats a normalized statement, truncating it if it is > 64 characters long by default.
    /// sys.format_time() - Takes a raw picoseconds value, and converts it to a human-readable form.

    /**
     * sys.list_add() - Takes a list, and a value to add to the list, and returns the resulting list.
     *
     * @param scalar|Value|null $list
     * @param scalar|Value|null $value
     */
    public function sys__list_add($list, $value): ?string
    {
        $list = $this->cast->toString($list);
        $value = $this->cast->toString($value);

        if ($list === null || $value === null) {
            return null;
        } else {
            $values = explode(',', $list);
            $values[] = $value;

            return implode(',', array_unique($values));
        }
    }

    /**
     * sys.list_drop() - Takes a list, and a value to attempt to remove from the list, and returns the resulting list.
     *
     * @param scalar|Value|null $list
     * @param scalar|Value|null $value
     */
    public function sys__list_drop($list, $value): ?string
    {
        $list = $this->cast->toString($list);
        $value = $this->cast->toString($value);

        if ($list === null || $value === null) {
            return null;
        } else {
            $values = explode(',', $list);
            $values = array_combine($values, $values);
            unset($values[$value]);

            return implode(',', $values);
        }
    }

    /// sys.ps_is_account_enabled() - Determines whether instrumentation of an account is enabled within Performance Schema.
    /// sys.ps_is_consumer_enabled() - Determines whether a consumer is enabled (taking the consumer hierarchy into consideration) within the Performance Schema.
    /// sys.ps_is_instrument_default_enabled() - Returns whether an instrument is enabled by default in this version of MySQL.
    /// sys.ps_is_instrument_default_timed() - Returns whether an instrument is timed by default in this version of MySQL.
    /// sys.ps_is_thread_instrumented() - Checks whether the provided connection id is instrumented within Performance Schema.
    /// sys.ps_thread_account() - Return the user@host account for the given Performance Schema thread id.
    /// sys.ps_thread_id() - Return the Performance Schema THREAD_ID for the specified connection ID.
    /// sys.ps_thread_stack() - Outputs a JSON formatted stack of all statements, stages and events within Performance Schema for the specified thread.
    /// sys.ps_thread_trx_info() - Returns a JSON object with info on the given threads current transaction, and the statements it has already executed, derived from the performance_schema.events_transactions_current and performance_schema.events_statements_history tables.

    /**
     * sys.quote_identifier() - Takes an unquoted identifier (schema name, table name, etc.) and returns the identifier quoted with backticks.
     *
     * @param scalar|Value|null $value
     */
    public function sys__quote_identifier($value): ?string
    {
        $value = $this->cast->toString($value);

        if ($value === null) {
            return null;
        } else {
            return '`' . str_replace('`', '``', $value) . '`';
        }
    }

    /// sys.sys_get_config() - Returns the value for the requested variable from sys.sys_config.

    /**
     * sys.version_major() - Returns the major version of MySQL Server.
     */
    public function sys_version_major(): int
    {
        return $this->session->getPlatform()->getVersion()->getMajor();
    }

    /**
     * sys.version_minor() - Returns the minor (release series) version of MySQL Server.
     */
    public function sys_version_minor(): int
    {
        return $this->session->getPlatform()->getVersion()->getMinor();
    }

    /**
     * sys.version_patch() - Returns the patch release version of MySQL Server.
     */
    public function sys_version_patch(): int
    {
        return $this->session->getPlatform()->getVersion()->getPatch();
    }

}
