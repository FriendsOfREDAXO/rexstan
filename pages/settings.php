<?php

use rexstan\RexStanSettings;
use rexstan\RexStanUserConfig;

$form = RexStanSettings::createForm();
$faqUrl = rex_url::backendPage('rexstan/faq');

$fragment = new rex_fragment();
$fragment->setVar('options', '<a class="btn btn-info" href="'. $faqUrl .'">Weitere Informationen in den FAQ</a>', false);
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Settings', false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

$form_name = $form->getName();
if (rex_post($form_name . '_save', 'bool', false)) {
    $postData = rex_post($form_name);
    $level = (int) $postData['level'];
    $addonPaths = $postData['addons'] ?? [];
    $extensions = $postData['extensions'] ?? [];
    $phpversion = (int) $postData['phpversion'];

    $baselineSettings = $postData['baseline'] ?? [];
    $analysisBaseline = isset($baselineSettings[RexStanSettings::BASELINE_ENABLED]);
    $reportUnmatchedIgnoredErrors = isset($baselineSettings[RexStanSettings::BASELINE_REPORT_UNMATCHED]);

    $paths = [];
    foreach ($addonPaths as $addonPath) {
        $paths[] = $addonPath;
    }

    $includes = [];
    foreach ($extensions as $extensionPath) {
        $includes[] = $extensionPath;
    }

    if ($analysisBaseline) {
        $includes[] = basename(RexStanSettings::getAnalysisBaselinePath());
    }

    RexStanUserConfig::save($level, $paths, $includes, $phpversion, $reportUnmatchedIgnoredErrors);
}

echo rex_view::info('This AddOn is created by Markus Staab in his free time. <a href="https://github.com/sponsors/staabm">Support rexstan with your sponsoring 💕</a>');
