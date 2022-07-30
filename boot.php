<?php

$addon = rex_addon::get('rexstan');

if (rex::isBackend() && is_object(rex::getUser()) && 'rexstan' === rex_be_controller::getCurrentPagePart(1)) {
    rex_view::addCssFile($addon->getAssetsUrl('rexstan.css'));
    rex_view::addJsFile($addon->getAssetsUrl('confetti.min.js'));
}

rex_extension::register('PACKAGE_CACHE_DELETED', static function (rex_extension_point $ep) {
    RexStan::clearResultCache();
});

$sql = rex_sql::factory();
$sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTable('article') . '
            LIMIT   1
        ');
$sql->getValue('abc');
exit();

function getName(int $id): int
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTable('cronjob') . '
            WHERE   id = ?
            LIMIT   1
        ', [$id]);

    return $sql->getValue('name'); // ERROR: Function getName() should return int but returns string|null.
}

function getName1(int $id): int
{
    $sql = rex_sql::factory();
    $sql->setQuery('
            SELECT  name
            FROM    ' . rex::getTable('cronjob') . '
            WHERE   id = 2
            LIMIT   1
        ');

    return $sql->getValue('name'); // ERROR: Function getName() should return int but returns string|null.
}
