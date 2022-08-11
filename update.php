<?php

// trigger the installation procedures also on updates
$addon = rex_addon::get('rexstan');
$addon->includeFile(__DIR__ . '/install.php');

// remove auto-generated file which used to be exist in previous versions
rex_file::delete(__DIR__.'/phpstan.neon');
