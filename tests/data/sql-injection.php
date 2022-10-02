<?php

namespace RexSqlInjection;

use rex_sql;

/**
 * @return void
 */
function safeArray($_id, string $langID)
{
    $select = rex_sql::factory();
    $select->setTable('article');
    $select->setWhere(['id' => $_id, 'clang_id' => $langID]);
}

/**
 * @param numeric-string  $numericS
 * @return void
 */
function safeScalars($numericS, int $i, float $f, bool $b)
{
    $select = rex_sql::factory();
    $select->setTable('article');
    $select->setWhere('id = ' . $numericS);
    $select->setWhere('id = ' . $i);
    $select->setWhere('id = ' . $f);
    $select->setWhere('id = ' . $b);
    $select->setQuery('SELECT * FROM rex_article WHERE id = ' . $i);
}

function injection($_id, string $langID): void
{
    $select = rex_sql::factory();
    $select->setTable('article');
    $select->setWhere('id = ' . $_id);
    $select->setWhere('id = ' . $langID);
    $select->setQuery('SELECT * FROM rex_article WHERE id = ' . $_id);
}
