<?php

namespace TransactionalTest;

use rex_sql;

class X {
    protected function doFoo():void {
        $sql = rex_sql::factory();

        $sql->beginTransaction();
        try {
            $sql->setQuery('SELECT 1');
            $sql->commit();
        } catch (\Exception $e) {
            $sql->rollBack();
        }
    }
}
