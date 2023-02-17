<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: XBA XBB XBC XBD XBE XBF XBG XBY XBZ closehandle expecterror mysqld recvresult stmtadmin stmtsql usexxx

namespace SqlFtw\Tests\Mysql;

use Dogma\Str;
use SqlFtw\Platform\Features\MysqlError;
use function array_flip;
use function array_splice;
use function explode;
use function implode;
use function preg_match;
use function preg_quote;
use function strpos;
use function substr;

/**
 * Filters non-SQL code from MySQL tests
 */
class MysqlTestFilter
{

    public const MYSQL_TEST_SUITE_COMMANDS = [
        'append_file', 'assert', 'break', 'cat_file', 'change_user', 'character_set', 'chdir', 'chmod', /*'close',*/ 'close OUT', 'closehandle',
        'connect', 'CONNECT', 'copy_file', 'dec', 'DEC', 'die', 'diff_files', 'dirty_close', 'disable_abort_on_error',
        'disable_async_client', 'disable_connect_log', 'disable_info', 'disable_metadata', 'disable_parsing',
        'disable_ps_protocol', 'disable_query_log', 'disable_reconnect', 'disable_result_log', 'disable_session_track_info',
        'disable_testcase', 'disable_warnings', 'disconnect', 'DISCONNECT', 'echo', 'ECHO', 'enable_abort_on_error', 'enable_async_client',
        'enable_connect_log', 'enable_info', 'enable_metadata', 'enable_parsing', 'enable_ps_protocol', 'enable_query_log',
        'enable_reconnect', 'enable_result_log', 'enable_session_track_info', 'enable_testcase', 'enable_warnings',
        'END_OF_PROCEDURE', 'eof', 'EOF', 'ERROR_LOG', 'eval', 'EVAL', 'exec', 'EXECUTE_STEP', 'exit', 'expecterror',
        'expr', 'file_exists', 'force', 'horizontal_results', 'ibdata2', 'inc', 'INC', 'let', 'LET', 'list_files',
        'lowercase_result', 'mkdir', 'move_file', 'my', 'mysqlx', /*'open',*/ 'open OUT', 'output', 'partially_sorted_result', 'perl',
        'print', 'PROCEDURE', 'query', 'query_attributes', 'read', 'real_sleep', 'reap', 'REAP', 'REAp', 'recvresult', 'remove_file',
        'remove_files_wildcard', 'replace_column', 'replace_numeric_round', 'replace_regex', 'replace_result',
        'reset_connection', 'result_format', 'rmdir', 'save_master_pos', 'SCRIPT', 'secret', 'send_eval', 'send_quit',
        'shutdown_server', 'skip', 'skip_if_hypergraph', 'sleep', 'SLEEP', 'source', 'SOURCE', 'sorted_result', 'stmtadmin', 'stmtsql',
        'sync_slave_with_master', 'sync_with_master', 'system', 'unlink', 'usexxx', 'vertical_results', 'wait',
        'wait_for_slave_to_stop', 'write_file',
    ];

    private string $commands;

    /** @var array<int, string> */
    private array $errorCodes;

    public function __construct()
    {
        $this->commands = implode('|', self::MYSQL_TEST_SUITE_COMMANDS);
        $this->errorCodes = array_flip(MysqlError::getAllowedValues());
    }

