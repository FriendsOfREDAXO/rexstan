<?php

/** @var rex_addon $this */

use rexstan\RexCmd;
use rexstan\RexLint;
use rexstan\RexResultsRenderer;
use rexstan\RexStanSettings;

$errors = RexLint::runFromWeb();
if (count($errors) > 0) {
    foreach ($errors as $file => $perFileErrors) {
        echo RexResultsRenderer::renderFileBlock($file, $perFileErrors);
    }
} else {
    echo rex_view::success('Gratulation, es wurden keine Linting Fehler gefunden.');
}

$cliVersion = RexCmd::getFormattedCliPhpVersion();
echo rex_view::warning('Verwendete CLI PHP Version: '. $cliVersion);

echo RexStanSettings::outputSettings();
