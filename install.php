<?php

$userConfig = $this->getDataPath('user-config.neon');

if (!is_file($userConfig)) {
    rex_file::put($userConfig, '');
}

$template = rex_file::get(__DIR__.'/phpstan.neon.tpl');
$template = str_replace('%REXSTAN_USERCONFIG%', $userConfig, $template);
rex_file::put(__DIR__.'/phpstan.neon', $template);
