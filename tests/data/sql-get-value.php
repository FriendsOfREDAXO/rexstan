<?php

namespace RexSqlGetValue;

use rex;
use rex_sql;
use function PHPStan\Testing\assertType;

function preparedQuery(int $id): void
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            WHERE   id = ?
            LIMIT   1
        ', [$id]);

    assertType('string', $sql->getValue('name'));
}

function regularQuery(): void
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            WHERE   id = 1
            LIMIT   1
        ');

    assertType('string', $sql->getValue('name'));
}

function unknownValue(): void
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            WHERE   id = 1
            LIMIT   1
        ');

    assertType('bool|float|int|string|null', $sql->getValue('doesNotExist'));
    $sql->getDateTimeValue('doesNotExist');
    $sql->getArrayValue('doesNotExist');
}

function tableNamePrefixedString(): void
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            WHERE   id = 1
            LIMIT   1
        ');

    assertType('string', $sql->getValue('rex_article.name'));
}

function tableNamePrefix(): void
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTablePrefix() . 'article
            WHERE   id = 1
            LIMIT   1
        ');
    assertType('string', $sql->getValue('rex_article.name'));
}

function escaped(string $s): void
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTablePrefix() . 'article
            WHERE   id = "'. $sql->escape($s) .'"
            LIMIT   1
        ');
    assertType('string', $sql->getValue('rex_article.name'));
}

function escapedWildcard(string $s): void
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTablePrefix() . 'article
            WHERE   id LIKE "'. $sql->escape($sql->escapeLikeWildcards($s)) .'"
            LIMIT   1
        ');
    assertType('string', $sql->getValue('rex_article.name'));
}

// see https://github.com/FriendsOfREDAXO/rexstan/issues/323
function chainedQuery(int $id): void
{
    $sql = rex_sql::factory()->setQuery('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            WHERE   id = ?
            LIMIT   1
        ', [$id]);

    assertType('string', $sql->getValue('name'));
}

function stringUnion(int $id): void
{
    $sql = rex_sql::factory()->setQuery('
            SELECT  name, id
            FROM    ' . rex::getTable('article') . '
            WHERE   id = ?
            LIMIT   1
        ', [$id]);

    if ($id > 0) {
        $field = 'name';
        $tableField = rex::getTable('article') .'.name';
    } else {
        $field = 'id';
        $tableField = rex::getTable('article') .'.id';
    }

    assertType('int<0, 4294967295>|string', $sql->getValue($field));
    assertType('int<0, 4294967295>|string', $sql->getValue($tableField));
}
