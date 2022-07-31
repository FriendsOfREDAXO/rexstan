<?php

namespace RexSqlGetArray;

use rex;
use rex_sql;
use function PHPStan\Testing\assertType;

function getArray(int $id): void
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
