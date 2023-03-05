<?php

namespace RexSqlSetValue;

use rex;
use rex_sql;
use function PHPStan\Testing\assertType;

function setValue(int $id): void
{
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('article'));
    $sql->setValue('name', 1);
    $sql->setValue('doesNotExist', 1);
}

function setArrayValue(): void
{
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('article'));
    $sql->setArrayValue('name', [1, 2, 3]);
    $sql->setArrayValue('doesNotExist', [1, 2, 3]);

    $sql->setArrayValue('id', [1, 2, 3]);
}

function unionTableName(): void
{
    if (rand(0,1) ===1) {
        $table = rex::getTable('article');
    } else {
        $table = rex::getTable('template');
    }

    $sql = rex_sql::factory();
    $sql->setTable($table);
    $sql->setArrayValue('name', [1, 2, 3]);
    $sql->setArrayValue('doesNotExist', [1, 2, 3]);
}

function chainedSet(): void
{
    $sql = rex_sql::factory();
    $sqlChained = $sql->setTable(rex::getTable('article'));
    $sqlChained->setValue('name', 1);
    $sqlChained->setArrayValue('name', [1, 2, 3]);

    $sqlChained->setValue('doesNotExist', 1);
    $sqlChained->setArrayValue('doesNotExist', [1, 2, 3]);
}
