<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable SlevomatCodingStandard.TypeHints.NullTypeHintOnLastPosition.NullTypeHintNotOnLastPosition

namespace SqlFtw\Resolver\Functions;

use SqlFtw\Resolver\UnresolvableException;
use SqlFtw\Sql\Expression\BoolLiteral;
use SqlFtw\Sql\Expression\NullLiteral;
use SqlFtw\Sql\Expression\Value;
use function assert;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function str_replace;
use function strcmp;

trait FunctionsComparison
{

    /**
     * > - Greater than operator
     *
     * @param scalar|Value|null|list<scalar|Value|null> $left
     * @param scalar|Value|null|list<scalar|Value|null> $right
     */
    public function _greater($left, $right): ?bool
    {
        ///
        throw new UnresolvableException('_greater() is not implemented yet.');
    }

    /**
     * >= - Greater than or equal operator
     *
     * @param scalar|Value|null|list<scalar|Value|null> $left
     * @param scalar|Value|null|list<scalar|Value|null> $right
     */
    public function _greater_or_equal($left, $right): ?bool
    {
        ///
        throw new UnresolvableException('_greater_or_equal() is not implemented yet.');
    }

    /**
     * < - Less than operator
     *
     * @param scalar|Value|null|list<scalar|Value|null> $left
     * @param scalar|Value|null|list<scalar|Value|null> $right
     */
    public function _less($left, $right): ?bool
    {
        ///
        throw new UnresolvableException('_less() is not implemented yet.');
    }

    /**
     * <= - Less than or equal operator
     *
     * @param scalar|Value|null|list<scalar|Value|null> $left
     * @param scalar|Value|null|list<scalar|Value|null> $right
     */
    public function _less_or_equal($left, $right): ?bool
    {
        ///
        throw new UnresolvableException('_less_or_equal() is not implemented yet.');
    }

    /**
     * = - Equal operator
     *
     * @param scalar|Value|null|list<scalar|Value|null> $left
     * @param scalar|Value|null|list<scalar|Value|null> $right
     */
    public function _equal($left, $right): ?bool
    {
        ///
        throw new UnresolvableException('_equal() is not implemented yet.');
    }

    /**
     * <>, != - Not equal operator
     *
     * @param scalar|Value|null|list<scalar|Value|null> $left
     * @param scalar|Value|null|list<scalar|Value|null> $right
     */
    public function _not_equal($left, $right): ?bool
    {
        ///
        throw new UnresolvableException('_not_equal() is not implemented yet.');
    }

    /**
     * <=> - NULL-safe equal to operator
     *
     * @param scalar|Value|null|list<scalar|Value|null> $left
     * @param scalar|Value|null|list<scalar|Value|null> $right
     */
    public function _safe_equal($left, $right): ?bool
    {
        ///
        throw new UnresolvableException('_safe_equal() is not implemented yet.');
    }

    /**
     * BETWEEN ... AND ... - Whether a value is within a range of values
     *
     * @param scalar|Value|null $value
     * @param scalar|Value|null $min
     * @param scalar|Value|null $max
     */
    public function _between($value, $min, $max): ?bool
    {
        $value = $this->cast->toString($value);
        $min = $this->cast->toString($min);
        $max = $this->cast->toString($max);

        ///
        throw new UnresolvableException('_between() is not implemented yet.');
    }

    /**
     * NOT BETWEEN ... AND ... - Whether a value is not within a range of values
     *
     * @param scalar|Value|null $value
     * @param scalar|Value|null $min
     * @param scalar|Value|null $max
     */
    public function _not_between($value, $min, $max): ?bool
    {
        return $this->_not($this->_between($value, $min, $max));
    }

    /**
     * IS - Test a value against a boolean
     * IS NULL - NULL value test
     *
     * @param scalar|Value|null $left
     */
    public function _is($left, BoolLiteral $right): bool
    {
        $left = $this->cast->toBool($left);

        return $left === $right->asBool();
    }

    /**
     * IS NOT - Test a value against a boolean
     * IS NOT NULL - NOT NULL value test
     *
     * @param scalar|Value|null $left
     */
    public function _is_not($left, BoolLiteral $right): bool
    {
        $left = $this->cast->toBool($left);

        return $left !== $right->asBool();
    }

    /**
     * IN () - Whether a value is within a set of values
     *
     * @param scalar|Value|null|list<scalar|Value|null> $left
     * @param list<scalar|Value|null|list<scalar|Value|null>> $right
     */
    public function _in($left, array $right): bool
    {
        ///
        throw new UnresolvableException('_in() is not implemented yet.');
    }

    /**
     * NOT IN () - Whether a value is not within a set of values
     *
     * @param scalar|Value|null $left
     * @param list<scalar|Value|null|list<scalar|Value|null>> $right
     */
    public function _not_in($left, array $right): bool
    {
        return !$this->_in($left, $right);
    }

    /**
     * LIKE - Simple pattern matching
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $pattern
     * @param scalar|Value|null $escape
     */
    public function _like($string, $pattern, $escape = '\\'): ?bool
    {
        $string = $this->cast->toString($string);
        $pattern = $this->cast->toString($pattern);
        $escape = $this->cast->toString($escape);

        if ($string === null || $pattern === null || $escape === null) {
            return null;
        }

        $escape = preg_quote($escape, '~');
        $pattern = preg_quote($pattern, '~');
        // todo: quoted delimiters before wild chars
        $pattern = preg_replace("~(?!{$escape})_~", ".", $pattern);
        assert($pattern !== null);
        $pattern = preg_replace("~(?!{$escape})%~", ".*?", $pattern);
        assert($pattern !== null);
        $pattern = str_replace($escape . $escape, $escape, $pattern);
        $pattern = "~^{$pattern}$~iu";

        return preg_match($pattern, $string) !== 0;
    }

    /**
     * NOT LIKE - Negation of simple pattern matching
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $pattern
     * @param scalar|Value|null $escape
     */
    public function _not_like($string, $pattern, $escape = '\\'): ?bool
    {
        return $this->_not($this->_like($string, $pattern, $escape));
    }

    /**
     * COALESCE() - Return the first non-NULL argument
     *
     * @param scalar|Value|null $args
     * @return scalar|Value|null
     */
    public function coalesce(...$args)
    {
        foreach ($args as $arg) {
            if ($arg !== null && !$arg instanceof NullLiteral) {
                return $arg;
            }
        }

        return null;
    }

    /// GREATEST() - Return the largest argument
    /// INTERVAL() - Return the index of the argument that is less than the first argument

    /**
     * ISNULL() - Test whether the argument is NULL
     *
     * @param scalar|Value|null $value
     */
    public function isnull($value): bool
    {
        return $value === null || $value instanceof NullLiteral;
    }

    /// LEAST() - Return the smallest argument

    /**
     * STRCMP() - Compare two strings
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     */
    public function strcmp($left, $right): ?int
    {
        $left = $this->cast->toString($left);
        $right = $this->cast->toString($right);

        if ($left === null || $right === null) {
            return null;
        } else {
            // todo: utf8
            return strcmp($left, $right);
        }
    }

    /// WEIGHT_STRING() - Return the weight string for a string

}
