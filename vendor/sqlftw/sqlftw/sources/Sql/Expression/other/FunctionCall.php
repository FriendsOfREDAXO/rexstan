<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dml\Query\WindowSpecification;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\Keyword;
use function is_int;

/**
 * e.g. AVG([DISTINCT] x) OVER ...
 */
class FunctionCall implements RootNode
{

    private FunctionIdentifier $function;

    /** @var array<int|string, ArgumentNode> */
    private array $arguments;

    /** @var WindowSpecification|string|null */
    private $over;

    private ?bool $respectNulls;

    private ?bool $fromFirst;

    /**
     * @param array<int|string, ArgumentNode> $arguments
     * @param WindowSpecification|string $over
     */
    public function __construct(
        FunctionIdentifier $function,
        array $arguments = [],
        $over = null,
        ?bool $respectNulls = null,
        ?bool $fromFirst = null
    )
    {
        if ($over !== null && (!$function instanceof BuiltInFunction || !$function->isWindow())) {
            throw new InvalidDefinitionException('OVER clause is supported only on window functions.');
        }
        if ($respectNulls !== null && (!$function instanceof BuiltInFunction || !$function->hasNullTreatment())) {
            throw new InvalidDefinitionException('RESPECT NULLS clause is not supported by this function.');
        }
        if ($fromFirst !== null && (!$function instanceof BuiltInFunction || !$function->hasFromFirstLast())) {
            throw new InvalidDefinitionException('FROM FIRST/LAST clause is not supported by this function.');
        }

        $this->function = $function;
        $this->arguments = $arguments;
        $this->over = $over;
        $this->respectNulls = $respectNulls;
        $this->fromFirst = $fromFirst;
    }

    public function getFunction(): FunctionIdentifier
    {
        return $this->function;
    }

    /**
     * @return array<int|string, ArgumentNode>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return WindowSpecification|string|null
     */
    public function getOver()
    {
        return $this->over;
    }

    public function respectNulls(): ?bool
    {
        return $this->respectNulls;
    }

    public function fromFirst(): ?bool
    {
        return $this->fromFirst;
    }

    public function serialize(Formatter $formatter): string
    {
        $arguments = '';
        if ($this->function instanceof BuiltInFunction && $this->function->hasNamedParams()) {
            $first = true;
            foreach ($this->arguments as $name => $argument) {
                if (is_int($name)) {
                    // value, value...
                    $arguments .= ($first ? '' : ', ') . $argument->serialize($formatter);
                } elseif ($this->function->equalsValue(BuiltInFunction::TRIM)) {
                    // TRIM([{BOTH | LEADING | TRAILING} [remstr] FROM] str), TRIM([remstr FROM] str)
                    if ($name === Keyword::BOTH || $name === Keyword::LEADING || $name === Keyword::TRAILING) {
                        if ($argument instanceof NoValue) {
                            $arguments .= $name . ' ';
                        } else {
                            $arguments .= $name . ' ' . $argument->serialize($formatter) . ' ';
                        }
                    } else {
                        $arguments .= ' FROM ' . $argument->serialize($formatter);
                    }
                } elseif ($this->function->equalsValue(BuiltInFunction::JSON_VALUE)) {
                    // JSON_VALUE(json_doc, path [RETURNING type] [on_empty] [on_error])
                    static $onEmpty = Keyword::ON . ' ' . Keyword::EMPTY;
                    static $onError = Keyword::ON . ' ' . Keyword::ERROR;
                    if ($name === $onEmpty || $name === $onError) {
                        $arguments .= ' ' . $argument->serialize($formatter) . ' ' . $name;
                    } elseif ($name === Keyword::RETURNING) {
                        $arguments .= ' RETURNING ' . $argument->serialize($formatter);
                    } else {
                        $arguments .= $arguments === '' ? $argument->serialize($formatter) : ', ' . $argument->serialize($formatter);
                    }
                } else {
                    // KEYWORD value KEYWORD value...
                    $arguments .= ($first ? '' : ' ') . $name . ' ' . $argument->serialize($formatter);
                }
                $first = false;
            }
        } elseif ($this->arguments !== []) {
            /** @var non-empty-list<ArgumentNode> $values */
            $values = $this->arguments;
            $arguments = $formatter->formatSerializablesList($values);
        }

        $result = $this->function->serialize($formatter) . '(' . $arguments . ')';

        if ($this->respectNulls === true) {
            $result .= ' ' . Keyword::RESPECT . ' ' . Keyword::NULLS;
        } elseif ($this->respectNulls === false) {
            $result .= ' ' . Keyword::IGNORE . ' ' . Keyword::NULLS;
        }
        if ($this->fromFirst === true) {
            $result .= ' ' . Keyword::FROM . ' ' . Keyword::FIRST;
        } elseif ($this->fromFirst === false) {
            $result .= ' ' . Keyword::FROM . ' ' . Keyword::LAST;
        }

        if ($this->over !== null) {
            if ($this->over instanceof WindowSpecification) {
                $result .= ' OVER ' . $this->over->serialize($formatter);
            } else {
                $result .= ' OVER ' . $formatter->formatName($this->over);
            }
        }

        return $result;
    }

}
