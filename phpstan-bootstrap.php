<?php

unset($REX);
$REX['REDAXO'] = false;

// not sure yet, why running the command from the WEB UI needs a different HTDOCS_PATH
if (getenv('REXSTAN_WEBUI')) {
    $REX['HTDOCS_PATH'] = '../';
} else {
    $REX['HTDOCS_PATH'] = './';
}

$REX['BACKEND_FOLDER'] = 'redaxo';
$REX['LOAD_PAGE'] = false;

require __DIR__.'/../../core/boot.php';
