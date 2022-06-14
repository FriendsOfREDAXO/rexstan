<?php


$form = rex_config_form::factory("rexstan");

$field = $form->addInputField('number', 'level', RexStanUserConfig::getLevel(), ["class" => "form-control","min" =>0, 'max'=>9]);
$field->setLabel('Level');
$field->setNotice('0 is the loosest and 9 is the strictest - <a href="https://phpstan.org/user-guide/rule-levels">see PHPStan Rule Levels</a>');

$field = $form->addSelectField('addons', RexStanUserConfig::getPaths(), ['class' => 'form-control selectpicker', 'data-live-search' => 'true']); // die Klasse selectpicker aktiviert den Selectpicker von Bootstrap
$field->setAttribute('multiple', 'multiple');
$field->setLabel("AddOns");
$field->setNotice('AddOns die untersucht werden sollen');
$select = $field->getSelect();

$available_addons = rex_addon::getAvailableAddons();
foreach ($available_addons as $available_addon) {
    $select->addOption($available_addon->getName(), $available_addon->getPath());
}

$field = $form->addSelectField('extensions', RexStanUserConfig::getIncludes(), ['class' => 'form-control selectpicker']);
$field->setAttribute('multiple', 'multiple');
$field->setLabel("PHPStan Extensions");
$select = $field->getSelect();

$select->addOption('Bleeding-Edge', realpath(__DIR__.'/../vendor/phpstan/phpstan/conf/bleedingEdge.neon'));
$select->addOption('Strict-Mode', realpath(__DIR__.'/../vendor/phpstan/phpstan-strict-rules/rules.neon'));
$select->addOption('Deprecation Warnings', realpath(__DIR__.'/../vendor/phpstan/phpstan-deprecation-rules/rules.neon'));

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', "Settings", false);
$fragment->setVar('body', $form->get(), false);
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
