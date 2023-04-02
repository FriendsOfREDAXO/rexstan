<?php

declare(strict_types=1);

namespace rexstan;

use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use rex_sql;

final class RexSqlGenericType extends GenericObjectType
{
    /**
     * @var ?string
     */
    private $tableName;

    /**
     * @var ?string
     */
    private $selectExpression;

    public function __construct(Type $resultType)
    {
        parent::__construct(rex_sql::class, [$resultType]);
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
}
