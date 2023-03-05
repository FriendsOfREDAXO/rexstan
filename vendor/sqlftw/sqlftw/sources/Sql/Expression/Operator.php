<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;
use function in_array;

class Operator extends SqlEnum
{

    // assign
    public const ASSIGN = ':=';

    // boolean
    public const AND = Keyword::AND;
    public const OR = Keyword::OR;
    public const XOR = Keyword::XOR;
    public const NOT = Keyword::NOT;
    public const AMPERSANDS = '&&';
    public const PIPES = '||'; // OR or CONCAT
    public const EXCLAMATION = '!';

    // comparison
    public const EQUAL = '=';
    public const NOT_EQUAL = '!=';
    public const LESS_OR_GREATER = '<>';
    public const LESS = '<';
    public const LESS_OR_EQUAL = '<=';
    public const GREATER = '>';
    public const GREATER_OR_EQUAL = '>=';
    public const SAFE_EQUAL = '<=>';
    public const BETWEEN = Keyword::BETWEEN;
    public const NOT_BETWEEN = Keyword::NOT . ' ' . Keyword::BETWEEN;

    // arithmetic
    public const PLUS = '+';
    public const MINUS = '-';
    public const MULTIPLY = '*';
    public const DIVIDE = '/';
    public const DIV = Keyword::DIV;
    public const MOD = Keyword::MOD;
    public const MODULO = '%';

    // binary
    public const BIT_AND = '&';
    public const BIT_INVERT = '~';
    public const BIT_OR = '|';
    public const BIT_XOR = '^';
    public const LEFT_SHIFT = '<<';
    public const RIGHT_SHIFT = '>>';

    // test
    public const IS = Keyword::IS;
    public const IS_NOT = Keyword::IS . ' ' . Keyword::NOT;
    public const LIKE = Keyword::LIKE;
    public const NOT_LIKE = Keyword::NOT . ' ' . Keyword::LIKE;
    public const ESCAPE = Keyword::ESCAPE;
    public const REGEXP = Keyword::REGEXP;
    public const NOT_REGEXP = Keyword::NOT . ' ' . Keyword::REGEXP;
    public const RLIKE = Keyword::RLIKE;
    public const NOT_RLIKE = Keyword::NOT . ' ' . Keyword::RLIKE;
    public const SOUNDS_LIKE = Keyword::SOUNDS . ' ' . Keyword::LIKE;

    // case
    public const CASE = Keyword::CASE;
    public const WHEN = Keyword::WHEN;
    public const THEN = Keyword::THEN;
    public const ELSE = Keyword::ELSE;
    public const END = Keyword::END;

    // set & quantifiers
    public const IN = Keyword::IN;
    public const NOT_IN = Keyword::NOT . ' ' . Keyword::IN;
    public const ANY = Keyword::ANY;
    public const SOME = Keyword::SOME;
    public const ALL = Keyword::ALL;
    public const EXISTS = Keyword::EXISTS;

    // collation
    public const BINARY = Keyword::BINARY;

    // JSON
    public const JSON_EXTRACT = '->';
    public const JSON_EXTRACT_UNQUOTE = '->>';
    public const MEMBER_OF = Keyword::MEMBER . ' ' . Keyword::OF;

    public function isUnary(): bool
    {
        return in_array(
            $this->getValue(),
            [self::PLUS, self::MINUS, self::NOT, self::EXCLAMATION, self::BIT_INVERT, self::EXISTS, self::BINARY],
            true
        );
    }

    public function isBinary(): bool
    {
        return !in_array(
            $this->getValue(),
            [self::EXCLAMATION, self::BIT_INVERT, self::EXISTS, self::BINARY, self::BETWEEN, self::ESCAPE],
            true
        );
    }

    public function checkUnary(): void
    {
        if (!$this->isUnary()) {
            throw new InvalidDefinitionException('Unary operator expected.');
        }
    }

    public function checkBinary(): void
    {
        if (!$this->isBinary()) {
            throw new InvalidDefinitionException('Binary operator expected.');
        }
    }

    public static function checkTernary(self $left, self $right): void
    {
        if (!(
            ($left->getValue() === self::BETWEEN && $right->getValue() === self::AND)
            || ($left->getValue() === self::NOT_BETWEEN && $right->getValue() === self::AND)
            || ($left->getValue() === self::LIKE && $right->getValue() === self::ESCAPE)
            || ($left->getValue() === self::NOT_LIKE && $right->getValue() === self::ESCAPE)
        )) {
            throw new InvalidDefinitionException('Invalid ternary operator: ' . $left->getValue() . ' ' . $right->getValue());
        }
    }

}
