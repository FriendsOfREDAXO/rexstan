<?php declare(strict_types = 1);

// spell-check-ignore: 7where abc autocommitted bb blabla cblob cenum cint cset cvarchar dat dbb dbc dbd defaulte denormal divoire dml ibd idx initfiledb3 innr1 innr2 innrcte jt latin2mysql latin2mysqlcollate maccecollate macromancollate masterreplicate mno modifyowner mtr mv mysqlttest1 nabc ncote nj nx outr1 outr2 outr3 outrcte pmax russian selecta spexecute11 spexecute64 t1values t2values t3values tcnt tde ucs2collate wp xfcller xffe xfff yextent youre zip1k zip2k zip4k zip8k fffd
// spell-check-ignore: bka bnl dupsweedout firstmatch icp intoexists loosescan qb

namespace SqlFtw\Tests\Mysql\Data;

trait SerialisationExceptions
{

    /** @var array<string, string> */
    private static array $exceptions = [
        "explain t1;" => "describe t1;",
        "explain v1;" => "describe v1;",
        "explain t2;" => "describe t2;",
        "begin|" => "start transaction|",

        // no whitespace
        "execute stmt5 using@val;" => "execute stmt5 using @val;",
        "insert t1values(1);" => "insert t1 values(1);",
        "insert t2values(1);" => "insert t2 values(1);",
        "insert t3values(1);" => "insert t3 values(1);",
        "update t1 set b=7where c=4;" => "update t1 set b=7 where c=4;",
        "create tablespace encrypt_ts add datafile encrypt_ts.ibd engine innodb autoextend_size 12m max_size 100m nodegroup 5 wait comment tablespace encryption initial_size 100m encryption yextent_size 100m;"
            => "create tablespace encrypt_ts add datafile encrypt_ts.ibd engine innodb autoextend_size 12m max_size 100m nodegroup 5 wait comment tablespace encryption initial_size 100m encryption y extent_size 100m;",
        "insert t1 values(2 0 0 0 0 0-128 0-32768 0-2147483648 0-9223372036854775808 0 0x0 0x0 0x0 0x0 0x0 0x0);"
            => "insert t1 values(2 0 0 0 0 0-128 0-32768 0-2147483648 0-9223372036854775808 0  0x0 0x0 0x0 0x0 0x0 0x0);",
        "alter table account modifyowner int(11)unsigned not null column_format fixed;"
            => "alter table account modify owner int(11)unsigned not null column_format fixed;",
        "select _latin2mysqlcollate latin2_general_ci=mysql;" => "select _latin2mysql collate latin2_general_ci=mysql;",
        "create table t4(a char(1))character set ucs2collate ucs2_persian_ci;" => "create table t4(a char(1))character set ucs2 collate ucs2_persian_ci;",
        "create table t4(a char(1))character set macromancollate macroman_general_ci;" => "create table t4(a char(1))character set macroman collate macroman_general_ci;",
        "create table t4(a char(1))character set maccecollate macce_general_ci;" => "create table t4(a char(1))character set macce collate macce_general_ci;",
        "select regexp_instr(char(313:50:35.199734using utf16le)uuid());" => "select regexp_instr(char(313:50:35.199734 using utf16le)uuid());",
        "create procedure t1()begin declare exit handler for sqlexception selecta;insert t1 values(200);end;|"
            => "create procedure t1()begin declare exit handler for sqlexception select a;insert t1 values(200);end;|",
        "create table t1(russian enum(e f e\xfff f\xffe)not null defaulte);" => "create table t1(russian enum(e f e\xfff f\xffe)not null default e);",
        "create table t1(denormal enum(e f e f f e)not null defaulte);" => "create table t1(denormal enum(e f e f f e)not null default e);",
        "create table t1(russian_deviant enum(e f e\xfff f e)not null defaulte);" => "create table t1(russian_deviant enum(e f e\xfff f e)not null default e);",
        "select regexp_instr(char(313:50:35.199734using utf16le)cast(uuid()char character set utf16le));" // 8.0.31
            => "select regexp_instr(char(313:50:35.199734 using utf16le)cast(uuid()char character set utf16le));",

        // useless whitespace
        "grant all on*.*to x_root @10.0.2.1 with grant option;" => "grant all on*.*to x_root@10.0.2.1 with grant option;",
        "revoke all on*.*from x_root @10.0.2.1;" => "revoke all on*.*from x_root@10.0.2.1;",
        "create user some_user @localhost identified with mysql_native_password;" => "create user some_user@localhost identified with mysql_native_password;",
        "drop user some_user @localhost;" => "drop user some_user@localhost;",
        "prepare s from select ! ?;" => "prepare s from select !?;",
        "create function f1()returns int begin update v1 set fld2=b where fld1=1;return row_count();end !"
            => "create function f1()returns int begin update v1 set fld2=b where fld1=1;return row_count();end!",
        "drop procedure if exists p2 \$" => "drop procedure if exists p2\$",
        "create event teste_bug11763507 on schedule at current_timestamp+interval 1 hour do select 1 \$"
            => "create event teste_bug11763507 on schedule at current_timestamp+interval 1 hour do select 1\$",
        "create procedure proc1()begin declare dummy int unsigned;set dummy=100;alter event event1 on schedule every dummy second starts 2000-01-01 00:00:00 enable;end "
            => "create procedure proc1()begin declare dummy int unsigned;set dummy=100;alter event event1 on schedule every dummy second starts 2000-01-01 00:00:00 enable;end",
        "create user=x_root @10.0.2.1 identified with mysql_native_password;" => "create user=x_root@10.0.2.1 identified with mysql_native_password;",
        "drop user=x_root @10.0.2.1;" => "drop user=x_root@10.0.2.1;",

        // '' vs NONE
        "alter table t1 compression;" => "alter table t1 compression none;",

        // limit offset
        "prepare s from select 1 limit 1 ?;" => "prepare s from select 1 limit ? offset 1;",
        "prepare s from select 1 limit ? ?;" => "prepare s from select 1 limit ? offset ?;",
        "prepare stmt from select*from t1 limit ? ?;" => "prepare stmt from select*from t1 limit ? offset ?;",
        "prepare stmt from select*from t1 union all select*from t1 limit ? ?;" => "prepare stmt from select*from t1 union all select*from t1 limit ? offset ?;",
        "prepare stmt from(select*from t1 limit ? ?)union all(select*from t1 limit ? ?)order by a limit ?;"
            => "prepare stmt from(select*from t1 limit ? offset ?)union all(select*from t1 limit ? offset ?)order by a limit ?;",
        "create procedure p1()begin declare a integer;declare b integer;select*from t1 limit a b;end|"
            => "create procedure p1()begin declare a integer;declare b integer;select*from t1 limit b offset a;end|",
        "create procedure p1(p1 integer p2 integer)select*from t1 limit p1 p2;" => "create procedure p1(p1 integer p2 integer)select*from t1 limit p2 offset p1;",
        "create function f1()returns int begin declare a b c int;set a=(select count(*)from t1 limit b c);return a;end|"
            => "create function f1()returns int begin declare a b c int;set a=(select count(*)from t1 limit c offset b);return a;end|",
        "create function f1(p1 integer p2 integer)returns int begin declare count int;set count=(select count(*)from(select*from t1 limit p1 p2)t_1);return count;end|"
            => "create function f1(p1 integer p2 integer)returns int begin declare count int;set count=(select count(*)from(select*from t1 limit p2 offset p1)t_1);return count;end|",

        // dropped fraction in float(10.3)
        "alter table t1 modify c1 float(10.3)drop check t1_chk_1 add constraint check(c1>10.1)enforced;"
            => "alter table t1 modify c1 float(10)drop check t1_chk_1 add constraint check(c1>10.1)enforced;",

        // heatwave things
        "create table t1(f1 date not secondary);" => "create table t1(f1 date);",
        "alter table t1 modify f1 date not secondary;" => "alter table t1 modify f1 date;",
        "create table b32340208.test(pk int not null auto_increment a1 smallint(((0<>c1)and(_utf8mb40000-00-00 00:00:00<>d1)))virtual not secondary b1 char(8)default null c1 longblob not null not secondary d1 timestamp not null primary key(pk)index functional_index((radians(c1)))using btree comment youre)engine innodb character set euckr;"
            => "create table b32340208.test(pk int not null auto_increment a1 smallint(((0<>c1)and(_utf8mb40000-00-00 00:00:00<>d1)))virtual b1 char(8)default null c1 longblob not null d1 timestamp not null primary key(pk)index functional_index((radians(c1)))using btree comment youre)engine innodb character set euckr;",

        // this thing : E
        "set character set cp1250_latin2;" => "set character set cp1250;",
        "set character set cp1251_koi8;" => "set character set cp1251;",

        // bug - double ;; after END inside sp
        "create procedure p1()begin prepare stmt from create procedure p2()begin select 1;end;execute stmt;deallocate prepare stmt;end|"
            => "create procedure p1()begin prepare stmt from create procedure p2()begin select 1;end;;execute stmt;deallocate prepare stmt;end|",

        // invalid optimizer hints treated as regular comments (ignored)
        "select/*+bad_hint_also_goes_to_digest*/1;" => "select 1;",
        "select/*+*/*from t1;" => "select*from t1;",
        "select/*+bka(t1@qb1)bnl(@qb1 t1)dupsweedout firstmatch intoexists loosescan materialization mrr(t1)no_bka(t2)no_bnl(t2)no_icp(t2)no_mrr(t2)no_range_optimization(t2)no_semijoin(t2)qb_name(qb1)semijoin(t1)subquery(t1)*/*from t1 t2;" => "select*from t1 t2;",
        "alter view v1 select/*+bad_hint*/1;" => "alter view v1 select 1;",
        "create view v1 select/*+bad_hint*/1;" => "create view v1 select 1;",
        "select/*+*/a from t where a=2012-01-01 00:11:11;" => "select a from t where a=2012-01-01 00:11:11;",
        "select/*+? bad syntax*/1;" => "select 1;",
        "select/*+no_icp(t1)bad_hint*/1 from t1;" => "select 1 from t1;",
        "select/*+10*/1;" => "select 1;",
        "select 1 from dual where 1 in(select/*+debug_hint3*/1);" => "select 1 from dual where 1 in(select 1);",
        "select(select/*+debug_hint3*/1);" => "select(select 1);",
        "select*from(select/*+debug_hint3*/1)a;" => "select*from(select 1)a;",
        "explain delete from/*+test*/using t1 where i=10;" => "explain delete from t1 where i=10;",
        "explain update/*+test*/t1 set i=10 where j=10;" => "explain update t1 set i=10 where j=10;",
        "explain replace/*+test*/into t1 values(10 10);" => "explain replace t1 values(10 10);",
        "explain insert/*+test*/into t1 values(10 10);" => "explain insert t1 values(10 10);",
        "explain select/*+test*/1;" => "explain select 1;",
        "select/*+foo @bar*/1;" => "select 1;",
        "select/*+foo@bar*/1;" => "select 1;",
        "select/*+@foo*/1;" => "select 1;",
        "select/*+@*/1;" => "select 1;",
        "select/*+***/select/*+@*/1;" => "select 1;",
        "select/*+*/1;" => "select 1;",

        // todo: not yet supported
        "prepare stmt2 from select/*+max_execution_time(2)*/*sleep(0.5)from t1 where b=new_string;" => "prepare stmt2 from select*sleep(0.5)from t1 where b=new_string;",
        "prepare stmt3 from select/*+max_execution_time(3600000)*/count(*)from t1;" => "prepare stmt3 from select count(*)from t1;",

        // int limit, todo: string-int?
        "create table t1(a int)max_rows 18446744073709551615;" => "create table t1(a int)max_rows 9223372036854775807;",
        "create table t1(a int)min_rows 18446744073709551615;" => "create table t1(a int)min_rows 9223372036854775807;",
        "select*from t order by x limit 18446744073709551614;" => "select*from t order by x limit 9223372036854775807;",

        // expression starting with +/-, todo: keep original?
        "select+9999999999999999999-9999999999999999999;" => "select 9999999999999999999-9999999999999999999;",
        "select--9223372036854775808---9223372036854775808----9223372036854775808;" => "select 9223372036854775808-9223372036854775808 9223372036854775808;",

        // + in intervals
        "alter table t1 add c11 year add constraint check(c11>2007-01-01+interval+1 year);" => "alter table t1 add c11 year add constraint check(c11>2007-01-01+interval 1 year);",

        // charset introducer N'foo'
        "select nabc;" => "select abc;",
        "insert t1 values(n);" => "insert t1 values();",
        "insert t1 values(ncote divoire);" => "insert t1 values(cote divoire);",
        "select concat(a if(b>10 nx ny))from t1;" => "select concat(a if(b>10 x y))from t1;",
        "select nabc length(nabc);" => "select abc length(abc);",
        "select n length(n);" => "select length();",

        // dropped format
        "explain analyze format=tree select 1;" => "explain analyze select 1;",

        // dropped ()
        "create table t3(like t1);" => "create table t3 like t1;",

        // from from
        "prepare stmt4 from show columns from t2 from test like a%;" => "prepare stmt4 from show columns from test.t2 like a%;",
        "prepare stmt4 from show indexes from t2 from test;" => "prepare stmt4 from show indexes from test.t2;",
        "show columns from ab from mysqlttest1;" => "show columns from mysqlttest1.ab;",

        // missing into
        "insert/*+set_var(time_zone=utc)*/t1 values(timediff(current_timestamp utc_timestamp));"
            => "insert/*+set_var(time_zone=utc)*/into t1 values(timediff(current_timestamp utc_timestamp));",

        // query expressions vs into
        "(select 1 into @v);" => "(select 1)into @v;",
        "((select 1 into @v));" => "((select 1))into @v;",
        "(select 1 from t1 into @v);" => "(select 1 from t1)into @v;",
        "((select 1 from t1 into @v));" => "((select 1 from t1))into @v;",
        "select 1 union select 1 into @v from t1;" => "select 1 union select 1 from t1 into @v;",
        "(select 1)union select 1 into @v from t1;" => "(select 1)union select 1 from t1 into @v;",
        "(select 1)union(select 1 into @v from t1);" => "(select 1)union(select 1 from t1)into @v;",
        "((select 1)union(select 1 into @v from t1));" => "((select 1)union(select 1 from t1))into @v;",
        "select 1 union(select 2 into outfile parser.test.file2);" => "select 1 union(select 2)into outfile parser.test.file2;",
        "(select 1)union(select 2 into outfile parser.test.file4);" => "(select 1)union(select 2)into outfile parser.test.file4;",
        "((select 1)union(select 2 into outfile parser.test.file5));" => "((select 1)union(select 2))into outfile parser.test.file5;",
        "(select 1 union select 1 into @var);" => "(select 1 union select 1)into @var;",
        "select 1 union select 1 into @var from dual;" => "select 1 union select 1 from dual into @var;",
        "select 1 union(select 1 into @var from dual);" => "select 1 union(select 1 from dual)into @var;",
        "(select 1 union select 1 for update into @var);" => "(select 1 union select 1 for update)into @var;",
        "(select 1 union select 1 from dual into @var);" => "(select 1 union select 1 from dual)into @var;",
        "(select 1 union select 1 from dual for update into @var);" => "(select 1 union select 1 from dual for update)into @var;",
        "(select 1)union(select 1 into @var);" => "(select 1)union(select 1)into @var;",
        "select a from t1 union select a into @v from t1;" => "select a from t1 union select a from t1 into @v;",
        "select a from t1 union select a into outfile union.out.file5 from t1;" => "select a from t1 union select a from t1 into outfile union.out.file5;",
        "select a from t1 union select a into outfile union.out.file6 from t1;" => "select a from t1 union select a from t1 into outfile union.out.file6;",
        "select 1 from dual limit 1 for update into @var;" => "select 1 from dual limit 1 into @var for update;",

        // := vs =
        "load data infile../../std_data/words.dat into table t1(a)set b:=f1();" => "load data infile../../std_data/words.dat into table t1(a)set b=f1();",
        "create procedure p(p_num int)begin declare v_i int default 0;repeat update t set b:=repeat(a floor(1024*1024*4));update t set b:=;set v_i:=v_i+1;until v_i>p_num end repeat;end\$"
            => "create procedure p(p_num int)begin declare v_i int default 0;repeat update t set b=repeat(a floor(1024*1024*4));update t set b=;set v_i:=v_i+1;until v_i>p_num end repeat;end\$",
        "create procedure p(p_num int)begin declare v_i int default 0;repeat update t set b:=repeat(a 1024*1024*4);update t set b:=repeat(b 1024*1024*4);set v_i:=v_i+1;until v_i>p_num end repeat;end\$"
            => "create procedure p(p_num int)begin declare v_i int default 0;repeat update t set b=repeat(a 1024*1024*4);update t set b=repeat(b 1024*1024*4);set v_i:=v_i+1;until v_i>p_num end repeat;end\$",

        // mangled after removing "'"
        "set @a=_cp850 m\xfcller collate cp850_general_ci;" => "set @a=_cp850m\xfcller collate cp850_general_ci;",

        // eliminated --
        "select 6e-16-6e-16--6e-16-6e-16+1.000000;" => "select 6e-16-6e-16 6e-16-6e-16+1.000000;",
        "create procedure spexecute11()begin declare var1 decimal(0);declare var2 decimal(0);declare var3 bigint;declare var4 bigint;declare var5 bigint;declare var6 bigint;declare var7 bigint;declare var8 bigint;set var1=--1.00e+09;set var3=-9.22e+18;set var5=-9.22e+18;set var7=-9.22e+18;call sp11(--1.00e+09 var1 var2-9.22e+18 var3 var4-9.22e+18 var5 var6-9.22e+18 var7 var8);select var1 var2 var3 var4 var5 var6 var7 var8;end//"
            => "create procedure spexecute11()begin declare var1 decimal(0);declare var2 decimal(0);declare var3 bigint;declare var4 bigint;declare var5 bigint;declare var6 bigint;declare var7 bigint;declare var8 bigint;set var1=1.00e+09;set var3=-9.22e+18;set var5=-9.22e+18;set var7=-9.22e+18;call sp11(1.00e+09 var1 var2-9.22e+18 var3 var4-9.22e+18 var5 var6-9.22e+18 var7 var8);select var1 var2 var3 var4 var5 var6 var7 var8;end//",
        "create procedure spexecute64()begin declare var1 decimal;declare var2 decimal;declare var3 decimal;declare var4 decimal;declare var5 decimal;declare var6 decimal;declare var7 decimal;declare var8 decimal;set var1=--1.00e+09;set var3=--1.00e+09;set var5=--1.00e+09;set var7=--1.00e+09;call sp64(--1.00e+09 var1 var2--1.00e+09 var3 var4--1.00e+09 var5 var6--1.00e+09 var7 var8);select var1 var2 var3 var4 var5 var6 var7 var8;end//"
            => "create procedure spexecute64()begin declare var1 decimal;declare var2 decimal;declare var3 decimal;declare var4 decimal;declare var5 decimal;declare var6 decimal;declare var7 decimal;declare var8 decimal;set var1=1.00e+09;set var3=1.00e+09;set var5=1.00e+09;set var7=1.00e+09;call sp64(1.00e+09 var1 var2 1.00e+09 var3 var4 1.00e+09 var5 var6 1.00e+09 var7 var8);select var1 var2 var3 var4 var5 var6 var7 var8;end//",

        // nchar
        "create table t1(c nchar varchar(10));" => "create table t1(c varchar(10));",

        // removed conditional comments
        "prepare stmt from insert/*!99999 blabla*/t1 values(60)/*!99999(61)*/;" => "prepare stmt from insert t1 values(60);",
        "prepare stmt from insert/*!99999 blabla*/t1 values(?)/*!99999(63)*/;" => "prepare stmt from insert t1 values(?);",

        // duplicated enum/set value
        "create table t17(c1 set(a b a b));" => "create table t17(c1 set(a b));",
        "create table t2(cenum enum(a a)cset set(b b));" => "create table t2(cenum enum(a)cset set(b));",
        "create table t3(cenum enum(a a a c c)cset set(b b b d d));" => "create table t3(cenum enum(a c)cset set(b d));",
        "create table t1(c enum(a a)binary);" => "create table t1(c enum(a)binary);", // todo: ok with BINARY?
        "create table t1(s set(a a)character set latin1 collate latin1_bin);" => "create table t1(s set(a)character set latin1 collate latin1_bin);",
        "create procedure sp1(f1 enum(value1 value1))language sql not deterministic sql security invoker comment this is simple begin select f1;end//"
            => "create procedure sp1(f1 enum(value1))language sql not deterministic sql security invoker comment this is simple begin select f1;end//",
        "create procedure sp1(f1 set(value1 value1))language sql not deterministic sql security invoker comment this is simple begin select f1;end//"
            => "create procedure sp1(f1 set(value1))language sql not deterministic sql security invoker comment this is simple begin select f1;end//",
        "create function fn1(f1 enum(value1 value1))returns decimal(63 30)language sql not deterministic sql security invoker comment this is simple begin return f1;end//"
            => "create function fn1(f1 enum(value1))returns decimal(63 30)language sql not deterministic sql security invoker comment this is simple begin return f1;end//",
        "create function fn1(f1 set(value1 value1))returns decimal(63 30)language sql not deterministic sql security invoker comment this is simple begin return f1;end//"
            => "create function fn1(f1 set(value1))returns decimal(63 30)language sql not deterministic sql security invoker comment this is simple begin return f1;end//",
        "create table t1(a enum(\u{fffd} \u{fffd} \u{fffd})character set utf8 not null default \u{fffd} b enum(one two)character set utf8 c enum(one two));" // 8.0.31
            => "create table t1(a enum(\u{fffd})character set utf8 not null default \u{fffd} b enum(one two)character set utf8 c enum(one two));",

        // set limit
        "create table t1(f1 set(1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 33 34 35 36 37 38 39 40 41 42 43 44 45 46 47 48 49 50 51 52 53 54 55 56 57 58 59 60 61 62 63 64 1));"
            => "create table t1(f1 set(1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 33 34 35 36 37 38 39 40 41 42 43 44 45 46 47 48 49 50 51 52 53 54 55 56 57 58 59 60 61 62 63 64));",

        // other duplicities
        "create table t1(c1 varchar(33)index(c1)using btree using hash)engine memory;" => "create table t1(c1 varchar(33)index(c1)using hash)engine memory;",
        "create table t1(c1 varchar(33)index(c1)using hash using btree)engine memory;" => "create table t1(c1 varchar(33)index(c1)using btree)engine memory;",
        "alter table t1 character set utf8 character set utf8;" => "alter table t1 character set utf8;",
        "create table t1(i int)engine innodb pack_keys 0 pack_keys 1 stats_persistent 0 stats_persistent 1 checksum 0 checksum 1 delay_key_write 0 delay_key_write 1;"
            => "create table t1(i int)engine innodb pack_keys 1 stats_persistent 1 checksum 1 delay_key_write 1;",
        "alter table t1 pack_keys 1 pack_keys 0 stats_persistent 1 stats_persistent 0 checksum 1 checksum 0 delay_key_write 1 delay_key_write 0;"
            => "alter table t1 pack_keys 0 stats_persistent 0 checksum 0 delay_key_write 0;",
        "change replication filter replicate_do_db=(db1 db2 db32 db 3)replicate_do_db=(:test my_db3 my_db4)replicate_ignore_db=(my_initfiledb3)for channel;"
            => "change replication filter replicate_do_db=(:test my_db3 my_db4)replicate_ignore_db=(my_initfiledb3)for channel;",
        "change replication filter replicate_do_db=()replicate_do_db=()replicate_ignore_db=()for channel;"
            => "change replication filter replicate_do_db=()replicate_ignore_db=()for channel;",
        "change replication filter replicate_do_db=(db1 db2 db32 db 3)replicate_do_db=(:test my_db3 my_db4)replicate_ignore_db=(my_initfiledb3);"
            => "change replication filter replicate_do_db=(:test my_db3 my_db4)replicate_ignore_db=(my_initfiledb3);",
        "change replication filter replicate_do_db=(db2 db32 db 3)replicate_do_db=(db1 my_db3 my_db4)replicate_ignore_db=(my_initfiledb3)for channel channel_1;"
            => "change replication filter replicate_do_db=(db1 my_db3 my_db4)replicate_ignore_db=(my_initfiledb3)for channel channel_1;",
        "change replication filter replicate_do_db=(dbb)replicate_ignore_db=(dbc)replicate_ignore_db=(dbd);"
            => "change replication filter replicate_do_db=(dbb)replicate_ignore_db=(dbd);",
        "change replication source to ignore_server_ids=(110 220 330 420)ignore_server_ids=(110 220 330 420);" => "change replication source to ignore_server_ids=(110 220 330 420);",
        "change replication source to ignore_server_ids=(110 220 330 420)ignore_server_ids=(111 220 330 420);" => "change replication source to ignore_server_ids=(111 220 330 420);",
        "change replication source to ignore_server_ids=(110 220 330 420)ignore_server_ids=(111 221 330 420);" => "change replication source to ignore_server_ids=(111 221 330 420);",
        "change replication source to ignore_server_ids=(110 220 330 420)ignore_server_ids=(111 221 331 420);" => "change replication source to ignore_server_ids=(111 221 331 420);",
        "change replication source to ignore_server_ids=(110 220 330 420)ignore_server_ids=(111 221 331 421);" => "change replication source to ignore_server_ids=(111 221 331 421);",
        "create index r_index on t1(a)using rtree using btree;" => "create index r_index on t1(a)using btree;",
        "create table t1(id char(40)default 4 default 5);" => "create table t1(id char(40)default 5);",
        "alter table t1 algorithm copy lock none algorithm default lock exclusive algorithm inplace lock shared;" => "alter table t1 lock exclusive algorithm inplace;",
        "create index j_index on t1(j)key_block_size 1 key_block_size 1;" => "create index j_index on t1(j)key_block_size 1;",
        "create index k_index2 on t1(k)comment a comment comment another comment;" => "create index k_index2 on t1(k)comment another comment;",
        "create index n_index on t1(n)using btree using btree;" => "create index n_index on t1(n)using btree;",
        "create index o_index on t1(o)using rtree using btree;" => "create index o_index on t1(o)using btree;",

        // persist vs @@persist etc.
        "set @@persist.max_heap_table_size=999424 replica_net_timeout=124;" => "set @@persist.max_heap_table_size=999424 @@persist.replica_net_timeout=124;",
        "set @@persist.max_heap_table_size=default replica_net_timeout=default;" => "set @@persist.max_heap_table_size=default @@persist.replica_net_timeout=default;",
        "set @@global.max_heap_table_size=999424 replica_net_timeout=124;" => "set @@global.max_heap_table_size=999424 @@global.replica_net_timeout=124;",
        "set @@global.replication_optimize_for_static_plugin_config=1 replication_sender_observe_commit_only=1;"
            => "set @@global.replication_optimize_for_static_plugin_config=1 @@global.replication_sender_observe_commit_only=1;",
        "set @@global.replication_optimize_for_static_plugin_config=0 replication_sender_observe_commit_only=0;"
            => "set @@global.replication_optimize_for_static_plugin_config=0 @@global.replication_sender_observe_commit_only=0;",
        "set @@persist.max_user_connections=10 persist max_allowed_packet=8388608;" => "set @@persist.max_user_connections=10 @@persist.max_allowed_packet=8388608;",
        "set @@persist.autocommit=0 global max_user_connections=10;" => "set @@persist.autocommit=0 @@global.max_user_connections=10;",
        "set @@global.autocommit=0 persist max_user_connections=10;" => "set @@global.autocommit=0 @@persist.max_user_connections=10;",
        "set @@persist.autocommit=0 session auto_increment_offset=10;" => "set @@persist.autocommit=0 auto_increment_offset=10;",
        "set auto_increment_offset=20 persist max_user_connections=10;" => "set auto_increment_offset=20 @@persist.max_user_connections=10;",
        "set @@persist.autocommit=0 auto_increment_offset=10;" => "set @@persist.autocommit=0 @@persist.auto_increment_offset=10;",
        "set autocommit=0 persist auto_increment_offset=10;" => "set autocommit=0 @@persist.auto_increment_offset=10;",
        "set @@persist.autocommit=0 session auto_increment_offset=10 global max_error_count=128;"
            => "set @@persist.autocommit=0 auto_increment_offset=10 @@global.max_error_count=128;",
        "set autocommit=0 global auto_increment_offset=10 persist max_allowed_packet=8388608;"
            => "set autocommit=0 @@global.auto_increment_offset=10 @@persist.max_allowed_packet=8388608;",
        "set @@global.autocommit=0 persist auto_increment_offset=10 session max_error_count=128;"
            => "set @@global.autocommit=0 @@persist.auto_increment_offset=10 max_error_count=128;",
        "set @@persist.sort_buffer_size=156000 max_connections=52;" => "set @@persist.sort_buffer_size=156000 @@persist.max_connections=52;",
        "set @@persist.max_heap_table_size=887808 replica_net_timeout=160;" => "set @@persist.max_heap_table_size=887808 @@persist.replica_net_timeout=160;",
        "set @@persist.autocommit=0 innodb_deadlock_detect=off;" => "set @@persist.autocommit=0 @@persist.innodb_deadlock_detect=off;",
        "set @@persist.auto_increment_increment=4 auto_increment_offset=2;" => "set @@persist.auto_increment_increment=4 @@persist.auto_increment_offset=2;",
        "set @@persist.binlog_error_action=ignore_error binlog_format=row;" => "set @@persist.binlog_error_action=ignore_error @@persist.binlog_format=row;",
        "set @@persist.cte_max_recursion_depth=4294967295 eq_range_index_dive_limit=4294967295;"
            => "set @@persist.cte_max_recursion_depth=4294967295 @@persist.eq_range_index_dive_limit=4294967295;",
        "set @@global.optimizer_trace_offset=default activate_all_roles_on_login=default auto_increment_increment=default auto_increment_offset=default binlog_error_action=default binlog_format=default cte_max_recursion_depth=default eq_range_index_dive_limit=default innodb_monitor_disable=default innodb_max_dirty_pages_pct=default init_connect=default max_join_size=default;"
            => "set @@global.optimizer_trace_offset=default @@global.activate_all_roles_on_login=default @@global.auto_increment_increment=default @@global.auto_increment_offset=default @@global.binlog_error_action=default @@global.binlog_format=default @@global.cte_max_recursion_depth=default @@global.eq_range_index_dive_limit=default @@global.innodb_monitor_disable=default @@global.innodb_max_dirty_pages_pct=default @@global.init_connect=default @@global.max_join_size=default;",
        "set @@global.max_join_size=default init_connect=default;" => "set @@global.max_join_size=default @@global.init_connect=default;",
        "set @@persist_only.ft_query_expansion_limit=80 innodb_api_enable_mdl=1;" => "set @@persist_only.ft_query_expansion_limit=80 @@persist_only.innodb_api_enable_mdl=1;",
        "set @@global.sort_buffer_size=default max_connections=default replica_net_timeout=default max_heap_table_size=default;"
            => "set @@global.sort_buffer_size=default @@global.max_connections=default @@global.replica_net_timeout=default @@global.max_heap_table_size=default;",
        "set @@global.innodb_strict_mode=default innodb_lock_wait_timeout=default myisam_repair_threads=default myisam_stats_method=default;"
            => "set @@global.innodb_strict_mode=default @@global.innodb_lock_wait_timeout=default @@global.myisam_repair_threads=default @@global.myisam_stats_method=default;",
        "set @@global.max_connections=default replica_net_timeout=default max_heap_table_size=default;"
            => "set @@global.max_connections=default @@global.replica_net_timeout=default @@global.max_heap_table_size=default;",
        "set @@persist.innodb_checksum_algorithm=strict_crc32 persist innodb_default_row_format=compact persist sql_mode=ansi_quotes persist innodb_fast_shutdown=0;"
            => "set @@persist.innodb_checksum_algorithm=strict_crc32 @@persist.innodb_default_row_format=compact @@persist.sql_mode=ansi_quotes @@persist.innodb_fast_shutdown=0;",
        "set @@persist.innodb_flush_log_at_trx_commit=0 join_buffer_size=262144;" => "set @@persist.innodb_flush_log_at_trx_commit=0 @@persist.join_buffer_size=262144;",
        "set @@persist.ft_boolean_syntax=+-><()~*:&|persist log_error_services=default;" => "set @@persist.ft_boolean_syntax=+-><()~*:&|@@persist.log_error_services=default;",
        "set @@persist.log_output=default persist general_log=default;" => "set @@persist.log_output=default @@persist.general_log=default;",
        "set @@persist.block_encryption_mode=default persist ft_boolean_syntax=default persist innodb_checksum_algorithm default persist log_error_services=default persist innodb_max_dirty_pages_pct=default;"
            => "set @@persist.block_encryption_mode=default @@persist.ft_boolean_syntax=default @@persist.innodb_checksum_algorithm=default @@persist.log_error_services=default @@persist.innodb_max_dirty_pages_pct=default;",
        "set @@persist.innodb_fast_shutdown=default persist innodb_default_row_format=default persist sql_mode=default persist innodb_flush_log_at_trx_commit=default persist max_connections=default persist join_buffer_size=default persist innodb_flush_sync=default persist innodb_io_capacity=default persist log_bin_trust_function_creators=default persist autocommit=default;"
            => "set @@persist.innodb_fast_shutdown=default @@persist.innodb_default_row_format=default @@persist.sql_mode=default @@persist.innodb_flush_log_at_trx_commit=default @@persist.max_connections=default @@persist.join_buffer_size=default @@persist.innodb_flush_sync=default @@persist.innodb_io_capacity=default @@persist.log_bin_trust_function_creators=default @@persist.autocommit=default;",
        "set @@persist_only.innodb_log_file_size=4194304 ft_query_expansion_limit=80;" => "set @@persist_only.innodb_log_file_size=4194304 @@persist_only.ft_query_expansion_limit=80;",
        "set @@global.sort_buffer_size=default max_heap_table_size=default replica_net_timeout=default long_query_time=default;"
            => "set @@global.sort_buffer_size=default @@global.max_heap_table_size=default @@global.replica_net_timeout=default @@global.long_query_time=default;",
        "set @@global.gtid_mode=1 gtid_mode=2 enforce_gtid_consistency=on gtid_mode=3;"
            => "set @@global.gtid_mode=1 @@global.gtid_mode=2 @@global.enforce_gtid_consistency=on @@global.gtid_mode=3;",
        "select argument from mysql.general_log where argument like set @@persist.%;" => "select argument from mysql.general_log where argument like set persist%;",
        "set @@persist_only.max_user_connections=10 persist_only max_allowed_packet=8388608;"
            => "set @@persist_only.max_user_connections=10 @@persist_only.max_allowed_packet=8388608;",
        "set @@persist_only.autocommit=0 global max_user_connections=10;" => "set @@persist_only.autocommit=0 @@global.max_user_connections=10;",
        "set @@global.autocommit=0 persist_only max_user_connections=10;" => "set @@global.autocommit=0 @@persist_only.max_user_connections=10;",
        "set @@persist_only.autocommit=0 session auto_increment_offset=10;" => "set @@persist_only.autocommit=0 auto_increment_offset=10;",
        "set auto_increment_offset=20 persist_only max_user_connections=10;" => "set auto_increment_offset=20 @@persist_only.max_user_connections=10;",
        "set @@persist_only.autocommit=0 auto_increment_offset=10;" => "set @@persist_only.autocommit=0 @@persist_only.auto_increment_offset=10;",
        "set autocommit=0 persist_only auto_increment_offset=10;" => "set autocommit=0 @@persist_only.auto_increment_offset=10;",
        "set @@persist_only.autocommit=0 session auto_increment_offset=10 global max_error_count=128;"
            => "set @@persist_only.autocommit=0 auto_increment_offset=10 @@global.max_error_count=128;",
        "set autocommit=0 global auto_increment_offset=10 persist_only max_allowed_packet=8388608;"
            => "set autocommit=0 @@global.auto_increment_offset=10 @@persist_only.max_allowed_packet=8388608;",
        "set @@global.autocommit=0 persist_only auto_increment_offset=10 session max_error_count=128;"
            => "set @@global.autocommit=0 @@persist_only.auto_increment_offset=10 max_error_count=128;",
        "set @@persist_only.autocommit=0 session auto_increment_offset=10 global max_error_count=128 persist sort_buffer_size=256000;"
            => "set @@persist_only.autocommit=0 auto_increment_offset=10 @@global.max_error_count=128 @@persist.sort_buffer_size=256000;",
        "set autocommit=0 global auto_increment_offset=10 persist_only max_allowed_packet=8388608 persist max_heap_table_size=999424;"
            => "set autocommit=0 @@global.auto_increment_offset=10 @@persist_only.max_allowed_packet=8388608 @@persist.max_heap_table_size=999424;",
        "set @@global.autocommit=0 persist long_query_time=8.3452 persist_only auto_increment_offset=10 session max_error_count=128;"
            => "set @@global.autocommit=0 @@persist.long_query_time=8.3452 @@persist_only.auto_increment_offset=10 max_error_count=128;",
        "set @@persist_only.ft_query_expansion_limit=200 innodb_api_enable_mdl=0;" => "set @@persist_only.ft_query_expansion_limit=200 @@persist_only.innodb_api_enable_mdl=0;",
        "set @@persist_only.innodb_log_file_size=4194304 log_replica_updates=1;" => "set @@persist_only.innodb_log_file_size=4194304 @@persist_only.log_replica_updates=1;",
        "set @@persist_only.log_replica_updates=1 super_read_only=1 end_markers_in_json=1;"
            => "set @@persist_only.log_replica_updates=1 @@persist_only.super_read_only=1 @@persist_only.end_markers_in_json=1;",
        "set @@global.long_query_time=default max_connections=default max_heap_table_size=default replica_net_timeout=default sort_buffer_size=default join_buffer_size=default;"
            => "set @@global.long_query_time=default @@global.max_connections=default @@global.max_heap_table_size=default @@global.replica_net_timeout=default @@global.sort_buffer_size=default @@global.join_buffer_size=default;",
        "set default_storage_engine=myisam default_storage_engine=heap global default_storage_engine=merge;"
            => "set default_storage_engine=myisam default_storage_engine=heap @@global.default_storage_engine=merge;",
        "set @@global.net_retry_count=10 session net_retry_count=10;" => "set @@global.net_retry_count=10 net_retry_count=10;",
        "set @@global.net_buffer_length=1024 net_write_timeout=200 net_read_timeout=300;"
            => "set @@global.net_buffer_length=1024 @@global.net_write_timeout=200 @@global.net_read_timeout=300;",
        "set @@global.net_buffer_length=8000 global net_read_timeout=900 net_write_timeout=1000;"
            => "set @@global.net_buffer_length=8000 @@global.net_read_timeout=900 @@global.net_write_timeout=1000;",
        "insert statements(comment connection statement gtid_next gtid_owned anonymous_count sync_point error)values(nothing should be owned by default.1 automatic 0)(set gtid_next(automatic->anonymous)acquires ownership.1 set gtid_next=anonymous anonymous anonymous 1)(implicit commit releases ownership.1 create table t1(a int)anonymous 0)(implicitly committing statement re-acquires ownership.1 create table t2(a int)1->before_execute_sql_command)(implicitly committing statement releases ownership at the end.1 #create table t2(a int)anonymous 0 before_execute_sql_command->)(set gtid_next(anonymous->anonymous)acquires ownership.1 set gtid_next=anonymous anonymous anonymous 1)(nothing special happens with ownership while inside a transaction.1 begin anonymous anonymous 1)(1 insert t1 values(1)anonymous anonymous 1)(commit releases ownership.1 commit anonymous 0)(begin acquires ownership.1 begin anonymous anonymous 1)(commit releases ownership even if nothing executed.1 commit anonymous 0)(rollback releases ownership.1 begin anonymous anonymous 1)(1 rollback anonymous 0)(implicit commit in transaction releases ownership.1 begin anonymous anonymous 1)(1 insert t1 values(1)anonymous anonymous 1)(1 drop table t2 anonymous 0->after_implicit_pre_commit)(1 #drop table t2 anonymous 0 after_implicit_pre_commit->)(autocommit transaction acquires ownership.1 insert t1 values(1)1->before_execute_sql_command)(autocommit transaction releases ownership at end.1 #insert t1 values(1)anonymous 0 before_execute_sql_command->)(set does not acquire ownership.1 set autocommit=0 anonymous 0)(non-autocommitted dml acquires ownership.1 insert t1 values(1)anonymous anonymous 1)(1 insert t1 values(1)anonymous anonymous 1)(1 rollback anonymous 0)(client disconnect releases ownership.1 begin anonymous anonymous 1)(1 reconnect automatic 0)(ongoing_anonymous_transaction_count>1 when there are concurrent transactions.1 set gtid_next=anonymous anonymous anonymous 1)(2 automatic 1)(2 set gtid_next=anonymous anonymous anonymous 2)(3 automatic 2)(3 set gtid_next=anonymous anonymous anonymous 3)(2 reconnect automatic 2)(1 commit anonymous 1)(3 rollback anonymous 0)(set gtid_next(anonymous->automatic)works.1 set gtid_next=automatic automatic 0)(set gtid_next(automatic->automatic)works.1 set gtid_next=automatic automatic 0)(can set gtid_mode!=on when ongoing_anonymous_transaction_count>0.2 set gtid_next=anonymous anonymous anonymous 1)(1 set @@global.gtid_mode=off_permissive automatic 1)(1 set @@global.gtid_mode=on_permissive automatic 1)(1 set @@global.gtid_mode=on_permissive automatic 1)(1 set @@global.gtid_mode=off_permissive automatic 1)(1 set @@global.gtid_mode=off_permissive automatic 1)(1 set @@global.gtid_mode=off automatic 1)(1 set @@global.gtid_mode=off automatic 1)(2 rollback anonymous 0);"
            => "insert statements(comment connection statement gtid_next gtid_owned anonymous_count sync_point error)values(nothing should be owned by default.1 automatic 0)(set gtid_next(automatic->anonymous)acquires ownership.1 set gtid_next=anonymous anonymous anonymous 1)(implicit commit releases ownership.1 create table t1(a int)anonymous 0)(implicitly committing statement re-acquires ownership.1 create table t2(a int)1->before_execute_sql_command)(implicitly committing statement releases ownership at the end.1 #create table t2(a int)anonymous 0 before_execute_sql_command->)(set gtid_next(anonymous->anonymous)acquires ownership.1 set gtid_next=anonymous anonymous anonymous 1)(nothing special happens with ownership while inside a transaction.1 begin anonymous anonymous 1)(1 insert t1 values(1)anonymous anonymous 1)(commit releases ownership.1 commit anonymous 0)(begin acquires ownership.1 begin anonymous anonymous 1)(commit releases ownership even if nothing executed.1 commit anonymous 0)(rollback releases ownership.1 begin anonymous anonymous 1)(1 rollback anonymous 0)(implicit commit in transaction releases ownership.1 begin anonymous anonymous 1)(1 insert t1 values(1)anonymous anonymous 1)(1 drop table t2 anonymous 0->after_implicit_pre_commit)(1 #drop table t2 anonymous 0 after_implicit_pre_commit->)(autocommit transaction acquires ownership.1 insert t1 values(1)1->before_execute_sql_command)(autocommit transaction releases ownership at end.1 #insert t1 values(1)anonymous 0 before_execute_sql_command->)(set does not acquire ownership.1 set autocommit=0 anonymous 0)(non-autocommitted dml acquires ownership.1 insert t1 values(1)anonymous anonymous 1)(1 insert t1 values(1)anonymous anonymous 1)(1 rollback anonymous 0)(client disconnect releases ownership.1 begin anonymous anonymous 1)(1 reconnect automatic 0)(ongoing_anonymous_transaction_count>1 when there are concurrent transactions.1 set gtid_next=anonymous anonymous anonymous 1)(2 automatic 1)(2 set gtid_next=anonymous anonymous anonymous 2)(3 automatic 2)(3 set gtid_next=anonymous anonymous anonymous 3)(2 reconnect automatic 2)(1 commit anonymous 1)(3 rollback anonymous 0)(set gtid_next(anonymous->automatic)works.1 set gtid_next=automatic automatic 0)(set gtid_next(automatic->automatic)works.1 set gtid_next=automatic automatic 0)(can set gtid_mode!=on when ongoing_anonymous_transaction_count>0.2 set gtid_next=anonymous anonymous anonymous 1)(1 set global gtid_mode=off_permissive automatic 1)(1 set global gtid_mode=on_permissive automatic 1)(1 set global gtid_mode=on_permissive automatic 1)(1 set global gtid_mode=off_permissive automatic 1)(1 set global gtid_mode=off_permissive automatic 1)(1 set global gtid_mode=off automatic 1)(1 set global gtid_mode=off automatic 1)(2 rollback anonymous 0);",
        "call mtr.add_suppression(error running internal sql query: set @@global.super_read_only=1;.internal failure.);"
            => "call mtr.add_suppression(error running internal sql query: set global super_read_only=1;.internal failure.);",
        "call mtr.add_suppression(error running internal sql query: set @@persist_only.group_replication_enforce_update_everywhere_checks=off.internal failure.);"
            => "call mtr.add_suppression(error running internal sql query: set persist_only group_replication_enforce_update_everywhere_checks=off.internal failure.);",
        "call mtr.add_suppression(internal query: set @@persist_only.group_replication_enforce_update_everywhere_checks=off result in error.error number:-2);"
            => "call mtr.add_suppression(internal query: set persist_only group_replication_enforce_update_everywhere_checks=off result in error.error number:-2);",
        "call mtr.add_suppression(error running internal sql query: set @@persist_only.group_replication_single_primary_mode=on.internal failure.);"
            => "call mtr.add_suppression(error running internal sql query: set persist_only group_replication_single_primary_mode=on.internal failure.);",
        "call mtr.add_suppression(internal query: set @@persist_only.group_replication_single_primary_mode=on result in error.error number:-2);"
            => "call mtr.add_suppression(internal query: set persist_only group_replication_single_primary_mode=on result in error.error number:-2);",
        "set @@persist_only.innodb_redo_log_capacity=8388608 ft_query_expansion_limit=80;" // 8.0.30
            => "set @@persist_only.innodb_redo_log_capacity=8388608 @@persist_only.ft_query_expansion_limit=80;",
        "set @@global.innodb_strict_mode=default innodb_lock_wait_timeout=default myisam_stats_method=default;" // 8.0.30
            => "set @@global.innodb_strict_mode=default @@global.innodb_lock_wait_timeout=default @@global.myisam_stats_method=default;",

        // reordered server options
        "create server s1 foreign data wrapper mysql options(user=user1 host 192.168.1.106 database test);"
            => "create server s1 foreign data wrapper mysql options(host 192.168.1.106 database test user=user1);",
        "create server server_one foreign data wrapper mysql options(host host_1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890 database test user some_user_name password port 9983 socket owner some_user_name);"
            => "create server server_one foreign data wrapper mysql options(host host_1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890abcdefghij1234567890 database test user some_user_name password socket owner some_user_name port 9983);",

        // reordered on empty, on error
        "select*from json_table({} \$ columns(x varchar(10)path \$.x default a on error default b on empty))jt;" => "select*from json_table({} \$ columns(x varchar(10)path \$.x default b on empty default a on error))jt;",
        "select*from json_table({} \$ columns(x varchar(10)path \$.x null on error null on empty))jt;" => "select*from json_table({} \$ columns(x varchar(10)path \$.x null on empty null on error))jt;",
        "select*from json_table({x:c} \$ columns(x varchar(10)path \$.x error on error error on empty))jt;" => "select*from json_table({x:c} \$ columns(x varchar(10)path \$.x error on empty error on error))jt;",

        // reordered column options
        "create table t1(a int default(pi()unique+3)b int default(-a));" => "create table t1(a int default(pi()+3)unique b int default(-a));",
        "create table t1(col1 point srid 4326 col2 point(st_srid(col1 0))srid 0);" => "create table t1(col1 point srid 4326 col2 point srid 0(st_srid(col1 0)));",
        "create table t1(col1 point srid 4326 col2 point(st_srid(col1 0))srid 2000);" => "create table t1(col1 point srid 4326 col2 point srid 2000(st_srid(col1 0)));",
        "alter table t1 add(col_srid_4326_generated point(st_srid(col_srid_2000 4326))srid 4326);" => "alter table t1 add(col_srid_4326_generated point srid 4326(st_srid(col_srid_2000 4326)));",
        "create table t1(x integer y integer g point(point(x y))virtual srid 0 not null);" => "create table t1(x integer y integer g point srid 0(point(x y))virtual not null);",
        "create table t1(x integer y integer g point(point(x y))stored srid 0 not null);" => "create table t1(x integer y integer g point srid 0(point(x y))stored not null);",
        "create table t(a int(1)virtual b int(1)virtual c int(1)virtual d int(1)virtual e point(1)stored srid 0 not null)engine innodb;"
            => "create table t(a int(1)virtual b int(1)virtual c int(1)virtual d int(1)virtual e point srid 0(1)stored not null)engine innodb;",
        "create table t(a int(1)virtual b int(1)virtual c int(1)virtual d int(1)virtual e point(1)stored srid 0 not null spatial(e))engine innodb;"
            => "create table t(a int(1)virtual b int(1)virtual c int(1)virtual d int(1)virtual e point srid 0(1)stored not null spatial(e))engine innodb;",
        "create table t(a int(1)virtual b int(1)virtual c int(1)virtual d int(1)virtual e2 point(1)stored not null e point(1)stored srid 0 not null)engine innodb;"
            => "create table t(a int(1)virtual b int(1)virtual c int(1)virtual d int(1)virtual e2 point(1)stored not null e point srid 0(1)stored not null)engine innodb;",
        "create table t(a int(1)virtual b int(1)virtual c int(1)virtual d int(1)virtual e2 point(1)stored not null d2 int(1)virtual e point(1)stored srid 0 not null)engine innodb;"
            => "create table t(a int(1)virtual b int(1)virtual c int(1)virtual d int(1)virtual e2 point(1)stored not null d2 int(1)virtual e point srid 0(1)stored not null)engine innodb;",
        "alter table t1 change col2 col2 point(st_srid(col1 4326))srid 4326;" => "alter table t1 change col2 col2 point srid 4326(st_srid(col1 4326));",
        "create table t6(a integer unique check(a>0)check(a is not null)null check(a<100));" => "create table t6(a integer null unique check(a>0)check(a is not null)check(a<100));",
        "create table t1(id int c geometry srid 0 not null g geometry(st_srid(c 4326))stored srid 4326 not null spatial c_idx(c)spatial g_idx(g));"
            => "create table t1(id int c geometry srid 0 not null g geometry srid 4326(st_srid(c 4326))stored not null spatial c_idx(c)spatial g_idx(g));",
        "create table t0(id int primary key cint int cvarchar varchar(5)cblob blob(1000004)unique unique_t0_0 using btree(cint asc)unique unique_t0_2 using btree(cvarchar asc))engine ndb;"
            => "create table t0(id int primary key cint int cvarchar varchar(5)cblob blob(1000004)unique unique_t0_0(cint asc)using btree unique unique_t0_2(cvarchar asc)using btree)engine ndb;",

        // reordered index options
        "create index i1 using btree on t1(c1 asc);" => "create index i1 on t1(c1 asc)using btree;",
        "create unique i1 using btree on t1(c1 asc);" => "create unique i1 on t1(c1 asc)using btree;",

        // reordered alter options
        "alter table t1 engine ndb partition by key(a)(partition p0 engine ndb partition p1 engine ndb)algorithm copy;"
            => "alter table t1 engine ndb algorithm copy partition by key(a)(partition p0 engine ndb partition p1 engine ndb);",
        "alter table t1 tablespace ts1 change i i int storage disk;" => "alter table t1 change i i int storage disk tablespace ts1;",
        //"alter table t1 character set utf8 convert to character set utf8;" => "alter table t1 convert to character set utf8 character set utf8;",
        "alter table t1 partition by key(a)(partition p0 engine innodb partition p1)engine innodb;"
            => "alter table t1 engine innodb partition by key(a)(partition p0 engine innodb partition p1);",
        "alter table t1 partition by key(a)(partition p0 partition p1)engine innodb;" => "alter table t1 engine innodb partition by key(a)(partition p0 partition p1);",
        "alter table t partition by range(thread_id)(partition p0 values less than(123456789)partition pmax values less than maxvalue)engine innodb;"
            => "alter table t engine innodb partition by range(thread_id)(partition p0 values less than(123456789)partition pmax values less than maxvalue);",
        "alter table mysql.func engine innodb drop ret;" => "alter table mysql.func drop ret engine innodb;",
        "alter table mysql.plugin engine innodb modify dl char(64);" => "alter table mysql.plugin modify dl char(64)engine innodb;",
        "alter table mysql.servers engine innodb modify wrapper varchar(128);" => "alter table mysql.servers modify wrapper varchar(128)engine innodb;",
        "alter table mysql.user engine innodb drop max_updates;" => "alter table mysql.user drop max_updates engine innodb;",
        "alter table mysql.columns_priv engine innodb drop timestamp;" => "alter table mysql.columns_priv drop timestamp engine innodb;",
        "alter table mysql.tables_priv engine innodb drop timestamp;" => "alter table mysql.tables_priv drop timestamp engine innodb;",
        "alter table mysql.procs_priv engine innodb drop timestamp;" => "alter table mysql.procs_priv drop timestamp engine innodb;",
        "alter table mysql.proxies_priv engine innodb drop timestamp;" => "alter table mysql.proxies_priv drop timestamp engine innodb;",
        "alter table mysql.component engine innodb drop component_urn;" => "alter table mysql.component drop component_urn engine innodb;",
        "alter table mysql.db engine innodb drop select_priv;" => "alter table mysql.db drop select_priv engine innodb;",
        "alter table mysql.default_roles engine innodb drop default_role_user;" => "alter table mysql.default_roles drop default_role_user engine innodb;",
        "alter table mysql.global_grants engine innodb drop with_grant_option;" => "alter table mysql.global_grants drop with_grant_option engine innodb;",
        "alter table mysql.role_edges engine innodb drop to_user;" => "alter table mysql.role_edges drop to_user engine innodb;",
        "alter table mysql.password_history engine innodb drop password_timestamp;" => "alter table mysql.password_history drop password_timestamp engine innodb;",
        "alter table t1 encryption n engine myisam;" => "alter table t1 engine myisam encryption n;",
        "alter table t1 encryption y engine innodb;" => "alter table t1 engine innodb encryption y;",
        "alter table t1 tablespace innodb_file_per_table encryption n engine innodb;" => "alter table t1 engine innodb tablespace innodb_file_per_table encryption n;",
        "alter table t_nomad tablespace s_shorter_life engine innodb;" => "alter table t_nomad engine innodb tablespace s_shorter_life;",
        "alter table t1 add index mv_idx_time using hash((json_length(j5))(json_depth(j1))(cast(j7->\$.key1 time array)))invisible;"
            => "alter table t1 add index mv_idx_time((json_length(j5))(json_depth(j1))(cast(j7->\$.key1 time array)))using hash invisible;",
        "alter table t1 add unique mv_idx_date using btree((json_depth(j3))(cast(j6->\$[*] date array))(json_length(j1)))visible;"
            => "alter table t1 add unique mv_idx_date((json_depth(j3))(cast(j6->\$[*] date array))(json_length(j1)))using btree visible;",

        // order...
        "alter table t1 auto_increment 20 change b b int algorithm default;" => "alter table t1 change b b int auto_increment 20 algorithm default;",
        "alter table t1 auto_increment 10 change b b integer algorithm default;" => "alter table t1 change b b integer auto_increment 10 algorithm default;",
        "alter table t_zip1k_remote_in_s_1k tablespace innodb_file_per_table data directory \$mysql_tmp_dir rename t_zip1k_to_file_per_table;"
            => "alter table t_zip1k_remote_in_s_1k rename t_zip1k_to_file_per_table tablespace innodb_file_per_table data directory \$mysql_tmp_dir;",
        "alter table t_zip2k_remote_in_s_2k tablespace innodb_file_per_table data directory \$mysql_tmp_dir rename t_zip2k_to_file_per_table;"
            => "alter table t_zip2k_remote_in_s_2k rename t_zip2k_to_file_per_table tablespace innodb_file_per_table data directory \$mysql_tmp_dir;",
        "alter table t_zip4k_remote_in_s_4k tablespace innodb_file_per_table data directory \$mysql_tmp_dir rename t_zip4k_to_file_per_table;"
            => "alter table t_zip4k_remote_in_s_4k rename t_zip4k_to_file_per_table tablespace innodb_file_per_table data directory \$mysql_tmp_dir;",
        "alter table t_zip8k_remote_in_s_8k tablespace innodb_file_per_table data directory \$mysql_tmp_dir rename t_zip8k_to_file_per_table;"
            => "alter table t_zip8k_remote_in_s_8k rename t_zip8k_to_file_per_table tablespace innodb_file_per_table data directory \$mysql_tmp_dir;",
        "alter table t_nomad tablespace s_def drop bb;" => "alter table t_nomad drop bb tablespace s_def;",
        "alter table t1 tablespace ts1 modify c char(20)storage disk;" => "alter table t1 modify c char(20)storage disk tablespace ts1;",
        "load data infile../../std_data/bug35469.dat into table v1 fields escaped by terminated by enclosed by lines terminated by(c1 c2);"
            => "load data infile../../std_data/bug35469.dat into table v1 fields terminated by enclosed by escaped by lines terminated by(c1 c2);",
        "load data infile../../std_data/bug35469.dat into table v2 fields escaped by terminated by enclosed by lines terminated by(c1 c2);"
            => "load data infile../../std_data/bug35469.dat into table v2 fields terminated by enclosed by escaped by lines terminated by(c1 c2);",
        "create procedure bar(x char(16)y int)comment 111111111111 sql security invoker insert test.t1 values(x y)|"
            => "create procedure bar(x char(16)y int)sql security invoker comment 111111111111 insert test.t1 values(x y)|",
        "alter procedure bar comment 2222222222 sql security definer|" => "alter procedure bar sql security definer comment 2222222222|",

        // useless delete * columns
        "delete quick from d1.t1.*d2.t2.*d3.t3 using d1.t1 d2.t2 d3.t3 where d1.t1.c1=d2.t2.c2 and d2.t2.c1=d3.t3.c2;"
            => "delete quick from d1.t1 d2.t2 d3.t3 using d1.t1 d2.t2 d3.t3 where d1.t1.c1=d2.t2.c2 and d2.t2.c1=d3.t3.c2;",
        "delete quick from t1.*t2.*t3 using t1 t2 t3 where t1.c1=t2.c2 and t2.c1=t3.c2;" => "delete quick from t1 t2 t3 using t1 t2 t3 where t1.c1=t2.c2 and t2.c1=t3.c2;",
        "explain with cte(select alias2.col_datetime_key field1 alias1.col_varchar_key field2 alias2.col_int_key field3 from view_b alias1 left join cc alias2 left join view_d alias3 on alias2.col_varchar_key=alias3.col_blob_key on alias1.col_blob=alias3.col_blob_key where alias3.col_varchar_key like(w)or alias2.pk<>4)delete quick from outr1.*outr2.*using d outr1 left join d outr2 on(outr1.col_int=outr2.col_int)join cte outrcte on outr1.pk=outrcte.field1 where(4 7)in(select distinct innr1.col_int_key x innr1.col_int y from cc innr1 join cte innrcte on innr1.pk=innrcte.field1 where innr1.col_int>=innr1.pk);"
            => "explain with cte(select alias2.col_datetime_key field1 alias1.col_varchar_key field2 alias2.col_int_key field3 from view_b alias1 left join cc alias2 left join view_d alias3 on alias2.col_varchar_key=alias3.col_blob_key on alias1.col_blob=alias3.col_blob_key where alias3.col_varchar_key like(w)or alias2.pk<>4)delete quick from outr1 outr2 using d outr1 left join d outr2 on(outr1.col_int=outr2.col_int)join cte outrcte on outr1.pk=outrcte.field1 where(4 7)in(select distinct innr1.col_int_key x innr1.col_int y from cc innr1 join cte innrcte on innr1.pk=innrcte.field1 where innr1.col_int>=innr1.pk);",
        "with cte(select alias1.col_int_key field1 from a alias1 left join c alias2 on alias1.col_blob=alias2.col_blob_key where alias2.pk>3 and alias2.pk<(3+10)or alias1.col_varchar_key>=z and alias1.col_varchar_key<=k)delete from low_priority quick outr1.*outr2.*using d outr1 left join c outr2 on(outr1.col_int=outr2.pk)join a outr3 on(outr1.col_int_key=outr3.pk)right join cte outrcte on outr1.col_int_key=outrcte.field1 where outr1.col_blob_key<>(select innr1.col_blob y from a innr2 join a innr1 on(innr2.col_datetime>=innr1.col_datetime)right join cte innrcte on innr2.col_int_key<innrcte.field1 where innr1.col_datetime=2006-02-24);"
            => "with cte(select alias1.col_int_key field1 from a alias1 left join c alias2 on alias1.col_blob=alias2.col_blob_key where alias2.pk>3 and alias2.pk<(3+10)or alias1.col_varchar_key>=z and alias1.col_varchar_key<=k)delete low_priority quick from outr1 outr2 using d outr1 left join c outr2 on(outr1.col_int=outr2.pk)join a outr3 on(outr1.col_int_key=outr3.pk)right join cte outrcte on outr1.col_int_key=outrcte.field1 where outr1.col_blob_key<>(select innr1.col_blob y from a innr2 join a innr1 on(innr2.col_datetime>=innr1.col_datetime)right join cte innrcte on innr2.col_int_key<innrcte.field1 where innr1.col_datetime=2006-02-24);",
        "explain with cte(select alias1.pk field1 alias2.col_blob field2 from bb alias1 left join dd alias2 on alias1.col_varchar_key=alias2.col_varchar_key where alias2.pk>3 and alias2.pk<(1+1)or alias2.pk in(7 5)and alias2.pk<>3 and alias2.pk is not null)delete from low_priority outr2.*using d outr1 join a outr2 on(outr1.col_int=outr2.col_int_key)left join cte outrcte on outr2.col_int_key=outrcte.field1 where outr1.col_int_key<(select distinct innr1.col_int y from cc innr1 left join cte innrcte on innr1.col_int_key<=innrcte.field1 where innr1.col_int=4);"
            => "explain with cte(select alias1.pk field1 alias2.col_blob field2 from bb alias1 left join dd alias2 on alias1.col_varchar_key=alias2.col_varchar_key where alias2.pk>3 and alias2.pk<(1+1)or alias2.pk in(7 5)and alias2.pk<>3 and alias2.pk is not null)delete low_priority from outr2 using d outr1 join a outr2 on(outr1.col_int=outr2.col_int_key)left join cte outrcte on outr2.col_int_key=outrcte.field1 where outr1.col_int_key<(select distinct innr1.col_int y from cc innr1 left join cte innrcte on innr1.col_int_key<=innrcte.field1 where innr1.col_int=4);",

        // removed as
        "prepare stmt1 from create table t1(m int)select 1 m;" => "prepare stmt1 from create table t1(m int)as select 1 m;",
        "prepare stmt1 from create table t1(m int)select ? m;" => "prepare stmt1 from create table t1(m int)as select ? m;",
        "prepare stmt1 from create table t1 select 1 i;" => "prepare stmt1 from create table t1 as select 1 i;",
        "prepare stmt from create table t2 select*from t1;" => "prepare stmt from create table t2 as select*from t1;",
        "prepare st_19182 from create table t2(i int j int index(i)index(j))select i from t1;"
            => "prepare st_19182 from create table t2(i int j int index(i)index(j))as select i from t1;",
        "prepare stmt2 from create table mysqltest.t2 select test;" => "prepare stmt2 from create table mysqltest.t2 as select test;",
        "prepare stmt from create table t1 select ?;" => "prepare stmt from create table t1 as select ?;",
        "prepare stmt from create view v1 select*from t1;" => "prepare stmt from create view v1 as select*from t1;",
        "prepare stmt from create view v1(c d)select a b from t1;" => "prepare stmt from create view v1(c d)as select a b from t1;",
        "prepare stmt from create view v1(c)select b+1 from t1;" => "prepare stmt from create view v1(c)as select b+1 from t1;",
        "prepare stmt from create view v1(c d e f)select a b a in(select a+2 from t1)a=all(select a from t1)from t1;"
            => "prepare stmt from create view v1(c d e f)as select a b a in(select a+2 from t1)a=all(select a from t1)from t1;",
        "prepare stmt from create or replace view v1 select 1;" => "prepare stmt from create or replace view v1 as select 1;",
        "prepare stmt from create view v1 select 1 1;" => "prepare stmt from create view v1 as select 1 1;",
        "prepare stmt from create view v1(x)select a from t1 where a>1;" => "prepare stmt from create view v1(x)as select a from t1 where a>1;",
        "prepare stmt from create view v1 select*from t1 b;" => "prepare stmt from create view v1 as select*from t1 b;",
        "prepare stmt1 from create table t1 select 1;" => "prepare stmt1 from create table t1 as select 1;",
        "prepare stmt from create table t1 select*from mysql.user limit 0;" => "prepare stmt from create table t1 as select*from mysql.user limit 0;",
        "prepare stmt3 from create table t3(m int)select ? m;" => "prepare stmt3 from create table t3(m int)as select ? m;",
        "prepare stmt from create temporary table if not exists t2 select*from t1;" => "prepare stmt from create temporary table if not exists t2 as select*from t1;",
        "prepare s from create table t4 select ? a from t3 limit 1;" => "prepare s from create table t4 as select ? a from t3 limit 1;",
        "prepare s from create table t4 select cast(? binary(1000000))a from t3 limit 1;" => "prepare s from create table t4 as select cast(? binary(1000000))a from t3 limit 1;",
        "prepare stmt1 from create temporary table tmp2 select b from(select f1()b from tmp1)t;"
            => "prepare stmt1 from create temporary table tmp2 as select b from(select f1()b from tmp1)t;",
        "prepare stmt from create table t2 select ? from t1;" => "prepare stmt from create table t2 as select ? from t1;",
        "prepare x from create view bug22108567_v1 select 1 from(select 1)d1;" => "prepare x from create view bug22108567_v1 as select 1 from(select 1)d1;",
        "prepare stmt from create view v1 select*from json_table([] \$[*] columns(c1 int path \$.x))jt;"
            => "prepare stmt from create view v1 as select*from json_table([] \$[*] columns(c1 int path \$.x))jt;",
        "prepare stmt from create view v1 with recursive cte(n)(select 1 union all select n+1 from cte where n<5)select*from cte;"
            => "prepare stmt from create view v1 as with recursive cte(n)(select 1 union all select n+1 from cte where n<5)select*from cte;",
        "set @s:=concat(create table t(a longblob(repeat(a 1024*1024))stored)engine innodb;);"
            => "set @s:=concat(create table t(a longblob as(repeat(a 1024*1024))stored)engine innodb;);",
        "set @s:=concat(create table t(w int a int(repeat(0 1024*1024))stored)engine innodb;);"
            => "set @s:=concat(create table t(w int a int generated always as(repeat(0 1024*1024))stored)engine innodb;);",
        "prepare stmt from create table t1 select*from t0;" => "prepare stmt from create table t1 as select*from t0;",
        "prepare stmt2 from create table t2 select*from t;" => "prepare stmt2 from create table t2 as select*from t;", // 8.0.30

        // removed "=" after engine, user etc.
        "select transactions from information_schema.engines where engine federated;" => "select transactions from information_schema.engines where engine=federated;",
        "select*from information_schema.engines where engine example;" => "select*from information_schema.engines where engine=example;",
        "select table_schema table_name engine from information_schema.tables where table_schema=mysql and engine myisam;"
            => "select table_schema table_name engine from information_schema.tables where table_schema=mysql and engine=myisam;",
        "select engine support from information_schema.engines where engine federated;" => "select engine support from information_schema.engines where engine=federated;",
        "start group_replication user=regular_user_wp password;" => "start group_replication user=regular_user_wp password=;",

        // mangled default, todo
        "create table t1(c1 int c2 int unsigned default(10)invisible c3 int unsigned not null default(90)invisible c4 int unsigned invisible primary key(5)c5 varchar(100)character set ucs2 invisible c6 geometry invisible c7 enum(enum_v1 enum_v2 enum_v3)character set swe7 collate swe7_bin invisible c8 set(set_v1 set_v2 set_v3)character set swe7 collate swe7_bin invisible);"
            => "create table t1(c1 int c2 int unsigned default(10)invisible c3 int unsigned not null default(90)invisible c4 int unsigned default(5)invisible primary key c5 varchar(100)character set ucs2 invisible c6 geometry invisible c7 enum(enum_v1 enum_v2 enum_v3)character set swe7 collate swe7_bin invisible c8 set(set_v1 set_v2 set_v3)character set swe7 collate swe7_bin invisible);",

        // mangled texts & params
        "create table t(i int j encryption nj int);" => "create table t(i int j encryption=nj int);",
        "select---test 8 switch internal character set--;" => "select---test 8 switch internal charset--;",
        "select---test1 check tables load--;" => "select---test1 check table load--;",
        "create schema s encryption n s;" => "create schema s encryption=n s;",
        "drop schema s encryption n s;" => "drop schema s encryption=n s;",
        "prepare stmt4 from show storage engines;" => "prepare stmt4 from show engines;",
        "call mtr.add_suppression(slave i/o for channel : relay log write failure: could not queue event from master);"
            => "call mtr.add_suppression(slave i/o for channel : relay log write failure: could not queue event from master.*);",
        "call mtr.add_suppression(got fatal error 1236 from masterreplicate the missing transactions from elsewhere);"
            => "call mtr.add_suppression(got fatal error 1236 from master.*replicate the missing transactions from elsewhere);",
        "call mtr.add_suppression(.*column 1 of table.*cannot be converted from type);" => "call mtr.add_suppression(.*column 1 of table.*cannot be converted from type );",
        "call mtr.add_suppression(cant create schema performance_schema;database exists);" => "call mtr.add_suppression(cant create database performance_schema;database exists);",
        "call mtr.add_suppression(cannot load from mysql.the table is probably corrupted);" => "call mtr.add_suppression(cannot load from mysql.*.the table is probably corrupted);",
        "call mtr.add_suppression(.*slave: got error 173 unknown error code from ndbcluster);" => "call mtr.add_suppression(.*slave: got error 173 unknown error code from ndbcluster.*);",
        "update mysql.triggers set action_statement=delete from from t1 a using t1 a action_statement_utf8=delete using t1 a using t1 a where name=tr13;"
            => "update mysql.triggers set action_statement=delete from t1 a using t1 a action_statement_utf8=delete from t1 a using t1 a where name=tr13;",
        "update mysql.triggers set action_statement=delete from from non_existing_table action_statement_utf8=delete using non_existing_table where name=tr14;"
            => "update mysql.triggers set action_statement=delete from non_existing_table action_statement_utf8=delete from non_existing_table where name=tr14;",
        "update mysql.triggers set action_statement=delete from from non_existing_table a using non_existing_table a action_statement_utf8=delete using non_existing_table a using non_existing_table a where name=tr15;"
            => "update mysql.triggers set action_statement=delete from non_existing_table a using non_existing_table a action_statement_utf8=delete from non_existing_table a using non_existing_table a where name=tr15;",
        "update mysql.tables set comment mno where name=t1;" => "update mysql.tables set comment=mno where name=t1;",
        "update whole_schema set checksum sha2(row_checksums 256);" => "update whole_schema set checksum=sha2(row_checksums 256);",
        "set @a=concat(create table t_parent(a int)union(@a)insert_method first engine mrg_myisam);"
            => "set @a=concat(create table t_parent(a int)union(@a)insert_method=first engine=mrg_myisam);",
        "insert t2 values(create schema mysqltest1 with table locked);" => "insert t2 values(create database mysqltest1 with table locked);",
        "call mtr.add_suppression(.*set @@global.clone_valid_donor_list=::1:.*result in error.*);"
            => "call mtr.add_suppression(.*set global clone_valid_donor_list=::1:.*result in error.*);",
        "call mtr.add_suppression(failed to execute alter schema);" => "call mtr.add_suppression(failed to execute alter database);",
        "call tde_db.row_format_t_encrypt(row_format dynamic);" => "call tde_db.row_format_t_encrypt(row_format=dynamic);",
        "call tde_db.row_format_t_encrypt(row_format compact);" => "call tde_db.row_format_t_encrypt(row_format=compact);",
        "call tde_db.row_format_t_encrypt(row_format redundant);" => "call tde_db.row_format_t_encrypt(row_format=redundant);",
        "call tde_db.row_format_t_encrypt(row_format compressed);" => "call tde_db.row_format_t_encrypt(row_format=compressed);",
        "call tde_db.row_format_t_encrypt(row_format compressed key_block_size 4);" => "call tde_db.row_format_t_encrypt(row_format=compressed key_block_size=4);",
        "call mtr.add_suppression(failed to delete from the row:.*using the gtid_executed table.);"
            => "call mtr.add_suppression(failed to delete the row:.*from the gtid_executed table.);",
        "call mtr.add_suppression(unable to delete from statistics for table test.innodb_stats_drop_locked: lock wait timeout.they can be deleted later using delete using mysql.innodb_index_stats where database_name);"
            => "call mtr.add_suppression(unable to delete statistics for table test.innodb_stats_drop_locked: lock wait timeout.they can be deleted later using delete from mysql.innodb_index_stats where database_name);",
        "set @s=concat(create table t1(c1 int)engine innodb compression @long_str);" => "set @s=concat(create table t1(c1 int)engine innodb compression=@long_str);",
        "insert query_rewrite.rewrite_rules(pattern replacement)values(delete from test.t1 partition(p0)where a=? order by b limit 2 delete from test.t1 partition(p0)where b=? order by b limit 2)(delete from test.t1 from test.t1 join test.t2 where test.t1.a=? delete test.t1 using test.t1 join test.t2 where test.t1.a in(? 3 5))(delete test.v1 from test.v1 join test.t2 where test.v1.a=? delete test.v1 from test.v1 join test.t2 where test.v1.a in(? 3 5))(delete test.v2 from test.v2 where test.v2.a=? delete test.v2 from test.v2 where test.v2.a in(? 3 5))(delete from test.v1 delete from test.t2);"
            => "insert query_rewrite.rewrite_rules(pattern replacement)values(delete from test.t1 partition(p0)where a=? order by b limit 2 delete from test.t1 partition(p0)where b=? order by b limit 2)(delete test.t1 from test.t1 join test.t2 where test.t1.a=? delete test.t1 from test.t1 join test.t2 where test.t1.a in(? 3 5))(delete test.v1 from test.v1 join test.t2 where test.v1.a=? delete test.v1 from test.v1 join test.t2 where test.v1.a in(? 3 5))(delete test.v2 from test.v2 where test.v2.a=? delete test.v2 from test.v2 where test.v2.a in(? 3 5))(delete from test.v1 delete from test.t2);",
        "delete from from query_rewrite.rewrite_rules where pattern=select*using test.t1;" => "delete from query_rewrite.rewrite_rules where pattern=select*from test.t1;",
        "delete from from query_rewrite.rewrite_rules where pattern=select c1 using test.t1 where c2=?;"
            => "delete from query_rewrite.rewrite_rules where pattern=select c1 from test.t1 where c2=?;",
        "delete from from query_rewrite.rewrite_rules where pattern<>select c1 using test.t1;" => "delete from query_rewrite.rewrite_rules where pattern<>select c1 from test.t1;",
        "insert query_rewrite.rewrite_rules(pattern replacement)values(delete from from test.t1 where a=? delete using test.t1);" // 8.0.31
            => "insert query_rewrite.rewrite_rules(pattern replacement)values(delete from test.t1 where a=? delete from test.t1);",
        "select statement_digest(lock tables t1 read)is null;" => "select statement_digest(lock table t1 read)is null;",
        "insert proc values(test downgrade_alter_proc procedure downgrade_alter_proc sql contains_sql no invoker begin select c1 english french from t1 join t2 on t1.c3=t2.col2;end root@localhost 1988-04-25 20:45:00 1988-04-25 20:45:00 no_zero_date latin1 latin1_swedish_ci latin1_swedish_ci begin select c1 english french from t1 join t2 on t1.c3=t2.col2;end)(test my_test_func function myfunc sql contains_sql no definer varchar(20)character set latin1 begin return \xe3\xa5;end root@localhost 2017-03-08 09:07:36 2017-03-08 09:07:36 only_full_group_by strict_trans_tables no_zero_in_date no_zero_date error_for_division_by_zero no_auto_create_user no_engine_substitution latin1 latin1_swedish_ci latin1_swedish_ci begin return \xe3\x83\xe2\xa5;end);"
            => "insert proc values(test downgrade_alter_proc procedure downgrade_alter_proc sql contains_sql no invoker begin select c1 english french from t1 join t2 on t1.c3=t2.col2;end root@localhost 1988-04-25 20:45:00 1988-04-25 20:45:00 no_zero_date latin1 latin1_swedish_ci latin1_swedish_ci begin select c1 english french from t1 join t2 on t1.c3=t2.col2;end)(test my_test_func function myfunc sql contains_sql no definer varchar(20)charset latin1 begin return \xe3\xa5;end root@localhost 2017-03-08 09:07:36 2017-03-08 09:07:36 only_full_group_by strict_trans_tables no_zero_in_date no_zero_date error_for_division_by_zero no_auto_create_user no_engine_substitution latin1 latin1_swedish_ci latin1_swedish_ci begin return \xe3\x83\xe2\xa5;end);",
        "create procedure p_create()begin declare i int default 1;set @lock_table_stmt=lock tables;set @drop_table_stmt=drop table;while i<@@global.table_definition_cache+1 do set @table_name=concat(t_ i);set @opt_comma=if(i=1);set @lock_table_stmt=concat(@lock_table_stmt @opt_comma @table_name read);set @drop_table_stmt=concat(@drop_table_stmt @opt_comma @table_name);set @create_table_stmt=concat(create table if not exists @table_name(a int));prepare stmt from @create_table_stmt;execute stmt;deallocate prepare stmt;set i=i+1;end while;end|"
            => "create procedure p_create()begin declare i int default 1;set @lock_table_stmt=lock table;set @drop_table_stmt=drop table;while i<@@global.table_definition_cache+1 do set @table_name=concat(t_ i);set @opt_comma=if(i=1);set @lock_table_stmt=concat(@lock_table_stmt @opt_comma @table_name read);set @drop_table_stmt=concat(@drop_table_stmt @opt_comma @table_name);set @create_table_stmt=concat(create table if not exists @table_name(a int));prepare stmt from @create_table_stmt;execute stmt;deallocate prepare stmt;set i=i+1;end while;end|",
        "set @create_cmd=create table mysql.ndb_binlog_index(i integer primary key)engine innodb stats_persistent 0;"
            => "set @create_cmd=create table mysql.ndb_binlog_index(i integer primary key)engine innodb stats_persistent=0;",
        "insert t1(stmt_text)values(select 1)(flush tables)(handler t1 open ha)(analyze table t1)(check tables t1)(checksum table t1)(check tables t1)(optimize table t1)(repair table t1)(describe extended select*from t1)(help help)(show databases)(show tables)(show table status)(show open tables)(show engines)(insert t1(id)values(1))(update t1 set status=)(delete from t1)(truncate t1)(call p1())(foo bar)(create view v1 select 1)(alter view v1 select 2)(drop view v1)(create table t2(a int))(alter table t2 add(b int))(drop table t2)|"
            => "insert t1(stmt_text)values(select 1)(flush tables)(handler t1 open ha)(analyze table t1)(check table t1)(checksum table t1)(check table t1)(optimize table t1)(repair table t1)(describe extended select*from t1)(help help)(show databases)(show tables)(show table status)(show open tables)(show storage engines)(insert t1(id)values(1))(update t1 set status=)(delete from t1)(truncate t1)(call p1())(foo bar)(create view v1 select 1)(alter view v1 select 2)(drop view v1)(create table t2(a int))(alter table t2 add(b int))(drop table t2)|",
        "create procedure tde_db.create_table_rotate_key()begin declare i int default 1;declare has_error int default 0;declare continue handler for 1062 set has_error=1;while(i<=2000)do if i%10=0 then set @sql_text=concat(create table concat(tde_db.t_non_encrypt_ encrypt _ i)(c1 int)engine innodb);else set @sql_text=concat(create table concat(tde_db.t_encrypt_ encrypt _ i)(c1 int)encryption y engine innodb);end if;prepare stmt from @sql_text;execute stmt;deallocate prepare stmt;alter instance rotate innodb master key;set i=i+1;end while;end|"
            => "create procedure tde_db.create_table_rotate_key()begin declare i int default 1;declare has_error int default 0;declare continue handler for 1062 set has_error=1;while(i<=2000)do if i%10=0 then set @sql_text=concat(create table concat(tde_db.t_non_encrypt_ encrypt _ i)(c1 int)engine innodb);else set @sql_text=concat(create table concat(tde_db.t_encrypt_ encrypt _ i)(c1 int)encryption=y engine innodb);end if;prepare stmt from @sql_text;execute stmt;deallocate prepare stmt;alter instance rotate innodb master key;set i=i+1;end while;end|",
        "create procedure tde_db.row_format_t_encrypt(row_form varchar(1000))begin declare i int default 1;declare has_error int default 0;declare continue handler for 1062 set has_error=1;drop table if exists tde_db.t_encrypt;set @saved_innodb_strict_mode=@@session.innodb_strict_mode;set innodb_strict_mode=off;set @sql_text=concat(create table tde_db.t_encrypt(c2 int not null auto_increment primary key c3 varchar(255)c4 json c5 int(json_extract(c4 \$.key_a))stored c6 int(json_extract(c4 \$.key_b))virtual c7 point srid 0 not null spatial idx2(c7))encryption y row_form engine innodb);prepare stmt from @sql_text;execute stmt;deallocate prepare stmt;set innodb_strict_mode=@saved_innodb_strict_mode;show create table tde_db.t_encrypt;insert tde_db.t_encrypt(c3 c4 c7)values(repeat(a 200){key_a: 1 key_b: 2 key_c: 3} st_geomfromtext(point(383293632 1754448)));insert tde_db.t_encrypt(c3 c4 c7)select c3 c4 c7 from tde_db.t_encrypt;insert tde_db.t_encrypt(c3 c4 c7)select c3 c4 c7 from tde_db.t_encrypt;insert tde_db.t_encrypt(c3 c4 c7)select c3 c4 c7 from tde_db.t_encrypt;insert tde_db.t_encrypt(c3 c4 c7)select c3 c4 c7 from tde_db.t_encrypt;insert tde_db.t_encrypt(c3 c4 c7)select c3 c4 c7 from tde_db.t_encrypt;select count(*)from tde_db.t_encrypt;select c2 c4 c5 st_astext(c7)from tde_db.t_encrypt limit 10;select c2 c4 c5 c6 st_astext(c7)from tde_db.t_encrypt limit 10;delete from tde_db.t_encrypt where c2>10;update tde_db.t_encrypt set c2=100 where c2=1;select c2 c4 c5 st_astext(c7)from tde_db.t_encrypt limit 10;select c2 c4 c5 c6 st_astext(c7)from tde_db.t_encrypt limit 10;show create table tde_db.t_encrypt;end|"
            => "create procedure tde_db.row_format_t_encrypt(row_form varchar(1000))begin declare i int default 1;declare has_error int default 0;declare continue handler for 1062 set has_error=1;drop table if exists tde_db.t_encrypt;set @saved_innodb_strict_mode=@@session.innodb_strict_mode;set innodb_strict_mode=off;set @sql_text=concat(create table tde_db.t_encrypt(c2 int not null auto_increment primary key c3 varchar(255)c4 json c5 int(json_extract(c4 \$.key_a))stored c6 int(json_extract(c4 \$.key_b))virtual c7 point not null srid 0 spatial idx2(c7))encryption=y row_form engine innodb);prepare stmt from @sql_text;execute stmt;deallocate prepare stmt;set innodb_strict_mode=@saved_innodb_strict_mode;show create table tde_db.t_encrypt;insert tde_db.t_encrypt(c3 c4 c7)values(repeat(a 200){key_a: 1 key_b: 2 key_c: 3} st_geomfromtext(point(383293632 1754448)));insert tde_db.t_encrypt(c3 c4 c7)select c3 c4 c7 from tde_db.t_encrypt;insert tde_db.t_encrypt(c3 c4 c7)select c3 c4 c7 from tde_db.t_encrypt;insert tde_db.t_encrypt(c3 c4 c7)select c3 c4 c7 from tde_db.t_encrypt;insert tde_db.t_encrypt(c3 c4 c7)select c3 c4 c7 from tde_db.t_encrypt;insert tde_db.t_encrypt(c3 c4 c7)select c3 c4 c7 from tde_db.t_encrypt;select count(*)from tde_db.t_encrypt;select c2 c4 c5 st_astext(c7)from tde_db.t_encrypt limit 10;select c2 c4 c5 c6 st_astext(c7)from tde_db.t_encrypt limit 10;delete from tde_db.t_encrypt where c2>10;update tde_db.t_encrypt set c2=100 where c2=1;select c2 c4 c5 st_astext(c7)from tde_db.t_encrypt limit 10;select c2 c4 c5 c6 st_astext(c7)from tde_db.t_encrypt limit 10;show create table tde_db.t_encrypt;end|",
        "create procedure tde_db.create_encrypt_table(encrypt varchar(5))begin declare i int default 1;declare has_error int default 0;while(i<=50)do set @sql_text=concat(create table concat(tde_db.t_encrypt_ encrypt _ i)(c1 int)encryption encrypt engine innodb);prepare stmt from @sql_text;execute stmt;deallocate prepare stmt;set i=i+1;end while;end|"
            => "create procedure tde_db.create_encrypt_table(encrypt varchar(5))begin declare i int default 1;declare has_error int default 0;while(i<=50)do set @sql_text=concat(create table concat(tde_db.t_encrypt_ encrypt _ i)(c1 int)encryption=encrypt engine innodb);prepare stmt from @sql_text;execute stmt;deallocate prepare stmt;set i=i+1;end while;end|",
        "create procedure tde_db.create_t_encrypt(encrypt varchar(5)tcnt int)begin declare i int default 1;declare has_error int default 0;declare continue handler for 1050 set has_error=1;set i=tcnt;while(i<=5000)do set @sql_text=concat(create table concat(tde_db.t_encrypt_ encrypt _ i)(c1 int)encryption encrypt engine innodb);prepare stmt from @sql_text;execute stmt;deallocate prepare stmt;set i=i+1;end while;end|"
            => "create procedure tde_db.create_t_encrypt(encrypt varchar(5)tcnt int)begin declare i int default 1;declare has_error int default 0;declare continue handler for 1050 set has_error=1;set i=tcnt;while(i<=5000)do set @sql_text=concat(create table concat(tde_db.t_encrypt_ encrypt _ i)(c1 int)encryption=encrypt engine innodb);prepare stmt from @sql_text;execute stmt;deallocate prepare stmt;set i=i+1;end while;end|",
        "insert proc values(test downgrade_alter_proc procedure downgrade_alter_proc sql contains_sql no invoker begin select c1 english french from t1 join t2 on t1.c3=t2.col2;end root@localhost 1988-04-25 20:45:00 1988-04-25 20:45:00 no_zero_date latin1 latin1_swedish_ci latin1_swedish_ci begin select c1 english french from t1 join t2 on t1.c3=t2.col2;end)(test my_test_func function myfunc sql contains_sql no definer varchar(20)character set latin1 begin return \u{e5};end root@localhost 2017-03-08 09:07:36 2017-03-08 09:07:36 only_full_group_by strict_trans_tables no_zero_in_date no_zero_date error_for_division_by_zero no_auto_create_user no_engine_substitution latin1 latin1_swedish_ci latin1_swedish_ci begin return \u{c3}\u{a5};end);" // 8.0.31
            => "insert proc values(test downgrade_alter_proc procedure downgrade_alter_proc sql contains_sql no invoker begin select c1 english french from t1 join t2 on t1.c3=t2.col2;end root@localhost 1988-04-25 20:45:00 1988-04-25 20:45:00 no_zero_date latin1 latin1_swedish_ci latin1_swedish_ci begin select c1 english french from t1 join t2 on t1.c3=t2.col2;end)(test my_test_func function myfunc sql contains_sql no definer varchar(20)charset latin1 begin return \u{e5};end root@localhost 2017-03-08 09:07:36 2017-03-08 09:07:36 only_full_group_by strict_trans_tables no_zero_in_date no_zero_date error_for_division_by_zero no_auto_create_user no_engine_substitution latin1 latin1_swedish_ci latin1_swedish_ci begin return \u{c3}\u{a5};end);",
        "call mtr.add_suppression(slave sql.*master suffers from this bug: http:..bugs.mysql.com.bug.php.id=37426 error_code: my-013127);"
            => "call mtr.add_suppression(slave sql.*master suffers from this bug: http:..bugs.mysql.com.bug.php.id=37426.*error_code: my-013127);",
        "call mtr.add_suppression(internal query: clone instance from root@127.0.0.1:);" => "call mtr.add_suppression(internal query: clone instance from root@127.0.0.1:.*);",
        "call mtr.add_suppression(internal query: clone instance from root@127.0.0.1: error number: 3862);"
            => "call mtr.add_suppression(internal query: clone instance from root@127.0.0.1:.*error number: 3862);",
        "call mtr.add_suppression([error].*my-d+.*cannot delete tablespace because it is not found in the tablespace memory cache);"
            => "call mtr.add_suppression([error].*my-d+.*cannot delete tablespace.*because it is not found in the tablespace memory cache);",
        "call mtr.add_suppression(cannot delete or update a parent row: a foreign key constraint fails error_code: my-001451);"
            => "call mtr.add_suppression(cannot delete or update a parent row: a foreign key constraint fails.*error_code: my-001451);",
        "call mtr.add_suppression(slave sql.*cannot delete or update a parent row: a foreign key constraint fails error_code: 1451);"
            => "call mtr.add_suppression(slave sql.*cannot delete or update a parent row: a foreign key constraint fails.*error_code: 1451);",
        "call mtr.add_suppression(cannot delete tablespace because it is not found in the tablespace memory cache.);"
            => "call mtr.add_suppression(cannot delete tablespace.*because it is not found in the tablespace memory cache.);",
        "call mtr.add_suppression(cannot delete tablespace in discard tablespace.tablespace not found);"
            => "call mtr.add_suppression(cannot delete tablespace.*in discard tablespace.tablespace not found);",
        "call mtr.add_suppression(failed to delete from the row: using the gtid_executed table.);"
            => "call mtr.add_suppression(failed to delete the row:.*from the gtid_executed table.);",

        // too much features removed
        "alter table t1 engine csv encryption;" => "alter table t1 engine csv encryption n;",
        "create table t1(f1 int not null)engine myisam encryption;" => "create table t1(f1 int not null)engine myisam encryption n;",
        "alter table t1 modify c2 varchar(1024)binary byte binary;" => "alter table t1 modify c2 varchar(1024)byte binary;",
    ];

}
