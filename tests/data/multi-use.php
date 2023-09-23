<?php

namespace MultiUse;

use rex_sql;
use rex;


function doFoo(int $id) {
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('article'));
    $sql->select('id');

    if (0 !== ($sql->getRows() ?? 0) && $sql->getValue('id') === $id) {
        $sql->setTable(rex::getTable('article'));
        $sql->setValue('updatedate', date(rex_sql::FORMAT_DATETIME));
        $sql->setWhere('id = :id', ['id' => $sql->getValue('id')]);
        $sql->update();
    }
}
