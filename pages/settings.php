<?php

$form = redaxo\phpstan\RexStanSettings::createForm();
$faqUrl = rex_url::backendPage('rexstan/faq');

$fragment = new rex_fragment();
$fragment->setVar('options', '<a class="btn btn-info" href="'. $faqUrl .'">Weitere Informationen in den FAQ</a>', false);
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Settings', false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

redaxo\phpstan\RexStanSettings::save($form->getName());
