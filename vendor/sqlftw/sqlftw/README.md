# SQLFTW

MySQL (for now) lexer, parser, language model and static analyzer written in PHP

it is a validating parser which produces an object implementing SqlFtw\Sql\Command interface
for each of approximately 140 supported SQL commands. Commands do model the syntactic aspect of SQL code,
not domain aspect (models exactly how queries are written), however does not track white space and currently 
ignores some comments

this parser is intended as a basis for two other projects:
- one is doing static analysis of SQL code, especially safety and performance of migrations (currently using very basic SQL parser from phpMyAdmin project)
- another will hopefully help PHPStan (static analysis tool for PHP) better understand SQL queries and their results

on its own it can be used to validate syntax of SQL code (e.g. migrations)


SQL syntax support:
-------------------

supports all SQL commands from MySQL 5.x to MySQL 8.0.31 and almost all language features

not supported features, that will fail to parse:
- support for ascii-incompatible multibyte encodings like `shift-jis`, `gb18030` or `utf-16` (fails to parse)
- quoted delimiters (not implemented, probably will fail)
- implicit string concatenation of double-quoted names in ANSI mode (`"foo" "bar"`; this is supported on strings, but not on names)

accepted, but ignored features (no model and serialization):
- resolving operator precedence in expressions (for now operators of the same tier are just parsed from left to right; will be implemented later)
- regular comments inside statements (comments before statement are collected)
- HeatWave plugin features (SECONDARY_ENGINE)
- `SELECT ... PROCEDURE ANALYSE (...)` - removed in MySQL 8
- `WEIGHT_STRING(... LEVEL ...)` - removed in MySQL 8

features implemented other way than MySQL:
- parser produces an error on unterminated comments same as PostgreSQL does (MySQL is silent and according to tests, this might be a bug)
- parser produces an error when reading user variables with invalid name (MySQL silently ignores them and returns null)
- parser produces an error on optimizer hint with invalid syntax (MySQL produces a warning)


Architecture:
-------------

main layers:
- Lexer - tokenizes SQL, returns a Generator of parser Tokens
- Parser(s) - validates syntax and returns a Generator of parsed Command objects
- Command(s) - SQL commands parsed from plaintext to immutable object representation. can be serialized back to plaintext
- Platform - lists of features supported by particular platform
- Formatter - configurable SQL statements serializer
- Analyzer - static analysis rules and instrumentation for them


Basic usage:
------------

```
<?php

use ...

$platform = Platform::get(Platform::MYSQL, '8.0'); // version defaults to x.x.99 when no patch number is given
$session = new Session($platform);
$parser = new Parser($session);

// returns a Generator. will not parse anything if you don't iterate over it
$commands = $parser->parse('SELECT foo FROM ...');
foreach ($commands as [$command, $tokenList, $start, $end]) {
    // Parser does not throw exceptions. this allows to parse partially invalid code and not fail on first error
    if ($command instanceof InvalidCommand) {
        $e = $command->getException();
        ...
    }
    ...
}
```


Current state of development:
-----------------------------

where we are now:
- ☑ ~99.9% MySQL language features implemented
- ☑ basic unit tests with serialisation
- ☑ tested against several thousands of tables and migrations
- ☑ parses almost everything from MySQL test suite (no false negatives)
- ☑ fails on almost all error tests from MySQL test suite (no false positives)
- ☑ serialisation testing on MySQL test suite (all SQL features represented as expected)
- ☐ parallelized and automated tests against multiple versions of MySQL test suite
- ☐ mutation testing (handling mutated SQL same as a real DB; extending range beyond MySQL test suite)
- ☐ distinguishing platform versions (parsing for exact patch version of the DB server)
- ☐ porting my migration static analysis tools to this library
- ☐ 100% MySQL language features implemented?
- ☐ release of first stable version?
- ☐ other platforms (MariaDB, SQLite, PostgreSQL...)


Versioning:
-----------

we all love semantic versioning, don't we? : ]

but, this library is a huge undertaking and is still going through a rapid initial development stage. 
i have decided to tag and release versions even in this stage, because i think it is better than nothing and better than
releasing dozens of alpha versions. so **do not expect any backwards compatibility until we leave "0.1"**. 
designing a huge system on a first try is impossible, lots of concepts must settle and click into its place 
and therefore lots of changes are still coming

when using Composer, always lock your dependency on this package to an exact version. e.g. `sqlftw/sqlftw:0.1.4` 


Author:
-------

Vlasta Neubauer, @paranoiq, https://github.com/paranoiq
