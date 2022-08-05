<?php

$form = rex_config_form::factory('rexstan');

$field = $form->addInputField('number', 'level', RexStanUserConfig::getLevel(), ['class' => 'form-control', 'min' => 0, 'max' => 9]);
$field->setLabel('Level');
$field->setNotice('0 is the loosest and 9 is the strictest - <a href="https://phpstan.org/user-guide/rule-levels">see PHPStan Rule Levels</a>');

$field = $form->addSelectField('addons', RexStanUserConfig::getPaths(), ['class' => 'form-control selectpicker', 'data-live-search' => 'true']); // die Klasse selectpicker aktiviert den Selectpicker von Bootstrap
$field->setAttribute('multiple', 'multiple');
$field->setLabel('AddOns');
$field->setNotice('AddOns die untersucht werden sollen');
$select = $field->getSelect();

foreach (rex_addon::getAvailableAddons() as $availableAddon) {
    $select->addOption($availableAddon->getName(), $availableAddon->getPath());

    if ('developer' === $availableAddon->getName() && class_exists(rex_developer_manager::class)) {
        $select->addOption('developer: modules', rex_developer_manager::getBasePath() .'/modules/');
        $select->addOption('developer: templates', rex_developer_manager::getBasePath() .'/templates/');
    }
}

$field = $form->addSelectField('extensions', RexStanUserConfig::getIncludes(), ['class' => 'form-control selectpicker']);
$field->setAttribute('multiple', 'multiple');
$field->setLabel('PHPStan Extensions');
$field->setNotice('Weiterlesen bzgl. der verf&uuml;gbaren Extensions: <a href="https://phpstan.org/blog/what-is-bleeding-edge">Bleeding-Edge</a>, <a href="https://github.com/phpstan/phpstan-strict-rules#readme">Strict-Mode</a>, <a href="https://github.com/phpstan/phpstan-deprecation-rules#readme">Deprecation-Warnings</a>, <a href="https://github.com/phpstan/phpstan-phpunit#readme">PHPUnit</a>, <a href="https://staabm.github.io/archive.html#phpstan-dba">phpstan-dba</a>');
$select = $field->getSelect();

$select->addOption('Bleeding-Edge', realpath(__DIR__.'/../vendor/phpstan/phpstan/conf/bleedingEdge.neon'));
$select->addOption('Strict-Mode', realpath(__DIR__.'/../vendor/phpstan/phpstan-strict-rules/rules.neon'));
$select->addOption('Deprecation Warnings', realpath(__DIR__.'/../vendor/phpstan/phpstan-deprecation-rules/rules.neon'));
$select->addOption('PHPUnit', realpath(__DIR__.'/../vendor/phpstan/phpstan-phpunit/rules.neon'));
$select->addOption('phpstan-dba', realpath(__DIR__.'/../lib/phpstan-dba.neon'));

$cliMemLimit = RexStan::getCliMemoryLimit();
$footer = '';
if (($cliMemLimit / 1024 / 1024) < 256) {
    $footer = '
        <i>
            Das <code>memory_limit</code> in der PHP CLI ist mit '. round($cliMemLimit / 1024 / 1024) .' MB ggf. zu niedrig.<br />
            Dies kann dazu führen, dass die Analyse nicht beendet werden kann.<br />
            Bitte setzen Sie das <code>memory_limit</code> in der PHP CLI auf mindestens 256 MB - je nach Projektgr&ouml;ße ggf. auch mehr.<br /><br />
            '. nl2br(RexStan::execCmd('php --ini', $lastError)) .'
        </i>
    ';
}

$fragment = new rex_fragment();
$fragment->setVar('heading', '<i>Einstellungen werden <a href="'. rex_url::backendPage('rexstan/faq') .'">im FAQ erklärt</a></i>', false);
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Settings', false);
$fragment->setVar('body', $form->get(), false);
$fragment->setVar('footer', $footer, false);
echo $fragment->parse('core/page/section.php');

$form_name = $form->getName();
if (rex_post($form_name . '_save')) {
    $postData = rex_post($form_name);
    $addonPaths = $postData['addons'] ?? [];
    $extensions = $postData['extensions'] ?? [];

    $paths = [];
    foreach ($addonPaths as $addonPath) {
        $paths[] = $addonPath;
    }

    $includes = [];
    foreach ($extensions as $extensionPath) {
        $includes[] = $extensionPath;
    }

    RexStanUserConfig::save((int) $postData['level'], $paths, $includes);
}
