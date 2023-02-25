<?php

/** @var rex_addon $this */

use rexstan\RexLint;
use rexstan\RexResultsRenderer;

$errors = RexLint::runFromWeb();
if (count($errors) > 0) {
    foreach ($errors as $file => $errors) {
        echo RexResultsRenderer::renderFileBlock($file, $errors);
    }

    return;
}

echo rex_view::success('Gratulation, es wurden keine Lint Fehler gefunden.');
