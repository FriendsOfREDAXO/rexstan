<?php

namespace switchRexVar;

function doFoo():int {
    switch ("REX_VALUE[5]") {
        case 'two-pics': $x = 1;
        case 'three-pics': $x = 2;
        default: $x = 3;
    }
    return $x;
}
