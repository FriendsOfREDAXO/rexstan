<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Parser\TokenType as T;
use SqlFtw\Tests\Assert;

require '../bootstrap.php';

// NUMBER
$tokens = Assert::tokens(' 1 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER | T::INT | T::UINT, '1', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 2);

$tokens = Assert::tokens(' 123 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER | T::INT | T::UINT, '123', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 4);

$tokens = Assert::tokens(' +123 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER | T::INT, '+123', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 5);

$tokens = Assert::tokens(' -123 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER | T::INT, '-123', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 5);

$tokens = Assert::tokens(' --123 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER | T::INT, '123', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 6);

$tokens = Assert::tokens(' ---123 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER | T::INT, '-123', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 7);

$tokens = Assert::tokens(' ----123 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER | T::INT, '123', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

$tokens = Assert::tokens(' 123.456 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER, '123.456', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

$tokens = Assert::tokens(' 123. ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER, '123.', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 5);

$tokens = Assert::tokens(' .456 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER, '.456', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 5);

$tokens = Assert::tokens(' 1.23e4 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER, '1.23e4', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 7);

$tokens = Assert::tokens(' 1.23E4 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER, '1.23e4', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 7);

$tokens = Assert::tokens(' 1.23e+4 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER, '1.23e+4', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

$tokens = Assert::tokens(' 1.23e-4 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER, '1.23e-4', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 8);

$tokens = Assert::tokens(' 123.e4 ', 3);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::VALUE | T::NUMBER, '123.e4', 1);
Assert::token($tokens[2], T::WHITESPACE, ' ', 7);

$tokens = Assert::tokens(' 1.23e', 2);
Assert::invalidToken($tokens[1], T::VALUE | T::NUMBER | T::INVALID, '~^Invalid number exponent~', 1);

$tokens = Assert::tokens(' 1.23e+', 2);
Assert::invalidToken($tokens[1], T::VALUE | T::NUMBER | T::INVALID, '~^Invalid number exponent~', 1);

$tokens = Assert::tokens(' 1.23e-', 2);
Assert::invalidToken($tokens[1], T::VALUE | T::NUMBER | T::INVALID, '~^Invalid number exponent~', 1);

$tokens = Assert::tokens(' 1.23ef', 3);
Assert::invalidToken($tokens[1], T::VALUE | T::NUMBER | T::INVALID, '~^Invalid number exponent~', 1);

$tokens = Assert::tokens(' 1.23e+f', 3);
Assert::invalidToken($tokens[1], T::VALUE | T::NUMBER | T::INVALID, '~^Invalid number exponent~', 1);

$tokens = Assert::tokens(' 1.23e-f', 3);
Assert::invalidToken($tokens[1], T::VALUE | T::NUMBER | T::INVALID, '~^Invalid number exponent~', 1);

$tokens = Assert::tokens(' -(1) ', 6);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::SYMBOL | T::OPERATOR, '-', 1);
Assert::token($tokens[2], T::SYMBOL, '(', 2);
Assert::token($tokens[3], T::VALUE | T::NUMBER | T::INT | T::UINT, '1', 3);
Assert::token($tokens[4], T::SYMBOL, ')', 4);
Assert::token($tokens[5], T::WHITESPACE, ' ', 5);

$tokens = Assert::tokens(' -(-1) ', 6);
Assert::token($tokens[0], T::WHITESPACE, ' ', 0);
Assert::token($tokens[1], T::SYMBOL | T::OPERATOR, '-', 1);
Assert::token($tokens[2], T::SYMBOL, '(', 2);
Assert::token($tokens[3], T::VALUE | T::NUMBER | T::INT, '-1', 3);
Assert::token($tokens[4], T::SYMBOL, ')', 5);
Assert::token($tokens[5], T::WHITESPACE, ' ', 6);
