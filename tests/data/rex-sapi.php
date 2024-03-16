<?php

namespace RexSapi;

use rex_request;
use function PHPStan\Testing\assertType;

function doFoo()
{
    assertType('string', rex_request::get('HTTP_REFERER', 'string', ''));
    assertType('string', rex_request::post('HTTP_REFERER', 'string', ''));
    assertType('string', rex_request::env('HTTP_REFERER', 'string', ''));
    assertType('string', rex_request::request('HTTP_REFERER', 'string', ''));
    assertType('string', rex_request::server('HTTP_REFERER', 'string', ''));
    assertType('string', rex_request::session('HTTP_REFERER', 'string', ''));
    assertType('string', rex_request::files('HTTP_REFERER', 'string', ''));
}

function doBar()
{
    assertType('string', rex_get('HTTP_REFERER', 'string', ''));
    assertType('string', rex_post('HTTP_REFERER', 'string', ''));
    assertType('string', rex_env('HTTP_REFERER', 'string', ''));
    assertType('string', rex_request('HTTP_REFERER', 'string', ''));
    assertType('string', rex_server('HTTP_REFERER', 'string', ''));
    assertType('string', rex_session('HTTP_REFERER', 'string', ''));
    assertType('string', rex_files('HTTP_REFERER', 'string', ''));
}

function doFooBar()
{
    assertType('string', rex_get('HTTP_REFERER', 'string', ''));
    assertType('int', rex_get('HTTP_REFERER', 'int', ''));
    assertType('int', rex_get('HTTP_REFERER', 'integer', ''));
    assertType('float', rex_get('HTTP_REFERER', 'float', ''));
    assertType('float', rex_get('HTTP_REFERER', 'double', ''));
    assertType('float', rex_get('HTTP_REFERER', 'real', ''));
    assertType('bool', rex_get('HTTP_REFERER', 'bool', ''));
    assertType('bool', rex_get('HTTP_REFERER', 'boolean', ''));
}

function doQux()
{
    assertType('int', rex_get('HTTP_REFERER', 'int', 1));
    assertType('int|null', rex_get('HTTP_REFERER', 'int', null));
    assertType("'foo'|int", rex_get('HTTP_REFERER', 'int', 'foo'));
    assertType("'foo'|int", rex_get('HTTP_REFERER', 'int', rand(0,1) ? 'foo' : ''));
}
