<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Platform\Platform;
use SqlFtw\Session\Session;
use SqlFtw\Tests\Assert;

require '../bootstrap.php';

$session = new Session(Platform::get(Platform::MYSQL, '5.7'));

$ws = new Token(TokenType::WHITESPACE, 0, 1, 'ws');
$comment = new Token(TokenType::COMMENT, 1, 1, 'comment');
$value = new Token(TokenType::VALUE, 2, 1, 'value');
$name = new Token(TokenType::NAME, 2, 1, 'name');

$tokenList = new TokenList([$ws, $comment, $value, $ws, $comment, $name, $ws, $comment], $session);
$tokenList->setAutoSkip(TokenType::WHITESPACE | TokenType::COMMENT);

getLast:
Assert::same($tokenList->getLast()->value, $ws->value);

$tokenList->rewind(1);
Assert::same($tokenList->getLast()->value, $ws->value);

$tokenList->rewind(2);
Assert::same($tokenList->getLast()->value, $ws->value);

$tokenList->rewind(3);
Assert::same($tokenList->getLast()->value, $value->value);

$tokenList->rewind(4);
Assert::same($tokenList->getLast()->value, $value->value);

$tokenList->rewind(5);
Assert::same($tokenList->getLast()->value, $value->value);

$tokenList->rewind(6);
Assert::same($tokenList->getLast()->value, $name->value);

$tokenList->rewind(7);
Assert::same($tokenList->getLast()->value, $name->value);

$tokenList->rewind(8);
Assert::same($tokenList->getLast()->value, $name->value);

$tokenList->rewind(9);
Assert::same($tokenList->getLast()->value, $name->value);


expectNonReservedName:
$tokenList = Assert::tokenList(' 1 ');
Assert::exception(static function () use ($tokenList): void {
    $tokenList->expectNonReservedName(null);
}, InvalidTokenException::class);

$tokenList = Assert::tokenList(' select ');
Assert::exception(static function () use ($tokenList): void {
    $tokenList->expectNonReservedName(null);
}, InvalidTokenException::class);

$tokenList = Assert::tokenList(' `select` ');
Assert::same($tokenList->expectNonReservedName(null), 'select');
