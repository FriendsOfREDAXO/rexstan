<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs-disable Squiz.WhiteSpace.MemberVarSpacing.AfterComment

namespace SqlFtw\Parser;

use Dogma\Language\Encoding;
use Dogma\Str;
use InvalidArgumentException;
use LogicException;
use SqlFtw\Parser\TokenType as T;
use SqlFtw\Platform\Platform;
use SqlFtw\Session\Session;
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Collation;
use SqlFtw\Sql\CommonTableExpressionType;
use SqlFtw\Sql\Ddl\Table\Option\StorageEngine;
use SqlFtw\Sql\Dml\Error\SqlState;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\BinaryLiteral;
use SqlFtw\Sql\Expression\HexadecimalLiteral;
use SqlFtw\Sql\Expression\IntLiteral;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\QualifiedName;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\Expression\SizeLiteral;
use SqlFtw\Sql\Expression\StringLiteral;
use SqlFtw\Sql\Expression\StringValue;
use SqlFtw\Sql\Expression\UintLiteral;
use SqlFtw\Sql\Expression\Value;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\InvalidEnumValueException;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\MysqlVariable;
use SqlFtw\Sql\Routine\RoutineType;
use SqlFtw\Sql\SqlEnum;
use SqlFtw\Sql\SubqueryType;
use SqlFtw\Sql\UserName;
use function array_merge;
use function array_pop;
use function array_slice;
use function array_values;
use function count;
use function end;
use function explode;
use function implode;
use function in_array;
use function is_numeric;
use function is_scalar;
use function ltrim;
use function mb_strlen;
use function min;
use function preg_match;
use function rtrim;
use function strlen;
use function strtolower;
use function strtoupper;
use function substr;
use function ucfirst;

/**
 * List of lexer tokens and a local parser state (parser state, that does not persist between statements)
 *
 * Contained tokens represent either one or more SQL statements
 *
 * Method names explanation:
 * - seekFoo() - seeks token forward without consuming it
 * - hasFoo() - consume token if exists and return bool
 * - hasFoos() - consume all tokens if exist and return bool
 * - hasAnyFoo - consume one token and return bool
 * - getFoo() - consume token if exists and return it
 * - getFoos() - consume all tokens if exist and return it (serialized)
 * - getAnyFoo - consume one token if exists and return it
 * - passFoo() - consume token if exists, return nothing
 * - passFoos() - consume all tokens if they exist, return nothing
 * - passAnyFoo() - consume one token if exists, return nothing
 * - expectFoo() - consume token or throw an exception
 * - expectFoos() - consume all tokens or throw an exception
 * - expectAnyFoo() - consume one token or throw an exception
 * - missingFoo() - always throws an exception (just formats the error message)
 */
class TokenList
{

    /** @var non-empty-list<Token> */
    private array $tokens;

    private Platform $platform;

    private Session $session;

    /** @var array<string, int> */
    private array $maxLengths;

    private bool $invalid;

    // parser state ----------------------------------------------------------------------------------------------------

    private int $autoSkip;

    private int $position = 0;

    /** @var list<RoutineType::*> Are we inside a function, procedure, trigger or event definition? */
    private array $inRoutine = [];

    /** @var list<SubqueryType::*> Are we inside a subquery, and what type? */
    private array $inSubquery = [];

    /** @var bool Are we inside a UNION|EXCEPT|INTERSECT expression? */
    private bool $inQueryExpression = false;

    /** @var CommonTableExpressionType::WITH*|null Are we inside a Common Table Expression? */
    private ?string $inCommonTableExpression = null;

    /** @var bool Are we inside a prepared statement declaration? */
    private bool $inPrepared = false;

    /** @var bool Should we expect a delimiter after the command? (command directly embedded into another command) */
    private bool $inEmbedded = false;

    private string $trailingDelimiter = '';

    /**
     * @param non-empty-list<Token> $tokens
     */
    public function __construct(array $tokens, Platform $platform, Session $session, int $autoSkip = 0, bool $invalid = false)
    {
        $this->tokens = $tokens;
        $this->platform = $platform;
        $this->session = $session;
        $this->maxLengths = $platform->getMaxLengths();
        $this->autoSkip = $autoSkip;
        $this->invalid = $invalid;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function using(?string $platform = null, ?int $minVersion = null, ?int $maxVersion = null): bool
    {
        return $this->platform->matches($platform, $minVersion, $maxVersion);
    }

    public function check(string $feature, ?int $minVersion = null, ?int $maxVersion = null, ?string $platform = null): void
    {
        if (!$this->platform->matches($platform, $minVersion, $maxVersion)) {
            throw new InvalidVersionException($feature, $this->platform, $this);
        }
    }

    // state -----------------------------------------------------------------------------------------------------------

    public function invalid(): bool
    {
        return $this->invalid;
    }

    public function inDefinition(): bool
    {
        return $this->inRoutine() !== null && $this->inPrepared();
    }

    public function inRoutine(): ?string
    {
        return $this->inRoutine !== [] ? end($this->inRoutine) : null;
    }

    /**
     * @param RoutineType::* $type
     */
    public function startRoutine(string $type): void
    {
        $this->inRoutine[] = $type;
    }

    public function endRoutine(): void
    {
        array_pop($this->inRoutine);
    }

    public function inSubquery(): ?string
    {
        return $this->inSubquery !== [] ? end($this->inSubquery) : null;
    }

    /**
     * @param SubqueryType::* $type
     */
    public function startSubquery(string $type): void
    {
        $this->inSubquery[] = $type;
    }

    public function endSubquery(): void
    {
        array_pop($this->inSubquery);
    }

    public function inQueryExpression(): bool
    {
        return $this->inQueryExpression;
    }

    public function startQueryExpression(): void
    {
        $this->inQueryExpression = true;
    }

    public function endQueryExpression(): void
    {
        $this->inQueryExpression = false;
    }

    /**
     * @param CommonTableExpressionType::WITH*|null $type
     * @return bool
     */
    public function inCommonTableExpression(?string $type = null): bool
    {
        if ($type === null) {
            return $this->inCommonTableExpression !== null;
        } else {
            return $this->inCommonTableExpression === $type;
        }
    }

    /**
     * @param CommonTableExpressionType::WITH* $type
     */
    public function startCommonTableExpression(string $type): void
    {
        $this->inCommonTableExpression = $type;
    }

    public function endCommonTableExpression(): void
    {
        $this->inCommonTableExpression = null;
    }

    public function inPrepared(): bool
    {
        return $this->inPrepared;
    }

    public function startPrepared(): void
    {
        $this->inPrepared = true;
    }

    public function inEmbedded(): bool
    {
        return $this->inEmbedded;
    }

    public function startEmbedded(): void
    {
        $this->inEmbedded = true;
    }

    public function endEmbedded(): void
    {
        $this->inEmbedded = false;
    }

    public function finish(): void
    {
        $this->position = count($this->tokens);
    }

    public function isFinished(): bool
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }

