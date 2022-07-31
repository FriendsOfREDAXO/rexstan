<?php

namespace RexSqlGetRow;

use PDO;
use rex;
use rex_sql;
use function PHPStan\Testing\assertType;

function getRow(int $id): void
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            WHERE   id = ?
            LIMIT   1
        ', [$id]);

    assertType('array{name: string}', $sql->getRow());
}

function getRowAssoc(int $id): void
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            WHERE   id = ?
            LIMIT   1
        ', [$id]);

    assertType('array{name: string}', $sql->getRow(PDO::FETCH_ASSOC));
}

function getRowNum(int $id): void
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            WHERE   id = ?
            LIMIT   1
        ', [$id]);

    assertType('array{0: string}', $sql->getRow(PDO::FETCH_NUM));
}
