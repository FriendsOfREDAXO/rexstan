<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Parser\TokenType as T;
use SqlFtw\Sql\SqlMode;
use SqlFtw\Tests\Assert;

require '../bootstrap.php';

// OPERATOR
$tokens = Assert::tokens(' AND ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::KEYWORD | T::RESERVED | T::NAME | T::UNQUOTED_NAME | T::OPERATOR, 'AND', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 4);

$tokens = Assert::tokens(' and ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::KEYWORD | T::RESERVED | T::NAME | T::UNQUOTED_NAME | T::OPERATOR, 'and', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 4);


// RESERVED
$tokens = Assert::tokens(' SELECT ', 3);
Assert::count($tokens, 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::KEYWORD | T::RESERVED | T::NAME | T::UNQUOTED_NAME, 'SELECT', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 7);

$tokens = Assert::tokens(' select ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::KEYWORD | T::RESERVED | T::NAME | T::UNQUOTED_NAME, 'select', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 7);


// KEYWORD
$tokens = Assert::tokens(' JOIN ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::KEYWORD | T::RESERVED | T::NAME | T::UNQUOTED_NAME, 'JOIN', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 5);

$tokens = Assert::tokens(' join ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::KEYWORD | T::RESERVED | T::NAME | T::UNQUOTED_NAME, 'join', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 5);


// UNQUOTED_NAME
$tokens = Assert::tokens(' NAME1 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::UNQUOTED_NAME, 'NAME1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 6);

$tokens = Assert::tokens(' name1 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::UNQUOTED_NAME, 'name1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 6);


// DOUBLE_QUOTED_STRING
$tokens = Assert::tokens(' "string1" ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::DOUBLE_QUOTED_STRING, 'string1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 10);

// invalid
$tokens = Assert::tokens(' "string1', 2);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::DOUBLE_QUOTED_STRING | T::INVALID, '"string1', 1);

// with ANSI_QUOTES mode enabled
$tokens = Assert::tokens(' "string1" ', 3, SqlMode::ANSI_QUOTES);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::DOUBLE_QUOTED_STRING, 'string1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 10);

// escaping quotes
$tokens = Assert::tokens(' "str\\"ing1" ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::DOUBLE_QUOTED_STRING, 'str"ing1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 12);

$tokens = Assert::tokens(' "str\\"" ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::DOUBLE_QUOTED_STRING, 'str"', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);


// SINGLE_QUOTED_STRING
$tokens = Assert::tokens(" 'string1' ", 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::SINGLE_QUOTED_STRING, 'string1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 10);

// invalid
$tokens = Assert::tokens(" 'string1", 2);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::SINGLE_QUOTED_STRING | T::INVALID, "'string1", 1);

// doubling quotes
$tokens = Assert::tokens(" 'str''ing1' ", 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::SINGLE_QUOTED_STRING, "str'ing1", 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 12);

// escaping quotes
$tokens = Assert::tokens(" 'str\\'ing1' ", 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::SINGLE_QUOTED_STRING, "str'ing1", 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 12);

$tokens = Assert::tokens(" 'str\\'' ", 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::SINGLE_QUOTED_STRING, "str'", 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);


$tokens = Assert::tokens(" '\\\\' ", 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::SINGLE_QUOTED_STRING, "\\", 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 5);

$tokens = Assert::tokens(" 'string1\\\\' ", 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::SINGLE_QUOTED_STRING, 'string1\\', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 12);

$tokens = Assert::tokens(" 'str\\\\\\'ing1' ", 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::SINGLE_QUOTED_STRING, "str\\'ing1", 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 14);

// with escaping disabled
$tokens = Assert::tokens(" 'string1\\' ", 3, SqlMode::NO_BACKSLASH_ESCAPES);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::SINGLE_QUOTED_STRING, 'string1\\', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 11);


// BACKTICK_QUOTED_STRING
$tokens = Assert::tokens(' `name1` ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::BACKTICK_QUOTED_STRING, 'name1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

// invalid
$tokens = Assert::tokens(' `name1', 2);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::BACKTICK_QUOTED_STRING | T::INVALID, '`name1', 1);


// N strings
$tokens = Assert::tokens(" N'\\\\' ", 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::STRING | T::SINGLE_QUOTED_STRING, "\\", 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 6);


// AT_VARIABLE
$tokens = Assert::tokens(' @var1 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::AT_VARIABLE, '@var1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 6);

$tokens = Assert::tokens(' @`var1` ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::AT_VARIABLE | T::BACKTICK_QUOTED_STRING, '@var1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

$tokens = Assert::tokens(' @\'var1\' ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::AT_VARIABLE | T::SINGLE_QUOTED_STRING, '@var1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

$tokens = Assert::tokens(' @"var1" ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::AT_VARIABLE | T::DOUBLE_QUOTED_STRING, '@var1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

$tokens = Assert::tokens(' name1@name2 ', 4);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::UNQUOTED_NAME, 'name1', 1);
Assert::token($tokens[2], T::NAME | T::AT_VARIABLE, '@name2', 6);
Assert::token($tokens[3], T::WHITESPACE, ' ', 12);
