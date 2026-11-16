<?php

/** @var rex_addon $this */

use FriendsOfRedaxo\RexStan\RexResultsRenderer;
use FriendsOfRedaxo\RexStan\RexStan;
use FriendsOfRedaxo\RexStan\RexStanRunStore;

$regenerateBaseline = rex_get('regenerate-baseline', 'bool', false);
if ($regenerateBaseline) {
    RexStan::generateAnalysisBaseline();
    // the cached result predates the baseline change and would otherwise keep
    // showing now-ignored errors until the next manually triggered run
    RexStanRunStore::clearCachedResult();
}

rex_view::addJsFile($this->getAssetsUrl('rexstan-analysis.js'));

$isRunning = RexStanRunStore::isRunning();
$cachedResult = $isRunning ? null : RexStanRunStore::readCachedResult();

if ($isRunning) {
    $initialHtml = '';
} elseif (null !== $cachedResult) {
    $initialHtml = RexResultsRenderer::renderAnalysisBody($cachedResult, $regenerateBaseline);
} else {
    $initialHtml = '';
}

$jsConfig = json_encode([
    'apiBase' => 'index.php?rex-api-call=rexstan_analysis',
    'running' => $isRunning,
    'hasResult' => null !== $cachedResult,
]);

echo '<div id="rexstan-analysis-config" data-config="'. rex_escape($jsConfig) .'" hidden></div>';
echo '<div id="rexstan-analysis-app">';
echo '<div id="rexstan-analysis-toolbar"></div>';
echo '<div id="rexstan-analysis-result">'. $initialHtml .'</div>';
echo '</div>';
