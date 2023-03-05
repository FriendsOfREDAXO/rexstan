<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Query;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ArgumentNode;
use SqlFtw\Sql\Expression\Asterisk;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\SqlSerializable;
use function is_string;

class SelectExpression implements SqlSerializable
{

    /** @var RootNode|Asterisk */
    private ArgumentNode $expression;

    private ?string $alias;

    /** @var WindowSpecification|string|null */
    private $window;

    /**
     * @param RootNode|Asterisk $expression
     * @param WindowSpecification|string|null $window
     */
    public function __construct(
        ArgumentNode $expression,
        ?string $alias = null,
        $window = null
    ) {
        $this->expression = $expression;
        $this->alias = $alias;
        $this->window = $window;
    }

    /**
     * @return RootNode|Asterisk
     */
    public function getExpression(): ArgumentNode
    {
        return $this->expression;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @return WindowSpecification|string|null
     */
    public function getWindow()
    {
        return $this->window;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->expression->serialize($formatter);

        if (is_string($this->window)) {
            $result .= ' OVER ' . $formatter->formatName($this->window);
        } elseif ($this->window !== null) {
            $result .= ' OVER ' . $this->window->serialize($formatter);
        }
        if ($this->alias !== null) {
            $result .= ' AS ' . $formatter->formatName($this->alias);
        }

        return $result;
    }

}
