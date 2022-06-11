<?php


$form = rex_config_form::factory("rexstan");

$field = $form->addInputField('number', 'level', 5, ["class" => "form-control","min" =>0, 'max'=>9]);
$field->setLabel('Level');

$field = $form->addSelectField('addons', $value = null, ['class' => 'form-control selectpicker', 'data-live-search' => 'true']); // die Klasse selectpicker aktiviert den Selectpicker von Bootstrap
$field->setAttribute('multiple', 'multiple');
$field->setLabel("Addons");
$select = $field->getSelect();

$available_addons = rex_addon::getAvailableAddons();
foreach ($available_addons as $available_addon) {
    $select->addOption($available_addon->getName(), $available_addon->getName());
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', "Settings", false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

$form_name = $form->getName();
if (rex_post($form_name . '_save')) {
    $post_data = rex_post($form_name);

    $paths = [];
    foreach ($post_data['addons'] as $addon_file) {
        $addon = rex_addon::get($addon_file);
        $paths[] = $addon->getPath();
    }

    RexStanUserConfig::save((int) $post_data['level'], $paths);
}
