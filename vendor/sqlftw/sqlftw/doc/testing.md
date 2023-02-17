
How SQLFTW is currently tested
==============================

SQLFTW unit tests
-----------------

there are unit tests of course. these are written against documentation and do not cover 100% of the features yet.

majority of unit tests check parser in this way:
- parser parses a given SQL code
- resulting `Command` is serialized back to string
- original code and result string is compared and should be equal

there are of course some differences. mainly these:
- parser does not track white space, so things like spaces and indentation are normalized
- keywords and other special values are normalized to upper-case
- (My)SQL uses some aliases like `\N` == `null` or `CHARSET` == `CHARACTER SET`. these are also normalized
- in some cases there are optional syntax features that are normalized - always removed or always added (e.g. optional `=` in table options)
- unary operators `+` and `-` before numeric literals are normalized

unit tests are not the main source of truth!

see `/tests/Parser`

run `composer t` for unit tests


Database unit tests
-------------------

these are the main source of truth

SQLFTW is currently using the test suites from mysql-server project

these tests are written in Perl, using SQL syntax directly combined with Perl syntax. i am doing my best to strip 
as much non-SQL syntax as possible while keeping as much SQL syntax as possible. resulting code is then being fed to
`Parser`. the measure for success for now is, whether parser fails and wheter the resulting `Command`s can be serialized. 
i am not currently checking if it serializes properly. working on that...

this test is being run on all ~8000 test files from mysql-server source and in all versions since 8.0.0 to current version.
more versions will be added later (5.7...)

see `/tests/Mysql`

run `php tests/Mysql/test.php` for Mysql tests

requirements:
- you will need `https://github.com/mysql/mysql-server` cloned to adjacent directory
- you will need `https://github.com/paranoiq/dogma-debug` wired in for now
