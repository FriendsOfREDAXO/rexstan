<?php

namespace Bug372;

use rex_clang;
use rex_sql;
use function PHPStan\Testing\assertType;

function foo(): void {
    $sql = rex_sql::factory();
    $sql->setTable('rex_article');
    $sql->setWhere('id > 10 AND clang_id = '.rex_clang::getCurrentId());
    $sql = $sql->select('count(*) as count');
    assertType('int<0, max>', $sql->getValue('count'));
}

function foo2(): void {
    $sql = rex_sql::factory();
    $sql->setTable('rex_article');
    $sql->setWhere('id > 10 AND clang_id = '.rex_clang::getCurrentId());
    $sql->select('count(*) as count');
    assertType('int<0, max>', $sql->getValue('count'));
}

function fooReordered(): void {
    $sql = rex_sql::factory();
    $sql->select('count(*) as count');
    $sql->setWhere('id > 10 AND clang_id = '.rex_clang::getCurrentId());
    $sql->setTable('rex_article');
    assertType('int<0, max>', $sql->getValue('count'));
}
