<?php

$userConfig = $this->getDataPath('user-config.neon');

if (!is_file($userConfig)) {
    $paths = [];

    $available_addons = rex_addon::getAvailableAddons();
    foreach ($available_addons as $available_addon) {
        if ($available_addon->isSystemPackage()) {
            continue;
        }

        if ($available_addon->getName() == 'rexstan') {
            continue;
        }

        $paths[] = $available_addon->getPath();
    }

    RexStanUserConfig::save(5, $paths);
}

$template = rex_file::get(__DIR__.'/phpstan.neon.tpl');
$template = str_replace('%REXSTAN_USERCONFIG%', $userConfig, $template);
rex_file::put(__DIR__.'/phpstan.neon', $template);
