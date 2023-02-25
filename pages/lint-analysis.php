<?php

/** @var rex_addon $this */

use rexstan\RexLint;
use rexstan\RexResultsRenderer;

$errors = RexLint::runFromWeb();
if (count($errors) > 0) {
    foreach ($errors as $file => $errors) {
        echo RexResultsRenderer::renderFileBlock($file, $errors);
    }

} else {
    echo rex_view::success('Gratulation, es wurden keine Syntax Fehler gefunden.');
}

$cliVersion = \rexstan\RexCmd::getFormattedCliPhpVersion();
echo rex_view::warning('Verwendete CLI PHP Version: '. $cliVersion);
