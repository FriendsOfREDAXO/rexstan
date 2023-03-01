<?php

namespace RexSqlSetValue;

use rex;
use rex_sql;
use function PHPStan\Testing\assertType;

function preparedQuery(int $id): void
{
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('article'));
    $sql->setValue('name', 1);
    $sql->setValue('doesNotExist', 1);
}

function regularQuery(): void
{
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('article'));
    $sql->setValue('name', 1);
    $sql->setValue('doesNotExist', 1);
    $sql->setArrayValue('name', [1, 2, 3]);
    $sql->setArrayValue('doesNotExist', [1, 2, 3]);
}
