<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Parser\TokenType as T;
use SqlFtw\Tests\Assert;

require '../bootstrap.php';

// BINARY_LITERAL
$tokens = Assert::tokens(' 0b0101 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::BINARY_LITERAL, '0101', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 7);

$tokens = Assert::tokens(' 0b0102 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::UNQUOTED_NAME, '0b0102', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 7);

$tokens = Assert::tokens(' b\'0101\' ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::BINARY_LITERAL, '0101', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

$tokens = Assert::tokens(' B\'0101\' ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::BINARY_LITERAL, '0101', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

$tokens = Assert::tokens(' b\'0102\' ', 3);
Assert::invalidToken($tokens[1], T::VALUE | T::BINARY_LITERAL | T::INVALID, '~^Invalid binary literal~', 1);

$tokens = Assert::tokens(' b\'0101 ', 2);
Assert::invalidToken($tokens[1], T::VALUE | T::BINARY_LITERAL | T::INVALID, '~^Invalid binary literal~', 1);

// HEXADECIMAL_LITERAL
$tokens = Assert::tokens(' 0x12AB ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::HEXADECIMAL_LITERAL, '12ab', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 7);

$tokens = Assert::tokens(' 0x12AG ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::NAME | T::UNQUOTED_NAME, '0x12AG', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 7);

$tokens = Assert::tokens(' x\'12AB\' ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::HEXADECIMAL_LITERAL, '12ab', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

$tokens = Assert::tokens(' X\'12AB\' ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::HEXADECIMAL_LITERAL, '12ab', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

$tokens = Assert::tokens(' x\'12AG\' ', 3);
Assert::invalidToken($tokens[1], T::VALUE | T::HEXADECIMAL_LITERAL | T::INVALID, '~^Invalid hexadecimal literal~', 1);

$tokens = Assert::tokens(' x\'12AB ', 2);
Assert::invalidToken($tokens[1], T::VALUE | T::HEXADECIMAL_LITERAL | T::INVALID, '~^Invalid hexadecimal literal~', 1);
