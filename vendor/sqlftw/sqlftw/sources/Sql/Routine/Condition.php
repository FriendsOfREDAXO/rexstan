<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Routine;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dml\Error\SqlState;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Util\TypeChecker;
use function is_int;
use function is_string;

class Condition implements SqlSerializable
{

    private ConditionType $type;

    /** @var int|string|SqlState|null */
    private $value;

    /**
     * @param int|string|SqlState|null $value
     */
    public function __construct(ConditionType $type, $value = null)
    {
        if ($type->equalsValue(ConditionType::ERROR)) {
            TypeChecker::check($value, BaseType::UNSIGNED, $type->getValue());
        } elseif ($type->equalsValue(ConditionType::CONDITION)) {
            TypeChecker::check($value, BaseType::CHAR, $type->getValue());
        } elseif ($type->equalsValue(ConditionType::SQL_STATE)) {
            TypeChecker::check($value, SqlState::class, $type->getValue());
        } elseif ($value !== null) {
            throw new InvalidDefinitionException("No value allowed for condition of type {$type->getValue()}.");
        }

        $this->type = $type;
        $this->value = $value;
    }

    public function getType(): ConditionType
    {
        return $this->type;
    }

    /**
     * @return int|string|SqlState|null
     */
    public function getValue()
    {
        return $this->value;
    }

    public function serialize(Formatter $formatter): string
    {
        if (is_int($this->value)) {
            return (string) $this->value;
        } elseif (is_string($this->value)) {
            return $formatter->formatName($this->value);
        } elseif ($this->value instanceof SqlState) {
            return 'SQLSTATE ' . $this->value->serialize($formatter);
        } else {
            return $this->type->serialize($formatter);
        }
    }

}
