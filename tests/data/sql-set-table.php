<?php

namespace RexSqlSetTable;

use rex_sql;

function foo() {
    $sql = rex_sql::factory();
    $sql->setTable('rex_article');
}

function bar() {
    $sql = rex_sql::factory();
    $sql->setTable('does_not_exist');
}
