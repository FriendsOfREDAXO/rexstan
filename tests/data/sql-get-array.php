<?php

namespace RexSqlGetArray;

use PDO;
use rex;
use rex_sql;
use function PHPStan\Testing\assertType;

function getArray(): void
{
    $sql = rex_sql::factory();
    $array = $sql->getArray('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            LIMIT   1
        ');

    assertType('array<int, array{name: string}>', $array);
}

function getArray0(int $id): void
{
    $sql = rex_sql::factory();
    $array = $sql->getArray('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            WHERE   id = ?
            LIMIT   1
        ', [$id]);

    assertType('array<int, array{name: string}>', $array);
}

function getArray1(int $id): void
{
    $sql = rex_sql::factory();
    $array = $sql->getArray('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            WHERE   id = ?
            LIMIT   1
        ', [$id], PDO::FETCH_ASSOC);

    assertType('array<int, array{name: string}>', $array);
}

function getArray2(int $id): void
{
    $sql = rex_sql::factory();
    $array = $sql->getArray('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            WHERE   id = ?
            LIMIT   1
        ', [$id], PDO::FETCH_NUM);

    assertType('array<int, array{string}>', $array);
}
