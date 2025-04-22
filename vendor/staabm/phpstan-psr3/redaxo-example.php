<?php

namespace RedaxoExample;

function doFoo(\rex_logger $logger, string $s) {
    \rex_logger::logError(11, 'error '. $s);

    $logger->error('error '. $s);
}
