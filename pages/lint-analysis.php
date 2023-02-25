<?php

/** @var rex_addon $this */

use rexstan\RexLint;
use rexstan\RexStan;
use rexstan\RexStanTip;
use rexstan\RexStanUserConfig;

$errors = RexLint::runFromWeb();
if (count($errors) > 0) {
    echo rex_view::warning('PHP linting errors');

    foreach ($errors as $file => $errors) {
        echo rexstan_renderFileBlock($file, $errors);
    }

    return;
}

echo rex_view::success('Gratulation, es wurden keine Lint Fehler gefunden.');
