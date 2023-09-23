<?php

namespace Bug588b;

use rex_sql;
use rex;

function doFoo(): void
{
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('article'));

    foreach ($sql->getFieldnames() as $key) {
        $sql->setValue($key, 'asd');
    }
}

function doFoo2(): void
{
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('article'));
    $sql->select();

    foreach ($sql->getFieldnames() as $key) {
        $sql->setValue($key, 'asd');
    }
}

function doFoo3(): void
{
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('article'));
    $sql->select('*');

    foreach ($sql->getFieldnames() as $key) {
        $sql->setValue($key, 'asdf');
    }
}
