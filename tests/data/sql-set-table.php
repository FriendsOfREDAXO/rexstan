<?php

namespace RexSqlSetTable;

use rex;
use rex_sql;

function foo() {
    $sql = rex_sql::factory();
    $sql->setTable('rex_article');
}

function bar() {
    $sql = rex_sql::factory();
    $sql->setTable('does_not_exist');
}

function foo2() {
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('article'));

    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('does_not_exist'));
}

