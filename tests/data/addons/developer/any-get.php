<?php

namespace RexGetArrayVars;

use rex_article;
use rex_category;
use rex_media;
use rex_user;

function rexVarsShoudNotError()
{
    rex_media::get('REX_MEDIA[1]');

    rex_media::get('REX_VALUE[1]');
}
