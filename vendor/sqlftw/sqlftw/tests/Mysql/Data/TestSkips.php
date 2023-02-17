<?php declare(strict_types = 1);

// spell-check-ignore: charlength

namespace SqlFtw\Tests\Mysql\Data;

trait TestSkips
{

    /** @var list<string> */
    private static array $skips = [
        // todo: problems with �\' in shift-jis encoding. need to tokenize strings per-character not per-byte
        'jp_alter_sjis.test',
        'jp_charlength_sjis.test',
        'jp_create_db_sjis.test',
        'jp_create_tbl_sjis.test',
        'jp_enum_sjis.test',
        'jp_insert_sjis.test',
        'jp_instr_sjis.test',
        'jp_join_sjis.test',
        'jp_left_sjis.test',
        'jp_length_sjis.test',
        'jp_like_sjis.test',
        'jp_locate_sjis.test',
        'jp_lpad_sjis.test',
        'jp_ltrim_sjis.test',
        'jp_ps_sjis.test',
        'jp_replace_sjis.test',
        'jp_reverse_sjis.test',
        'jp_right_sjis.test',
        'jp_rpad_sjis.test',
        'jp_rtrim_sjis.test',
        'jp_subquery_sjis.test',
        'jp_substring_sjis.test',
        'jp_update_sjis.test',
        'jp_where_sjis.test',
        'ctype_sjis.test',

        // todo: problems with gb18030 encoding. need to tokenize strings per-character not per-byte
        'ctype_gb18030_encoding_cn.test',

        // won't fix - invalid combination of SQL and Perl comment directives
        'binlog_start_comment.test',

        // need to parse some special syntax in string argument
        'debug_sync.test',

        // Heatwave analytics plugin
        'secondary_engine',

        // this test for a bug contains another bug. we are implementing correct behavior (nested comments)
        'innodb_bug48024.test',

        // badly named result file
        'r/server_offline_7.test',

        // no significant SQL to test, but problems...
        'suite/stress/t/wrapper.test',
        'binlog_expire_warnings.test',
        'binlog_gtid_accessible_table_with_readonly.test',
        'mysqltest.test',
        'charset_master.test',
    ];

}
