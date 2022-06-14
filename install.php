<?php

$addon = rex_addon::get('rexstan');

$userConfig = $addon->getDataPath('user-config.neon');

if (!is_file($userConfig)) {
    $paths = [];

    $projectAddon = rex_addon::get('project');
    if ($projectAddon->getPath()) {
        $paths[] = $projectAddon->getPath();
    } else {
        $availableAddons = rex_addon::getAvailableAddons();
        foreach ($availableAddons as $available_addon) {
            if ($availableAddons->isSystemPackage()) {
                continue;
            }

            if ($availableAddons->getName() == 'rexstan') {
                continue;
            }

            $paths[] = $availableAddons->getPath();
        }
    }

    RexStanUserConfig::save(0, $paths, []);
}

$template = rex_file::get(__DIR__.'/phpstan.neon.tpl');
$template = str_replace('%REXSTAN_USERCONFIG%', $userConfig, $template);
rex_file::put(__DIR__.'/phpstan.neon', $template);

// make sure the phpstan binary is executable
@chmod(__DIR__.'/vendor/bin/phpstan', 0775);
