<?php

$addon = rex_addon::get('rexstan');

if (isset($REX['PATH_PROVIDER'])) {
    $addon->setProperty('installmsg', 'Using rexstan in a setup with a custom PATH_PROVIDER is not supported.');
    return;
}

require_once __DIR__ .'/lib/RexStan.php';
$cliPhpVerssion = RexStan::execCmd('php -r "echo PHP_VERSION_ID;"', $lastError);
if (is_numeric($cliPhpVerssion)) {
    if ($cliPhpVerssion < 70300) {
        $addon->setProperty('installmsg', 'PHP CLI version is too old. Please upgrade to PHP 7.3 or higher.');
        return;
    }
} else {
    $addon->setProperty('installmsg', 'Unable to determine PHP CLI version. Make sure PHP is installed in the CLI and availabe within your system PATH.');
    return;
}

$userConfig = $addon->getDataPath('user-config.neon');
if (!is_file($userConfig)) {
    $paths = [];

    $projectAddon = rex_addon::get('project');
    if ($projectAddon->getPath()) {
        $paths[] = $projectAddon->getPath();
    } else {
        $availableAddons = rex_addon::getAvailableAddons();
        foreach ($availableAddons as $availableAddon) {
            if ($availableAddon->isSystemPackage()) {
                continue;
            }

            if ('rexstan' == $availableAddon->getName()) {
                continue;
            }

            $paths[] = $availableAddon->getPath();
        }
    }

    RexStanUserConfig::save(0, $paths, []);
}

$template = rex_file::get(__DIR__.'/phpstan.neon.tpl');
$template = str_replace('%REXSTAN_USERCONFIG%', $userConfig, $template);
rex_file::put(__DIR__.'/phpstan.neon', $template);

// make sure the phpstan binary is executable
@chmod(__DIR__.'/vendor/bin/phpstan', 0775);