    public function filter(string $text): string
    {
        $rows = explode("\n", $text);
        $i = 0;
        $delimiter = ';';
        $quotedDelimiter = ';';
        while ($i < count($rows)) {
            $row = $rows[$i];
            if ($row === 'my $escaped_query = <<END;'
                || $row === '($session_epoch)'
            ) {
                // trim rest of file
                array_splice($rows, $i, count($rows) - $i, ['-- XB1 removed all']);
                break;
            } elseif (preg_match('~^\s*DELIMITER (.*)~i', $row, $m) !== 0) {
                $d = trim($m[1]);
                //rl($delimiter, $i, 'y');
                //rl($d, null, 'g');
                // stripping Perl delimiter from SQL delimiter definition
                $delimiter = Str::endsWith($d, $delimiter) ? substr($d, 0, -strlen($delimiter)) : $d;
                $rows[$i] = 'DELIMITER ' . $delimiter . ' -- XBZ DELIMITER ' . $delimiter;
                //rl($delimiter);
                $quotedDelimiter = preg_quote($delimiter, '~');
            } elseif (preg_match('~^\s*--delimiter (.*)~i', $row, $m) !== 0) {
                // delimiter
                $d = trim($m[1]);
                //rl($delimiter, $i, 'y');
                //rl($d, null, 'g');
                $rows[$i] = 'DELIMITER ' . $d . ' -- XB0';
                $delimiter = $d;
                //rl($delimiter);
                $quotedDelimiter = preg_quote($delimiter, '~');
            } elseif (preg_match('~^\s*-- ?error (.*)~i', $row, $m) !== 0) {
                // error code
                $rows[$i] = '-- error ' . $this->translateErrorCodes($m[1]);
            } elseif (preg_match('~^\s*error ((?:\d|ER_).*)~i', $row, $m) !== 0) {
                // error code
                $rows[$i] = '-- error ' . $this->translateErrorCodes($m[1]);
            } elseif (preg_match('~^--disable_abort_on_error~i', $row, $m) !== 0) {
                // error code
                $rows[$i] = '-- error DISABLED (from "' . $m[0] . '")';
            } elseif (preg_match('~^--(query_vertical|send) (.*)~i', $row, $m) !== 0) {
                // query
                $rows[$i] = $m[2] . '; -- (from "--' . $m[1] . '")';
            } elseif (preg_match('~^\s*(query_vertical|send)(?:\s+(.*)|$)~i', $row, $m) !== 0) {
                // query
                $rows[$i] = ($m[2] ?? '') . ' -- (from "' . $m[1] . '")';
            } elseif (preg_match('~^--disable_testcase~', $row) !== 0) {
                // skipped
                $j = $i + 1;
                $replace = ['-- XB2 ' . $row];
                while ($j < count($rows)) {
                    $replace[] = '-- XB2 ' . $rows[$j];
                    if ($rows[$j] === '--enable_testcase') {
                        array_splice($rows, $i, $j - $i + 1, $replace);
                        break;
                    }
                    $j++;
                }
            }
            if (preg_match('~^\s*(?:--write_file|write_file|--append_file|-- ?perl|perl|\[mysqld]|binlog[.-])~', $row) !== 0) {
                // EOF terminated blocks
                $j = $i + 1;
                $replace = ['-- XB3 ' . $row];
                while ($j < count($rows)) {
                    $replace[] = '-- XB3 ' . $rows[$j];
                    if ($rows[$j] === 'EOF' || $rows[$j] === ' EOF') {
                        array_splice($rows, $i, $j - $i + 1, $replace);
                        break;
                    }
                    $j++;
                }
            }
            if (preg_match('~^\s*(?:echo|eval|exec|let|replace_regex)(?: |$)~i', $row) !== 0) {
                // terminated with delimiter
                $j = $i;
                $replace = ['-- XB4 ' . $row];
                while ($j < count($rows)) {
                    $replace[] = '-- XB4 ' . $rows[$j];
                    if (preg_match('~' . $quotedDelimiter . '\s*$~', $rows[$j]) !== 0) {
                        array_splice($rows, $i, $j - $i + 1, $replace);
                        break;
                    }
                    $j++;
                }
            }
            if (preg_match('~^\s*if\s*\(!?`(select.*)~i', $row, $m) !== 0) {
                // processing selects in conditions
                if (preg_match('~`\)\s*$~', $row) !== 0) {
                    $rows[$i] = substr($m[1], 0, -2) . ' -- XB5';
                } else {
                    $j = $i;
                    $rows[$i] = $m[1] . ' -- XB6';
                    while ($j < count($rows)) {
                        if (preg_match('~(.*)`\)\s*{?$~', $rows[$j], $m) !== 0) {
                            $rows[$j] = $m[1] . $delimiter . ' -- XB7';
                            break;
                        }
                        $j++;
                    }
                }
            }
            if (preg_match('~^\s*(?:--)?(?:if|while)\s*\(~i', $row) !== 0
                && (preg_match('~[{}]\s*(?:(?:#|--).*)?$~', $row) !== 0 || preg_match('~^\s*\{~', $rows[$i + 1]) !== 0)
            ) {
                // mostly perl
                array_splice($rows, $i, 1, ['-- XB8 ' . $row]);
            } elseif (preg_match('~^\s*[{}]\s*(?:#.*)?$~i', $row) !== 0 && !Str::endsWith($rows[$i - 1], "'")) {
                // brackets
                array_splice($rows, $i, 1, ['-- XB9 ' . $row]);
            } elseif (preg_match('~^\s*\{(?!["a-z])~', $row) !== 0 && !Str::endsWith($rows[$i - 1], "'")) {
                // brackets
                array_splice($rows, $i, 1, ['-- XBB ' . $row]);
            } elseif (preg_match('~^\s*(?:}(?!\')|-->(?:end)?sql|use File::|\./binlog|\.[\\\\/]master)~', $row) !== 0) {
                // start match
                array_splice($rows, $i, 1, ['-- XBA ' . $row]);
            } elseif (preg_match('~^\s*connection\s+[$a-zA-Z_\d]+' . $quotedDelimiter . '\s*($|#.*)~i', $row) !== 0) {
                // full match
                array_splice($rows, $i, 1, ['-- XBC ' . $row]);
            } elseif (preg_match('~/dev/null 2>&1 &$~', $row) !== 0) {
                // end match
                array_splice($rows, $i, 1, ['-- XBD ' . $row]);
            } elseif (preg_match('~^\s*--(?:' . $this->commands . '|send)~', $row) !== 0) {
                // perl commands
                array_splice($rows, $i, 1, ['-- XBE ' . $row]);
            } elseif (preg_match('~^\s*(?:' . $this->commands . ')(?:[^\w:]+.*|$)~', $row) !== 0) {
                // perl commands
                array_splice($rows, $i, 1, ['-- XBF ' . $row]);
            } elseif (preg_match('~\s*send;~i', $row) !== 0) {
                // lonely send
                array_splice($rows, $i, 1, ['-- XBG ' . $row]);
            }

            // flip rows
            if ($rows[$i] === 'DELIMITER // -- XBZ DELIMITER //') {
                if (strpos($rows[$i - 1], '-- error') === 0) {
                    $rows[$i] = $rows[$i - 1];
                    $rows[$i - 1] = 'DELIMITER // -- XBY DELIMITER //';
                }
            }

            $i++;
        }

        return implode("\n", $rows);
    }

    private function translateErrorCodes(string $error): string
    {
        $codes = explode(',', trim($error, ' ;'));
        foreach ($codes as $i => $code) {
            if (isset($this->errorCodes[(int) $code])) {
                $codes[$i] = $this->errorCodes[(int) $code];
            }
        }

        return implode(',', $codes);
    }

}
