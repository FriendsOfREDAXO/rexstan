<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser;

use Throwable;
use function array_map;
use function array_slice;
use function implode;
use function max;
use function min;

class ParserException extends ParsingException
{

    private TokenList $tokenList;

    public function __construct(string $message, TokenList $tokenList, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);

        $this->tokenList = $tokenList;
    }

    public function getTokenList(): TokenList
    {
        return $this->tokenList;
    }

    protected static function formatContext(TokenList $tokenList): string
    {
        $before = 30;
        $after = 10;
        $start = max($tokenList->getPosition() - $before, 0);
        $prefix = $before - min(max($before - $tokenList->getPosition(), 0), $before);
        $tokens = array_slice($tokenList->getTokens(), $start, $before + $after + 1);
        $separator = ($tokenList->getAutoSkip() & TokenType::WHITESPACE) === 0 ? ' ' : '';

        $context = '"…' . implode($separator, array_map(static function (Token $token) {
            return $token->original ?? $token->value;
        }, array_slice($tokens, 0, $prefix)));

        if (isset($tokens[$prefix])) {
            $context .= '»' . ($tokens[$prefix]->original ?? $tokens[$prefix]->value) . '«';
            $context .= implode($separator, array_map(static function (Token $token) {
                return $token->original ?? $token->value;
            }, array_slice($tokens, $prefix + 1))) . '…"';
        } else {
            $context .= '»«"';
        }

        return $context;
    }

}
