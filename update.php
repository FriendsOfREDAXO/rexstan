<?php

// trigger the installation procedures also on updates
$addon = rex_addon::get('rexstan');
$addon->includeFile(__DIR__ . '/install.php');
