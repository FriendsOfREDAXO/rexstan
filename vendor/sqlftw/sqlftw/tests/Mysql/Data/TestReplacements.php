<?php declare(strict_types = 1);

// spell-check-ignore: DET FC charbinary crc doesn storedproc usexxx xplugin

namespace SqlFtw\Tests\Mysql\Data;

trait TestReplacements
{

    /** @var array<string, array<string, string>> */
    private static array $replacements = [
        // some test fixtures
        '_alter.test' => ['#DET#' => ''],
        'gr_xplugin_global_variables.test' => ['%XCOM_PORT%' => '1234'],
        'undo_tablespace_win.test' => ["use Win32API" => "usexxx Win32API"],

        // unrecognized error test
        'storedproc.test' => [
            "set @@sql_mode = 'ansi, error_for_division_by_zero';" => "--error ER_\nset @@sql_mode = 'ansi, error_for_division_by_zero';",
            "DROP PROCEDURE IF EXISTSsp1;" => "DROP PROCEDURE IF EXISTS sp1;",
        ],
        // missing \n
        'events_2.test' => [
            'end|                                                                                                                                                    --error ER_EVENT_RECURSION_FORBIDDEN' => "end|\n--error ER_EVENT_RECURSION_FORBIDDEN",
        ],

        // broken error checks
        'gcol_insert_ignore.test' => [
            "error 0\n      ,ER_BLOB_KEY_WITHOUT_LENGTH\n      ,ER_UNSUPPORTED_ACTION_ON_GENERATED_COLUMN\n      ,ER_JSON_USED_AS_KEY\n    ;" => "error 0,ER_BLOB_KEY_WITHOUT_LENGTH,ER_UNSUPPORTED_ACTION_ON_GENERATED_COLUMN,ER_JSON_USED_AS_KEY;",
            "error 0\n      ,ER_WRONG_SUB_KEY\n      ,ER_UNSUPPORTED_ACTION_ON_GENERATED_COLUMN\n      ,ER_JSON_USED_AS_KEY\n    ;" => "error 0,ER_WRONG_SUB_KEY,ER_UNSUPPORTED_ACTION_ON_GENERATED_COLUMN,ER_JSON_USED_AS_KEY;",
            "error 0\n      ,ER_INVALID_JSON_TEXT\n      ,ER_INVALID_JSON_CHARSET\n      ,ER_CANT_CREATE_GEOMETRY_OBJECT\n    ;" => "error 0,ER_INVALID_JSON_TEXT,ER_INVALID_JSON_CHARSET,ER_CANT_CREATE_GEOMETRY_OBJECT;",
            "error 0\n      ,ER_INVALID_JSON_VALUE_FOR_CAST\n      ,ER_TRUNCATED_WRONG_VALUE_FOR_FIELD\n      ,ER_TRUNCATED_WRONG_VALUE\n    ;" => "error 0,ER_INVALID_JSON_VALUE_FOR_CAST,ER_TRUNCATED_WRONG_VALUE_FOR_FIELD,ER_TRUNCATED_WRONG_VALUE;",
        ],

        // broken strings
        'innochecksum.test' => [
            "doesn't" => 'does not',
            "Error while setting value \'strict_innodb\' to \'strict-check\'" => "Error while setting value strict_innodb to strict-check",
            "Error while setting value \'strict_crc32\' to \'strict-check\'" => "Error while setting value strict_crc32 to strict-check",
            "Error while setting value \'strict_none\' to \'strict-check\'" => "Error while setting value strict_none to strict-check",
            "Error while setting value \'InnoBD\' to \'strict-check\'" => "Error while setting value InnoBD to strict-check",
            "Error while setting value \'crc\' to \'strict-check\'" => "Error while setting value crc to strict-check",
            "Error while setting value \'no\' to \'strict-check\'" => "Error while setting value no to strict-check",
            "Error while setting value \'strict_crc32\' to \'write\'" => "Error while setting value strict_crc32 to write",
            "Error while setting value \'strict_innodb\' to \'write\'" => "Error while setting value strict_innodb to write",
            "Error while setting value \'crc23\' to \'write\'" => "Error while setting value crc23 to write",
        ],
        'log_encrypt_kill.test' => ["let SEARCH_PATTERN=Can\\'t" => 'let SEARCH_PATTERN=Cant'],
        'array_index.test' => ["Lookups of single SON null value can't use index" => 'Lookups of single SON null value cant use index'],
        'ndb_rpl_conflict_epoch2.test' => ['\\"' => '"'],
        'ndb_rpl_conflict_epoch2_trans.test' => ['\\"' => '"'],

        // broken delimiters
        'json_no_table.test' => ['execute s1 using @x # OK - returns ["a", "b"] and ["c", "a"];' => 'execute s1 using @x; # OK - returns ["a", "b"] and ["c", "a"]'],
        'ndb_alter_table_column_online.test' => ["name like '%t1%'#and type like '%UserTable%';" => "name like '%t1%';#and type like '%UserTable%'"],
        'rpl_temporary.test' => ['insert into t1 select * from `\E4\F6\FC\C4\D6\DC`' => 'insert into t1 select * from `\E4\F6\FC\C4\D6\DC`;'],
        'rpl_user_variables.test' => ['CREATE FUNCTION f1() RETURNS INT RETURN @a; DELIMITER |; CREATE' => "CREATE FUNCTION f1() RETURNS INT RETURN @a;\nDELIMITER |;\nCREATE"],
        'rpl_multi_source_cmd_errors.test' => ['START SLAVE UNTIL SOURCE_LOG_FILE = "dummy-bin.0000001", SOURCE_LOG_POS = 1729' => 'START SLAVE UNTIL SOURCE_LOG_FILE = "dummy-bin.0000001", SOURCE_LOG_POS = 1729;'],

        // too much hustle to filter out
        'func_misc.test' => ["if (!` SELECT (@sleep_time_per_result_row * @row_count - @max_acceptable_delay >\n              @sleep_time_per_result_row) AND (@row_count - 1 >= 3)`)" => 'if (XXX)'],

        // fucking includes :E
        'ndb_native_default_support.test' => ['--source suite/ndb/include/turn_off_strict_sql_mode.inc' => "set sql_mode=(select replace(@@sql_mode,'STRICT_TRANS_TABLES',''));"],
        'ndb_replace.test' => ['--source suite/ndb/include/turn_off_strict_sql_mode.inc' => "set sql_mode=(select replace(@@sql_mode,'STRICT_TRANS_TABLES',''));"],
        'ndb_restore_conv_lossy_charbinary.test' => ['--source suite/ndb/include/turn_off_strict_sql_mode.inc' => "set sql_mode=(select replace(@@sql_mode,'STRICT_TRANS_TABLES',''));"],
        'ndb_restore_conv_lossy_integral.test' => ['--source suite/ndb/include/turn_off_strict_sql_mode.inc' => "set sql_mode=(select replace(@@sql_mode,'STRICT_TRANS_TABLES',''));"],
        'ndb_restore_conv_padding.test' => ['--source suite/ndb/include/turn_off_strict_sql_mode.inc' => "set sql_mode=(select replace(@@sql_mode,'STRICT_TRANS_TABLES',''));"],
        'ndb_row_format.test' => ['--source suite/ndb/include/turn_off_strict_sql_mode.inc' => "set sql_mode=(select replace(@@sql_mode,'STRICT_TRANS_TABLES',''));"],
        'ndb_update_no_read.test' => ['--source suite/ndb/include/turn_off_strict_sql_mode.inc' => "set sql_mode=(select replace(@@sql_mode,'STRICT_TRANS_TABLES',''));"],
    ];

}
