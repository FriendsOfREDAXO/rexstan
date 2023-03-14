<?php

namespace Bug301;

use rex_clang;
use function PHPStan\Testing\assertType;

function doFoo(mixed $x):void {
    assertType('rex_clang', rex_clang::get(rex_clang::getStartId()));
    assertType('rex_clang|null', rex_clang::get($x));
}
