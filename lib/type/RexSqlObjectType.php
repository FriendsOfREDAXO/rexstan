<?php

declare(strict_types=1);

namespace rexstan;

use PHPStan\TrinaryLogic;
use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use rex_sql;

final class RexSqlObjectType extends ObjectType
{
    /**
     * @var ?string
     */
    private $tableName;

    /**
     * @var ?string
     */
    private $selectExpression;

    public function __construct()
    {
        parent::__construct(rex_sql::class);
    }

    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    public function setSelectExpression(string $selectExpression): void
    {
        $this->selectExpression = $selectExpression;
    }

    public function getSelectExpression(): ?string
    {
        return $this->selectExpression;
    }

    public function equals(Type $type): bool
    {
        if ($type instanceof self) {
            if ($this->getSelectExpression() !== $type->getSelectExpression()) {
                return false;
            }
            return $this->getTableName() === $type->getTableName();
        }

        return parent::equals($type);
    }

    public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
    {
        if ($type instanceof self) {
            return IsSuperTypeOfResult::createFromBoolean(
                $this->getSelectExpression() === $type->getSelectExpression()
                && $this->getTableName() === $type->getTableName()
            );
        }

        return parent::isSuperTypeOf($type);
    }
}
