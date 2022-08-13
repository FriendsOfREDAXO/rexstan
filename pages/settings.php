<?php
/**
 * Konfigurationsseite des Addons / Settings.
 *
 * Das Formular wird mit der Form-Klasse RexStanSettings, basierend auf rex_config_form, aufsetzt.
 * Die Parametriesierung und der Formularaufbaue (Felder) ist komplett in RexStanSettings.php zu finden
*/

$form = redaxo\phpstan\RexStanSettings::factory('rexstan');
$faqUrl = rex_url::backendPage('rexstan/faq');

/**
 * Formularcode als Section via Fragment ausgeben.
 */

$fragment = new rex_fragment();
$fragment->setVar('options', '<a class="btn btn-info" href="'. rex_url::backendPage('rexstan/faq') .'">Weitere Informationen in den FAQ</a>', false);
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Settings', false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');
