<?php

unset($REX);
$REX['REDAXO'] = false;

// not sure yet, why running the command from PHPStorm needs a different HTDOCS_PATH
if (getenv('REXSTAN_PATHFIX')) {
    $REX['HTDOCS_PATH'] = '../';
} else {
    $REX['HTDOCS_PATH'] = './';
}

$REX['BACKEND_FOLDER'] = 'redaxo';
$REX['LOAD_PAGE'] = false;

require __DIR__.'/../../core/boot.php';
