<?php

namespace Bug588;

use rex_sql;
use rex;

function setValue(int $id): void
{
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('article'));

    $data = [];
    foreach ($sql->getFieldnames() as $key) {
        $data[$key] = $sql->getValue($key);
    }
}

function setValue2(int $id): void
{
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('article'));
    $sql->select();

    $data = [];
    foreach ($sql->getFieldnames() as $key) {
        $data[$key] = $sql->getValue($key);
    }
}
