<?php

namespace matchRexVar;

function doFoo():int {
    $x = 0;
    match ("REX_VALUE[5]") {
        'one-pic' => $x = 1,
        'two-pics' => $x = 2,
    };
    return $x;
}
