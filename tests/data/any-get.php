<?php

namespace RexGetArray;

use rex_article;
use rex_category;
use rex_media;
use rex_user;

function doFoo()
{
    $u = rex_user::get(9999999);
    $m = rex_media::get('does-not-exist.jpg');
    $m = rex_article::get(9999999);
    $m = rex_category::get(9999999);
}