        if ($this->position >= count($this->tokens)) {
            return true;
        }

        // check that all remaining tokens can be ignored
        $this->trailingDelimiter = '';
        for ($n = $this->position; $n < count($this->tokens); $n++) {
            $token = $this->tokens[$n];
            if (($token->type & $this->autoSkip) !== 0) {
                continue;
            } elseif (($token->type & T::SYMBOL) !== 0 && $token->value === ';') {
                // trailing ;
                $this->trailingDelimiter .= ';';
            } elseif (($token->type & T::DELIMITER) !== 0) {
                // trailing delimiter
                $this->trailingDelimiter .= $token->value;
            } else {
                $this->trailingDelimiter = '';

                return false;
            }
        }

        // do not reset trailing delimiter on next run
        $this->position = min($n + 1, count($this->tokens)); // @phpstan-ignore-line $n is always defined

        return true;
    }

    public function getTrailingDelimiter(): string
    {
        return $this->trailingDelimiter;
    }

    public function resetTrailingDelimiter(): void
    {
        $this->trailingDelimiter = '';
    }

    public function appendTrailingDelimiter(string $part): void
    {
        $this->trailingDelimiter .= $part;
    }

    // navigation ------------------------------------------------------------------------------------------------------

    public function getPosition(): int
    {
        return $this->position;
    }

    public function rewind(int $position = 0): self
    {
        if ($position < 0) {
            $this->position += $position;
        } elseif ($position > count($this->tokens)) {
            $this->position = count($this->tokens) - 1;
        } else {
            $this->position = $position;
        }

        return $this;
    }

    public function getAutoSkip(): int
    {
        return $this->autoSkip;
    }

    public function setAutoSkip(int $autoSkip): void
    {
        $this->autoSkip = $autoSkip;
    }

    private function doAutoSkip(): void
    {
        $token = $this->tokens[$this->position] ?? null;
        while ($token !== null && ($this->autoSkip & $token->type) !== 0) {
            $this->position++;
            $token = $this->tokens[$this->position] ?? null;
        }
    }

    public function extractRawExpression(int $start): string
    {
        if ($this->autoSkip === 0) {
            throw new LogicException('Raw expression could be incomplete, when whitespace and comments parsing is disabled.');
        }

        $end = $this->position - 1;
        $beginning = true;
        $position = $start;
        $tokens = [];
        while ($position <= $end && isset($this->tokens[$position])) {
            $token = $this->tokens[$position];
            $position++;
            // remove leading whitespace and comments
            if ($beginning && ($token->type & $this->autoSkip) !== 0) {
                continue;
            }
            $tokens[] = $token;
            $beginning = false;
        }

        // remove trailing whitespace and comments
        for ($i = count($tokens) - 1; $i >= 0; $i--) {
            if (($tokens[$i]->type & $this->autoSkip) !== 0) {
                unset($tokens[$i]);
            } else {
                break;
            }
        }

        $expression = '';
        /** @var Token $token */
        foreach ($tokens as $token) {
            $expression .= $token->original ?? $token->value;
        }

        return $expression;
    }

    // contents --------------------------------------------------------------------------------------------------------

    /**
     * @return non-empty-list<Token>
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getStartOffset(): int
    {
        return $this->tokens[0]->position;
    }

    public function getEndOffset(): int
    {
        $token = end($this->tokens);
        $value = $token->original ?? $token->value;

        return $token->position + strlen($value);
    }

    public function append(self $tokenList): void
    {
        $this->tokens = array_merge($this->tokens, $tokenList->tokens);
    }

    public function slice(int $startOffset, int $endOffset): self
    {
        if ($startOffset >= $endOffset) {
            throw new InvalidArgumentException('Start offset should be smaller than end offset');
        }
        /** @var non-empty-list<Token> $tokens */
        $tokens = array_slice($this->tokens, $startOffset, $endOffset - $startOffset + 1);

        return new self($tokens, $this->platform, $this->session, $this->autoSkip);
    }

    /**
     * @param callable(Token $token): bool $filter
     */
    public function filter(callable $filter): self
    {
        /** @var non-empty-list<Token> $tokens */
        $tokens = [];
        foreach ($this->tokens as $token) {
            if (!$filter($token)) {
                $tokens[] = $token;
            }
        }

        return new self($tokens, $this->platform, $this->session, $this->autoSkip);
    }

    /**
     * @param callable(Token $token): Token $mapper
     */
    public function map(callable $mapper): self
    {
        /** @var non-empty-list<Token> $tokens */
        $tokens = [];
        foreach ($this->tokens as $token) {
            $tokens[] = $mapper($token);
        }

        return new self($tokens, $this->platform, $this->session, $this->autoSkip);
    }

    public function getLast(): Token
    {
        $position = $this->position;
        do {
            $position--;
            $token = $this->tokens[$position] ?? null;
        } while ($token !== null && ($this->autoSkip & $token->type) !== 0);

        return $token ?? $this->tokens[0];
    }

    public function getFirstSignificantToken(): ?Token
    {
        foreach ($this->tokens as $token) {
            if (($token->type & (T::WHITESPACE | T::COMMENT)) === 0) {
                return $token;
            }
        }

        return null;
    }

    public function serialize(): string
    {
        static $noSpaceAfter = ['(', '[', '{', '.'];
        static $noSpaceBefore = ['(', ')', ']', '}', '.', ',', ':', ';'];

        $result = '';
        foreach ($this->tokens as $i => $token) {
            $result .= $token->original ?? $token->value;

            if (($this->autoSkip & T::WHITESPACE) !== 0) {
                continue;
            }
            if (($token->type & T::SYMBOL) !== 0 && in_array($token->value, $noSpaceAfter, true)) {
                continue;
            }
            $next = $this->tokens[$i + 1] ?? null;
            if ($next === null) {
                break;
            }
            if (($next->type & T::SYMBOL) !== 0 && in_array($next->value, $noSpaceBefore, true)) {
                continue;
            }
            if (($next->type & T::DELIMITER) !== 0) {
                continue;
            }

            $result .= ' ';
        }

        return $result;
    }

    // seek ------------------------------------------------------------------------------------------------------------

    public function seek(int $type, ?string $value = null, int $maxOffset = 1000): ?Token
    {
        $position = $this->position;
        for ($n = 0; $n < $maxOffset; $n++) {
            if ($this->autoSkip !== 0) {
                $this->doAutoSkip();
            }
            $token = $this->tokens[$this->position] ?? null;
            if ($token === null) {
                break;
            }
            $this->position++;
            if (($token->type & $type) !== 0 && ($value === null || $value === $token->value)) {
                $this->position = $position;

                return $token;
            }
        }
        $this->position = $position;

        return null;
    }

    public function seekKeyword(string $keyword, int $maxOffset): bool
    {
        $position = $this->position;
        for ($n = 0; $n < $maxOffset; $n++) {
            if ($this->autoSkip !== 0) {
                $this->doAutoSkip();
            }
            $token = $this->tokens[$this->position] ?? null;
            if ($token === null) {
                break;
            }
            $this->position++;
            if (($token->type & T::KEYWORD) !== 0 && strtoupper($token->value) === $keyword) {
                $this->position = $position;

                return true;
            }
        }
        $this->position = $position;

        return false;
    }

    public function seekKeywordBefore(string $keyword, string $beforeKeyword, int $maxOffset = 1000): bool
    {
        $position = $this->position;
        for ($n = 0; $n < $maxOffset; $n++) {
            if ($this->autoSkip !== 0) {
                $this->doAutoSkip();
            }
            $token = $this->tokens[$this->position] ?? null;
            if ($token === null) {
                break;
            }
            $this->position++;
            if (($token->type & T::KEYWORD) !== 0) {
                if (strtoupper($token->value) === $keyword) {
                    $this->position = $position;

                    return true;
                } elseif (strtoupper($token->value) === $beforeKeyword) {
                    $this->position = $position;

                    return false;
                }
            }
        }
        $this->position = $position;

        return false;
    }

    // general ---------------------------------------------------------------------------------------------------------

    /**
     * @return never
     */
    public function missing(string $description): void
    {
        throw new InvalidTokenException($description, $this);
    }

    public function expect(int $tokenType, int $tokenMask = 0): Token
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        $token = $this->tokens[$this->position] ?? null;
        if ($token === null || ($token->type & $tokenType) === 0 || ($token->type & $tokenMask) !== 0) {
            throw InvalidTokenException::tokens($tokenType, $tokenMask, null, $token, $this);
        }
        $this->position++;

        return $token;
    }

    /**
     * @phpstan-impure
     */
    public function get(?int $tokenType = null, int $tokenMask = 0, ?string $value = null): ?Token
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        $token = $this->tokens[$this->position] ?? null;
        if ($token === null) {
            return null;
        }
        if ($tokenType !== null && (($token->type & $tokenType) === 0 || ($token->type & $tokenMask) !== 0)) {
            return null;
        }
        if ($value !== null && strtolower($token->value) !== strtolower($value)) {
            return null;
        }

        $this->position++;

        return $token;
    }

    /**
     * @phpstan-impure
     */
    public function has(int $tokenType, ?string $value = null): bool
    {
        return $this->get($tokenType, 0, $value) !== null;
    }

    public function pass(int $tokenType, ?string $value = null): void
    {
        $this->get($tokenType, 0, $value);
    }

    // symbols ---------------------------------------------------------------------------------------------------------

    public function expectSymbol(string $symbol): Token
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        $token = $this->tokens[$this->position] ?? null;
        if ($token === null || ($token->type & T::SYMBOL) === 0) {
            throw InvalidTokenException::tokens(T::SYMBOL, 0, $symbol, $token, $this);
        }
        if ($token->value !== $symbol) {
            throw InvalidTokenException::tokens(T::SYMBOL, 0, $symbol, $token, $this);
        }
        $this->position++;

        return $token;
    }

    /**
     * @phpstan-impure
     */
    public function hasSymbol(string $symbol): bool
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        $token = $this->tokens[$this->position] ?? null;
        if ($token !== null && ($token->type & T::SYMBOL) !== 0 && $token->value === $symbol) {
            $this->position++;

            return true;
        } else {
            return false;
        }
    }

    /**
     * @phpstan-impure
     */
    public function passSymbol(string $symbol): void
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        $token = $this->tokens[$this->position] ?? null;
        if ($token !== null && ($token->type & T::SYMBOL) !== 0 && $token->value === $symbol) {
            $this->position++;
        }
    }

    // operators -------------------------------------------------------------------------------------------------------

    public function expectOperator(string $operator): string
    {
        $token = $this->expect(T::OPERATOR);
        $upper = strtoupper($token->value);
        if ($upper !== $operator) {
            $this->position--;

            throw InvalidTokenException::tokens(T::OPERATOR, 0, $operator, $token, $this);
        }

        return $upper;
    }

    /**
     * @phpstan-impure
     */
    public function hasOperator(string $operator): bool
    {
        $position = $this->position;

        $token = $this->get(T::OPERATOR);
        if ($token === null) {
            return false;
        } elseif (strtoupper($token->value) === $operator) {
            return true;
        } else {
            $this->position = $position;

            return false;
        }
    }

    public function expectAnyOperator(string ...$operators): string
    {
        $token = $this->expect(T::OPERATOR);
        $upper = strtoupper($token->value);
        if (!in_array($upper, $operators, true)) {
            $this->position--;

            throw InvalidTokenException::tokens(T::OPERATOR, 0, array_values($operators), $token, $this);
        }

        return $token->value;
    }

    public function getAnyOperator(string ...$operators): ?string
    {
        $position = $this->position;

        $token = $this->get(T::OPERATOR);
        if ($token === null || !in_array(strtoupper($token->value), $operators, true)) {
            $this->position = $position;

            return null;
        }

        return strtoupper($token->value);
    }

    // numbers ---------------------------------------------------------------------------------------------------------

    public function expectUnsignedInt(): string
    {
        return $this->expect(T::UINT)->value;
    }

    public function getUnsignedInt(): ?string
    {
        $token = $this->get(T::UINT);
        if ($token === null) {
            return null;
        }

        return $token->value;
    }

    public function expectInt(): string
    {
        return $this->expect(T::INT)->value;
    }

    public function expectIntLike(): Value
    {
        $number = $this->expect(T::INT | T::STRING | T::HEXADECIMAL_LITERAL | T::BINARY_LITERAL);
        $value = $number->value;
        if (($number->type & T::STRING) !== 0 && preg_match('~^(?:0|-[1-9][0-9]*)$~', $value) === 0) {
            throw new InvalidValueException('integer', $this);
        }

        if (($number->type & T::HEXADECIMAL_LITERAL) !== 0) {
            return new HexadecimalLiteral($value);
        } elseif (($number->type & T::BINARY_LITERAL) !== 0) {
            return new BinaryLiteral($value);
        } elseif (($number->type & T::UINT) !== 0) {
            return new UintLiteral($value);
        } else {
            return new IntLiteral($value);
        }
    }

    public function expectSize(): SizeLiteral
    {
        $token = $this->expect(T::UINT | T::NAME);
        if (($token->type & T::UINT) !== 0) {
            return new SizeLiteral($token->value);
        }

        if (preg_match(SizeLiteral::REGEXP, $token->value) === 0) {
            throw new InvalidValueException('size', $this);
        }

        return new SizeLiteral($token->value);
    }

    public function expectBool(): bool
    {
        // TRUE, FALSE, ON, OFF, 1, 0, Y, N, T, F
        $value = $this->expect(T::VALUE)->value;

        if ($value === '1' || $value === 'Y' || $value === 'T' || $value === 'y' || $value === 't') {
            return true;
        } elseif ($value === '0' || $value === 'N' || $value === 'F' || $value === 'n' || $value === 'f' || $value === '') {
            return false;
        }

        throw new InvalidValueException("boolean", $this);
    }

    public function expectYesNo(): bool
    {
        $value = $this->expect(T::VALUE)->value;

        if ($value === 'Y' || $value === 'y') {
            return true;
        } elseif ($value === 'N' || $value === 'n') {
            return false;
        }

        throw new InvalidValueException("Y or N", $this);
    }

    public function expectUuid(): string
    {
        $token = $this->expect(T::UUID | T::STRING);
        if (($token->type & T::STRING) !== 0) {
            if (preg_match(Lexer::UUID_REGEXP, $token->value) === 0) {
                throw new InvalidValueException('uuid', $this);
            }
        }

        return $token->value;
    }

    // strings ---------------------------------------------------------------------------------------------------------

    public function expectString(): string
    {
        return $this->expect(T::STRING)->value;
    }

    public function getString(): ?string
    {
        $token = $this->get(T::STRING);

        return $token !== null ? $token->value : null;
    }

    public function expectStringValue(): StringValue
    {
        $position = $this->position;
        $token = $this->expect(T::STRING | T::HEXADECIMAL_LITERAL | T::BINARY_LITERAL | T::UNQUOTED_NAME);

        // charset introducer
        $charset = null;
        if (($token->type & T::UNQUOTED_NAME) !== 0) {
            $charset = substr(strtolower($token->value), 1);
            if ($token->value[0] === '_' && Charset::isValidValue($charset)) {
                $charset = new Charset($charset);
                $token = $this->expect(T::STRING | T::HEXADECIMAL_LITERAL | T::BINARY_LITERAL);
            } else {
                $charset = null;
                $this->position = $position;

                $token = $this->expect(T::STRING | T::HEXADECIMAL_LITERAL | T::BINARY_LITERAL);
            }
        }

        if (($token->type & T::HEXADECIMAL_LITERAL) !== 0) {
            return new HexadecimalLiteral($token->value, $charset);
        } elseif (($token->type & T::BINARY_LITERAL) !== 0) {
            return new BinaryLiteral($token->value, $charset);
        } else {
            /** @var non-empty-list<string> $values */
            $values = [$token->value];
            while (($next = $this->getString()) !== null) {
                $values[] = $next;
            }

            return new StringLiteral($values, $charset);
        }
    }

    public function getStringValue(): ?StringValue
    {
        $position = $this->position;
        $token = $this->get(T::STRING | T::HEXADECIMAL_LITERAL | T::BINARY_LITERAL | T::UNQUOTED_NAME);
        if ($token === null) {
            return null;
        }

        // charset introducer
        $charset = null;
        if (($token->type & T::UNQUOTED_NAME) !== 0) {
            $lower = strtolower($token->value);
            if ($lower === 'n') {
                // todo: keep?
                $token = $this->get(T::STRING | T::HEXADECIMAL_LITERAL | T::BINARY_LITERAL);
            } else {
                $lower = substr($lower, 1);
                if ($token->value[0] === '_' && Charset::isValidValue($lower)) {
                    $charset = new Charset($lower);
                    $token = $this->get(T::STRING | T::HEXADECIMAL_LITERAL | T::BINARY_LITERAL);
                } else {
                    $this->position = $position;

                    return null;
                }
            }
        }
        if ($token === null) {
            $this->position = $position;

            return null;
        }

        if (($token->type & T::HEXADECIMAL_LITERAL) !== 0) {
            return new HexadecimalLiteral($token->value, $charset);
        } elseif (($token->type & T::BINARY_LITERAL) !== 0) {
            return new BinaryLiteral($token->value, $charset);
        } else {
            /** @var non-empty-list<string> $values */
            $values = [$token->value];
            while (($next = $this->getString()) !== null) {
                $values[] = $next;
            }

            return new StringLiteral($values, $charset);
        }
    }

    public function expectNonReservedNameOrString(): string
    {
        return $this->expect(T::NAME | T::STRING, T::RESERVED | T::AT_VARIABLE)->value;
    }

    public function getNonReservedNameOrString(): ?string
    {
        $token = $this->get(T::NAME | T::STRING, T::RESERVED | T::AT_VARIABLE);

        return $token !== null ? $token->value : null;
    }

    /**
     * @param string|int ...$values
     * @return string|int|null
     */
    public function getVariableEnumValue(...$values)
    {
        $token = $this->get(T::NAME | T::STRING | T::INT);
        if ($token === null) {
            return null;
        }

        $value = strtoupper($token->value);
        if (in_array($value, $values, true)) {
            return $value;
        } elseif (is_numeric($value)) {
            $int = (int) $value;
            if (($value === (string) $int) && in_array($int, $values, true)) {
                return $value;
            }
        }

        $this->position--;

        return null;
    }

    /**
     * @template T of SqlEnum
     * @param class-string<T> $className
     * @return T
     */
    public function expectNameEnum(string $className): SqlEnum
    {
        $value = $this->expectName(EntityType::GENERAL);

        try {
            /** @var T $enum */
            $enum = new $className($value);

            return $enum;
        } catch (InvalidEnumValueException $e) {
            $this->position--;
            /** @var list<string> $values */
            $values = $className::getAllowedValues();

            throw InvalidTokenException::tokens(T::NAME, 0, $values, $this->tokens[$this->position - 1], $this);
        }
    }

    /**
     * @template T of SqlEnum
     * @param class-string<T> $className
     * @return T
     */
    public function getNameEnum(string $className): ?SqlEnum
    {
        $value = $this->getName(EntityType::GENERAL);
        if ($value === null) {
            return null;
        }

        try {
            /** @var T $enum */
            $enum = new $className($value);

            return $enum;
        } catch (InvalidEnumValueException $e) {
            $this->position--;
            /** @var list<string> $values */
            $values = $className::getAllowedValues();

            throw InvalidTokenException::tokens(T::NAME, 0, $values, $this->tokens[$this->position - 1], $this);
        }
    }

    /**
     * @template T of SqlEnum
     * @param class-string<T> $className
     * @return T
     */
    public function expectNameOrStringEnum(string $className): SqlEnum
    {
        $value = $this->expectNonReservedNameOrString();

        try {
            /** @var T $enum */
            $enum = new $className($value);

            return $enum;
        } catch (InvalidEnumValueException $e) {
            $this->position--;
            /** @var list<string> $values */
            $values = $className::getAllowedValues();

            throw InvalidTokenException::tokens(T::NAME | T::STRING, 0, $values, $this->tokens[$this->position - 1], $this);
        }
    }

    /**
     * @template T of SqlEnum
     * @param class-string<T> $className
     * @return T
     */
    public function expectMultiNameEnum(string $className): SqlEnum
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        $start = $this->position;
        /** @var list<string> $values */
        $values = $className::getAllowedValues();
        foreach ($values as $value) {
            $this->position = $start;
            $keywords = explode(' ', $value);
            foreach ($keywords as $keyword) {
                if (!$this->hasName($keyword)) {
                    continue 2;
                }
            }

            /** @var T $enum */
            $enum = new $className($value);

            return $enum;
        }
        $this->position = $start;

        throw InvalidTokenException::tokens(T::NAME, 0, $values, $this->tokens[$this->position], $this);
    }

    /**
     * @template T of SqlEnum
     * @param class-string<T> $className
     * @return T
     */
    public function getMultiNameEnum(string $className): ?SqlEnum
    {
        $start = $this->position;
        /** @var array<string, string> $values */
        $values = $className::getAllowedValues();
        foreach ($values as $value) {
            $this->position = $start;
            $keywords = explode(' ', $value);
            foreach ($keywords as $keyword) {
                if (!$this->hasName($keyword)) {
                    continue 2;
                }
            }

            /** @var T $enum */
            $enum = new $className($value);

            return $enum;
        }

        return null;
    }

    // names ---------------------------------------------------------------------------------------------------------

    /**
     * @param EntityType::* $entity
     */
    public function expectNameOrString(string $entity): string
    {
        $token = $this->expect(T::NAME | T::STRING);
        $this->validateName($entity, $token->value);

        return $token->value;
    }

    /**
     * @param EntityType::* $entity
     */
    public function expectName(string $entity, int $mask = 0): string
    {
        $token = $this->expect(T::NAME, $mask);
        $this->validateName($entity, $token->value);

        return $token->value;
    }

    public function expectAnyName(string ...$names): string
    {
        $token = $this->expect(T::NAME);
        $upper = strtoupper($token->value);
        if (!in_array($upper, $names, true)) {
            $this->missingAnyKeyword(...$names);
        }

        return $token->value;
    }

    /**
     * @param EntityType::* $entity
     */
    public function getName(string $entity): ?string
    {
        $position = $this->position;
        $token = $this->get(T::NAME);
        if ($token !== null) {
            $this->validateName($entity, $token->value);

            return $token->value;
        }
        $this->position = $position;

        return null;
    }

    public function getAnyName(string ...$names): ?string
    {
        $position = $this->position;
        $token = $this->get(T::NAME);
        if ($token === null) {
            return null;
        }
        $upper = strtoupper($token->value);
        if (in_array($upper, $names, true)) {
            return $token->value;
        }
        $this->position = $position;

        return null;
    }

    /**
     * @phpstan-impure
     */
    public function hasName(string $name): bool
    {
        $position = $this->position;
        $token = $this->get(T::NAME, 0, $name);
        if ($token !== null) {
            return true;
        }
        $this->position = $position;

        return false;
    }

    /**
     * @param EntityType::* $entity
     */
    public function getNonKeywordNameOrString(string $entity): ?string
    {
        $token = $this->get(T::NAME | T::STRING, T::KEYWORD);
        if ($token === null) {
            return null;
        }
        $this->validateName($entity, $token->value);

        return $token->value;
    }

    /**
     * @param EntityType::* $entity
     */
    public function getNonKeywordName(string $entity): ?string
    {
        $token = $this->get(T::NAME, T::KEYWORD);
        if ($token === null) {
            return null;
        }
        $this->validateName($entity, $token->value);

        return $token->value;
    }

    /**
     * @param EntityType::* $entity
     */
    public function expectNonReservedName(string $entity, int $mask = 0): string
    {
        $token = $this->expect(T::NAME, T::RESERVED | $mask);
        $this->validateName($entity, $token->value);

        return $token->value;
    }

    /**
     * @param EntityType::* $entity
     */
    public function getNonReservedName(string $entity, int $mask = 0): ?string
    {
        $token = $this->get(T::NAME, T::RESERVED | $mask);
        if ($token === null) {
            return null;
        }
        $this->validateName($entity, $token->value);

        return $token->value;
    }

    /**
     * @param EntityType::* $entity
     */
    public function validateName(string $entity, string $name): void
    {
        // todo: move to platform
        static $trailingWhitespaceNotAllowed = [
            EntityType::SCHEMA, EntityType::TABLE, EntityType::COLUMN, EntityType::PARTITION, EntityType::USER_VARIABLE, EntityType::SRS, EntityType::CHANNEL,
        ];
        static $emptyAllowed = [EntityType::GENERAL, EntityType::TABLESPACE, EntityType::XA_TRANSACTION, EntityType::CHANNEL];

        if ($entity === EntityType::TABLE || $entity === EntityType::SCHEMA) {
            // might return an int collation id on unknown collations
            $collationName = $this->session->getSessionVariable(MysqlVariable::COLLATION_CONNECTION);
            $collation = Collation::tryCreate(is_scalar($collationName) ? $collationName : null);
            if ($collation !== null) {
                $charset = $collation->getCharsetName();
                if (Encoding::canCheck($charset) && !Encoding::check($name, $charset)) {
                    throw new ParserException("Invalid character in table name '$name' for charset '$charset'.", $this);
                }
            }
        }

        if ($name === '' && !in_array($entity, $emptyAllowed, true)) {
            throw new ParserException('Name must not be empty.', $this);
        }
        if ($entity === EntityType::SRS && ltrim($name, " \t\r\n") !== $name) {
            throw new ParserException(ucfirst($entity) . ' name must not contain left side white space.', $this);
        }
        if (in_array($entity, $trailingWhitespaceNotAllowed, true) && rtrim($name, " \t\r\n") !== $name) {
            throw new ParserException(ucfirst($entity) . ' name must not contain right side white space.', $this);
        }
        if (isset($this->maxLengths[$entity]) && Str::length($name) > $this->maxLengths[$entity]) { // todo: chars or bytes?
            throw new ParserException(ucfirst($entity) . " name must be at most {$this->maxLengths[$entity]} characters long.", $this);
        }
        if ($entity === EntityType::INDEX && strtoupper($name) === 'GEN_CLUST_INDEX') {
            throw new ParserException('GEN_CLUST_INDEX is a reserved name for primary index.', $this);
        }
    }

    // keywords --------------------------------------------------------------------------------------------------------

    /**
     * @return never
     * @throws InvalidTokenException
     */
    public function missingAnyKeyword(string ...$keywords): void
    {
        $token = $this->get(T::KEYWORD);

        throw InvalidTokenException::tokens(T::KEYWORD, 0, array_values($keywords), $token, $this);
    }

    public function expectKeyword(?string $keyword = null): string
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        $token = $this->tokens[$this->position] ?? null;
        if ($token === null || ($token->type & T::KEYWORD) === 0) {
            throw InvalidTokenException::tokens(T::KEYWORD, 0, $keyword, $token, $this);
        }
        $value = strtoupper($token->value);
        if ($keyword !== null && $value !== $keyword) {
            throw InvalidTokenException::tokens(T::KEYWORD, 0, $keyword, $token, $this);
        }
        $this->position++;

        return $value;
    }

    public function getKeyword(?string $keyword = null): ?string
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        $token = $this->tokens[$this->position] ?? null;
        if ($token === null || ($token->type & T::KEYWORD) === 0) {
            return null;
        }
        $value = strtoupper($token->value);
        if ($keyword !== null && $value !== $keyword) {
            return null;
        }
        $this->position++;

        return $value;
    }

    /**
     * @phpstan-impure
     */
    public function hasKeyword(string $keyword): bool
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        $token = $this->tokens[$this->position] ?? null;
        if ($token === null || ($token->type & T::KEYWORD) === 0) {
            return false;
        }
        $value = strtoupper($token->value);
        if ($value !== $keyword) {
            return false;
        }
        $this->position++;

        return true;
    }

    public function passKeyword(string $keyword): void
    {
        $this->getKeyword($keyword);
    }

    public function expectKeywords(string ...$keywords): string
    {
        foreach ($keywords as $keyword) {
            $this->expectKeyword($keyword);
        }

        return implode(' ', $keywords);
    }

    /**
     * @phpstan-impure
     */
    public function hasKeywords(string ...$keywords): bool
    {
        $position = $this->position;
        foreach ($keywords as $keyword) {
            if (!$this->hasKeyword($keyword)) {
                $this->position = $position;

                return false;
            }
        }

        return true;
    }

    public function expectAnyKeyword(string ...$keywords): string
    {
        $keyword = strtoupper($this->expect(T::KEYWORD)->value);
        if (!in_array($keyword, $keywords, true)) {
            $this->missingAnyKeyword(...$keywords);
        }

        return $keyword;
    }

    public function getAnyKeyword(string ...$keywords): ?string
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        $token = $this->tokens[$this->position] ?? null;
        if ($token === null || ($token->type & T::KEYWORD) === 0) {
            return null;
        }
        $value = strtoupper($token->value);
        if (!in_array($value, $keywords, true)) {
            return null;
        }
        $this->position++;

        return $value;
    }

    /**
     * @phpstan-impure
     */
    public function hasAnyKeyword(string ...$keywords): bool
    {
        $position = $this->position;
        foreach ($keywords as $keyword) {
            $token = $this->getKeyword($keyword);
            if ($token !== null) {
                return true;
            }
        }
        $this->position = $position;

        return false;
    }

    // keyword enums ---------------------------------------------------------------------------------------------------

    /**
     * @template T of SqlEnum
     * @param class-string<T> $className
     * @return T
     */
    public function expectKeywordEnum(string $className): SqlEnum
    {
        /** @var array<string, string> $values */
        $values = $className::getAllowedValues();

        /** @var T $enum */
        $enum = new $className($this->expectAnyKeyword(...array_values($values)));

        return $enum;
    }

    /**
     * @template T of SqlEnum
     * @param class-string<T> $className
     * @return T|null
     */
    public function getKeywordEnum(string $className): ?SqlEnum
    {
        /** @var array<string, string> $values */
        $values = $className::getAllowedValues();
        $token = $this->getAnyKeyword(...array_values($values));
        if ($token === null) {
            return null;
        }

        /** @var T $enum */
        $enum = new $className($token);

        return $enum;
    }

    /**
     * @template T of SqlEnum
     * @param class-string<T> $className
     * @return T
     */
    public function expectMultiKeywordsEnum(string $className): SqlEnum
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        $start = $this->position;
        /** @var list<string> $values */
        $values = $className::getAllowedValues();
        foreach ($values as $value) {
            $this->position = $start;
            $keywords = explode(' ', $value);
            foreach ($keywords as $keyword) {
                if (!$this->hasKeyword($keyword)) {
                    continue 2;
                }
            }

            /** @var T $enum */
            $enum = new $className($value);

            return $enum;
        }
        $this->position = $start;

        throw InvalidTokenException::tokens(T::KEYWORD, 0, $values, $this->tokens[$this->position], $this);
    }

    /**
     * @template T of SqlEnum
     * @param class-string<T> $className
     * @return T
     */
    public function getMultiKeywordsEnum(string $className): ?SqlEnum
    {
        $start = $this->position;
        /** @var array<string, string> $values */
        $values = $className::getAllowedValues();
        foreach ($values as $value) {
            $this->position = $start;
            $keywords = explode(' ', $value);
            foreach ($keywords as $keyword) {
                if (!$this->hasKeyword($keyword)) {
                    continue 2;
                }
            }

            /** @var T $enum */
            $enum = new $className($value);

            return $enum;
        }

        return null;
    }

    // special values --------------------------------------------------------------------------------------------------

    public function expectObjectIdentifier(): ObjectIdentifier
    {
        $first = $this->expectNonReservedName(EntityType::SCHEMA);
        if ($this->hasSymbol('.')) {
            if ($this->hasOperator(Operator::MULTIPLY)) {
                $second = Operator::MULTIPLY;
            } else {
                $second = $this->expectName(EntityType::TABLE);
            }

            return new QualifiedName($second, $first);
        }

        return new SimpleName($first);
    }

    public function getObjectIdentifier(): ?ObjectIdentifier
    {
        $position = $this->position;

        $first = $this->getNonReservedName(EntityType::SCHEMA);
        if ($first === null) {
            $this->position = $position;

            return null;
        }

        if ($this->hasSymbol('.')) {
            // a reserved keyword may follow after "." unescaped as we know it is a name context
            $secondToken = $this->get(T::KEYWORD);
            if ($secondToken !== null) {
                $second = $secondToken->value;
            } else {
                $second = $this->expectName(EntityType::TABLE);
            }

            return new QualifiedName($second, $first);
        }

        return new SimpleName($first);
    }

    public function expectUserName(bool $forRole = false): UserName
    {
        static $notAllowed = [
            Keyword::EVENT, Keyword::FILE, Keyword::NONE, Keyword::PROCESS, Keyword::PROXY,
            Keyword::RELOAD, Keyword::REPLICATION, Keyword::RESOURCE, Keyword::SUPER,
        ];

        $token = $this->expect(T::NAME | T::STRING, T::RESERVED | T::AT_VARIABLE);
        $name = $token->value;
        // characters, not bytes
        // todo: encoding
        if (mb_strlen($name) > $this->maxLengths[EntityType::USER]) {
            throw new ParserException('Too long user name.', $this);
        } elseif ($forRole && ($token->type & T::UNQUOTED_NAME) !== 0 && in_array(strtoupper($name), $notAllowed, true)) {
            throw new ParserException('User name not allowed.', $this);
        }
        $host = null;
        $token = $this->get(T::AT_VARIABLE);
        if ($token !== null) {
            $host = ltrim($token->value, '@');
            if (strlen($host) > $this->maxLengths[EntityType::HOST]) {
                throw new ParserException('Too long host name.', $this);
            }
        }

        return new UserName($name, $host);
    }

    public function expectCharsetName(): Charset
    {
        if ($this->hasKeyword(Keyword::BINARY)) {
            return new Charset(Charset::BINARY);
        } elseif ($this->hasKeyword(Keyword::ASCII)) {
            return new Charset(Charset::ASCII);
        } else {
            $charset = $this->getString();
            if ($charset === null) {
                $charset = $this->expectName(EntityType::CHARACTER_SET);
            }
            if (!Charset::isValidValue($charset)) {
                $values = array_values(Charset::getAllowedValues());

                throw InvalidTokenException::tokens(T::STRING | T::NAME, 0, $values, $this->tokens[$this->position - 1], $this);
            }

            return new Charset($charset);
        }
    }

    public function expectCollationName(): Collation
    {
        if ($this->hasKeyword(Keyword::BINARY)) {
            return new Collation(Collation::BINARY);
        } else {
            return $this->expectNameOrStringEnum(Collation::class);
        }
    }

    public function getCollationName(): ?Collation
    {
        if ($this->hasKeyword(Keyword::BINARY)) {
            return new Collation(Collation::BINARY);
        } else {
            $position = $this->position;
            $value = $this->getNonReservedNameOrString();
            if ($value === null) {
                return null;
            }

            if (!Collation::isValidValue($value)) {
                $this->position = $position;

                return null;
            }

            return new Collation($value);
        }
    }

    public function expectSqlState(): SqlState
    {
        $value = $this->expectString();
        if (preg_match('~^[\dA-Z]{5}$~', $value) === 0) {
            throw new ParserException("Invalid SQLSTATE value $value.", $this);
        }

        return new SqlState($value);
    }

    public function expectStorageEngineName(): StorageEngine
    {
        $value = $this->expectNonReservedNameOrString();

        try {
            return new StorageEngine($value);
        } catch (InvalidDefinitionException $e) {
            throw new ParserException($e->getMessage(), $this, $e);
        }
    }

    // end -------------------------------------------------------------------------------------------------------------

    public function expectEnd(): void
    {
        if ($this->autoSkip !== 0) {
            $this->doAutoSkip();
        }
        // pass trailing ; when delimiter is something else
        while ($this->hasSymbol(';')) {
            $this->trailingDelimiter .= ';';
            if ($this->autoSkip !== 0) {
                $this->doAutoSkip();
            }
        }

        if ($this->position < count($this->tokens)) {
            throw InvalidTokenException::tokens(T::END, 0, null, $this->tokens[$this->position], $this);
        }
    }

}
