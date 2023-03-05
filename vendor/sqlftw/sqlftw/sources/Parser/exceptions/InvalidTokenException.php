<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser;

use Dogma\ExceptionValueFormatter;
use Dogma\Str;
use Throwable;
use function implode;
use function is_array;
use function sprintf;

class InvalidTokenException extends ParserException
{

    /**
     * @param string|list<string>|null $expectedValue
     */
    public static function tokens(
        int $expectedToken,
        int $tokenMask,
        $expectedValue,
        ?Token $token,
        TokenList $tokenList,
        ?Throwable $previous = null
    ): self
    {
        $expectedToken = implode('|', TokenType::getByValue($expectedToken)->getConstantNames());
        if ($tokenMask !== 0) {
            $expectedToken .= ' ~' . implode('|', TokenType::getByValue($tokenMask)->getConstantNames());
        }

        if ($expectedValue !== null) {
            if (is_array($expectedValue)) {
                $expectedValue = Str::join($expectedValue, ', ', ' or ', 120, '...');
            }
            $expectedValue = " with value " . $expectedValue;
        }

        $context = self::formatContext($tokenList);

        if ($token === null) {
            return new self(sprintf(
                "Expected token %s%s, but end of query found instead at position %d in:\n%s",
                $expectedToken,
                $expectedValue,
                $tokenList->getPosition(),
                $context
            ), $tokenList, $previous);
        }

        $actualToken = implode('|', TokenType::getByValue($token->type)->getConstantNames());
        $actualValue = ExceptionValueFormatter::format($token->value);

        return new self(sprintf(
            "Expected token %s%s, but token %s with value %s found instead at position %d in:\n%s",
            $expectedToken,
            $expectedValue,
            $actualToken,
            $actualValue,
            $tokenList->getPosition(),
            $context
        ), $tokenList, $previous);
    }

}
