<?php

namespace RexObjectOrientedFramework;

use rex_user;
use rex_media;
use rex_article;
use rex_category;
use rex_article_slice;
use rex_sql;
use function PHPStan\Testing\assertType;

function doFoo() {
    $m = rex_user::get(1);
    assertType('int<0, 4294967295>', $m->getValue('id'));
    assertType('string', $m->getValue('unknownColumn'));

    $m = rex_media::get('markus.jpg');
    assertType('int<0, 4294967295>', $m->getValue('id'));
    assertType('int|string|null', $m->getValue('unknownColumn'));

    $m = rex_article::get(1);
    assertType('int<0, 4294967295>', $m->getValue('id'));
    assertType('int|string|null', $m->getValue('unknownColumn'));

    $m = rex_category::get(1);
    assertType('int<0, 4294967295>', $m->getValue('id'));
    assertType('int|string|null', $m->getValue('unknownColumn'));

    $m = rex_article_slice::fromSql(rex_sql::factory(1));
    assertType('int<0, 4294967295>', $m->getValue('id'));
    assertType('int|string|null', $m->getValue('unknownColumn'));
}


