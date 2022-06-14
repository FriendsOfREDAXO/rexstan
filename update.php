<?php

// trigger the installation procedues also on updates
$addon = rex_addon::get('rexstan');
$addon->includeFile(__DIR__ . '/install.php');
