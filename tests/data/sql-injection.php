<?php

namespace RexSqlInjection;

use rex;
use rex_i18n;
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
function safeScalars($mixed, string $s, $numericS, int $i, float $f, bool $b)
{
    $select = rex_sql::factory();
    $select->setTable('article');
    $select->setWhere('id = ' . $select->escape($s));
    $select->setWhere('id = ' . $select->escape($mixed));
    $select->setWhere($select->escapeIdentifier($s). ' = ' . $select->escape($mixed));
    $select->setWhere($select->escapeIdentifier($s). ' LIKE "' . $select->escapeLikeWildcards($mixed) . '"');
    $select->setWhere('id = ' . $numericS);
    $select->setWhere('id = ' . $i);
    $select->setWhere('id = ' . $f);
    $select->setWhere('id = ' . $b);
    $select->setQuery('SELECT * FROM rex_article WHERE id = ' . $i);
    $select->setQuery('INSERT INTO '.rex::getTablePrefix() . 'media_manager_type (status, name, description) SELECT 0, CONCAT(name, \' '.rex_i18n::msg('media_manager_type_name_copy').'\'), description FROM '.rex::getTablePrefix() . 'media_manager_type WHERE id = ?', [$i]);
}

function injection($_id, string $langID): void
{
    $select = rex_sql::factory();
    $select->setTable('article');
    $select->setWhere('id = ' . $_id);
    $select->setWhere('id = ' . $langID);
    $select->setQuery('SELECT * FROM rex_article WHERE id = ' . $_id);
}
