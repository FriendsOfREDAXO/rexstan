<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Parser\TokenType as T;
use SqlFtw\Tests\Assert;

require '../bootstrap.php';

// BLOCK_COMMENT
$tokens = Assert::tokens(' /* comment */ ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::BLOCK_COMMENT, '/* comment */', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 14);

$tokens = Assert::tokens(' /* comment /* inside */ comment */ ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::BLOCK_COMMENT, '/* comment /* inside */ comment */', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 35);

// according to /suite/innodb/t/innodb_bug48024.test this seems to be a valid comment, but the test is buggy and does
// not really test what it seems to. MySQL does not terminate the comment, but does not produce any error.
// implementing correct behavior (unterminated comment error), like PostgreSQL does
$tokens = Assert::tokens(' /*/ comment /*/ ', 2);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::BLOCK_COMMENT | T::INVALID, '/*/ comment /*/ ', 1);

$tokens = Assert::tokens(' /* comment ', 2);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::BLOCK_COMMENT | T::INVALID, '/* comment ', 1);

// HINT_COMMENT (not parsed)
$tokens = Assert::tokens(' /*+ content */ ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::BLOCK_COMMENT | T::OPTIMIZER_HINT_COMMENT, '/*+ content */', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 15);

// HINT_COMMENT (parsed)
$tokens = Assert::tokens('SELECT /*+ content */ ', 8);
Assert::token($tokens[0], T::NAME | T::UNQUOTED_NAME | T::KEYWORD | T::RESERVED, 'SELECT', 0);
Assert::token($tokens[1], T::WHITESPACE, ' ', 6);
Assert::token($tokens[2], T::OPTIMIZER_HINT_START, '/*+', 7);
Assert::token($tokens[3], T::WHITESPACE, ' ', 10);
Assert::token($tokens[4], T::NAME | T::UNQUOTED_NAME, 'content', 11);
Assert::token($tokens[5], T::WHITESPACE, ' ', 18);
Assert::token($tokens[6], T::OPTIMIZER_HINT_END, '*/', 19);
Assert::token($tokens[7], T::WHITESPACE, ' ', 21);

// OPTIONAL_COMMENT
$tokens = Assert::tokens(' /*!90000 comment */ ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::BLOCK_COMMENT | T::OPTIONAL_COMMENT, '/*!90000 comment */', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 20);

$tokens = Assert::tokens(' /*!2*/ ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::BLOCK_COMMENT | T::OPTIONAL_COMMENT | T::INVALID, '/*!2*/', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 7);


// DOUBLE_HYPHEN_COMMENT
$tokens = Assert::tokens(' -- comment', 2);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::DOUBLE_HYPHEN_COMMENT, '-- comment', 1);

$tokens = Assert::tokens(" -- comment\n ", 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::DOUBLE_HYPHEN_COMMENT, "-- comment\n", 1);
Assert::token($tokens[2], T::WHITESPACE, " ", 12);

// DOUBLE_SLASH_COMMENT
$tokens = Assert::tokens(' // comment', 2);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::DOUBLE_SLASH_COMMENT, '// comment', 1);

$tokens = Assert::tokens(" // comment\n ", 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::DOUBLE_SLASH_COMMENT, "// comment\n", 1);
Assert::token($tokens[2], T::WHITESPACE, " ", 12);

// HASH_COMMENT
$tokens = Assert::tokens(' # comment', 2);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::HASH_COMMENT, '# comment', 1);

$tokens = Assert::tokens(" # comment\n ", 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::COMMENT | T::HASH_COMMENT, "# comment\n", 1);
Assert::token($tokens[2], T::WHITESPACE, " ", 11);
