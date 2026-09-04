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

// Plain-link fallback for when JS didn't load (e.g. after forgetting
// assets:sync, or a blocked script) - reloads the page, which then shows the
// "running" state below just like the AJAX-triggered flow does. JS below
// intercepts this same link to avoid the page reload when it did load.
$forceRerun = rex_get('rerun', 'bool', false);
if ($forceRerun) {
    RexStan::startBackgroundWebAnalysis();
}

// JS is registered in boot.php (gated to this subpage), not here - see the
// comment there for why (matches this addon's own confetti.min.js pattern).

$isRunning = RexStanRunStore::isRunning();
$cachedResult = $isRunning ? null : RexStanRunStore::readCachedResult();
$resultTimestamp = $isRunning ? null : RexStanRunStore::getCachedResultTimestamp();

if ($isRunning) {
    $initialHtml = '';
} elseif (null !== $cachedResult) {
    $initialHtml = RexResultsRenderer::renderAnalysisBody($cachedResult, $regenerateBaseline);
} else {
    $initialHtml = '';
}

$rerunUrl = rex_url::backendPage('rexstan/analysis', ['rerun' => 1]);

$toolbarHtml = '';
if (!$isRunning) {
    $label = (null !== $cachedResult) ? '🔄 Neu analysieren' : '▶️ Analyse starten';
    // rex_url::backendPage() already returns a pre-escaped URL (escape=true default) -
    // wrapping it in rex_escape() again would double-escape the "&" into "&amp;amp;"
    $toolbarHtml = '<p><a href="'. $rerunUrl .'" id="rexstan-analysis-trigger" class="btn btn-primary">'. $label .'</a></p>';
}

$metaHtml = '';
if (null !== $resultTimestamp) {
    $metaHtml = '<p class="text-muted" id="rexstan-analysis-meta">Ergebnis vom '. rex_escape(date('d.m.Y H:i', $resultTimestamp)) .' Uhr</p>';
} elseif ($isRunning) {
    $metaHtml = '<p class="text-muted" id="rexstan-analysis-meta"></p>';
}

$jsConfig = json_encode([
    'apiBase' => 'index.php?rex-api-call=rexstan_analysis',
    'running' => $isRunning,
    'hasResult' => null !== $cachedResult,
]);

echo '<div id="rexstan-analysis-config" data-config="'. rex_escape($jsConfig) .'" hidden></div>';
echo '<div id="rexstan-analysis-app">';
echo '<div id="rexstan-analysis-toolbar">'. $toolbarHtml .'</div>';
echo '<div id="rexstan-analysis-meta-container">'. $metaHtml .'</div>';
echo '<div id="rexstan-analysis-result">'. $initialHtml .'</div>';
echo '</div>';
