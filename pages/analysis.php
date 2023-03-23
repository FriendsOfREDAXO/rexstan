<?php

/** @var rex_addon $this */

use rexstan\RexResultsRenderer;
use rexstan\RexStan;
use rexstan\RexStanTip;
use rexstan\RexStanSettings;
use rexstan\RexStanUserConfig;

$regenerateBaseline = rex_get('regenerate-baseline', 'bool', false);
if ($regenerateBaseline) {
    RexStan::generateAnalysisBaseline();
}

$phpstanResult = RexStan::runFromWeb();
$settingsUrl = rex_url::backendPage('rexstan/settings');

if (is_string($phpstanResult)) {
    // we moved settings files into config/.
    if (false !== stripos($phpstanResult, "neon' is missing or is not readable.")) {
        echo rex_view::warning(
            "Das Einstellungsformat hat sich geändert. Bitte die <a href='".$settingsUrl."'>Einstellungen öffnen</a> und erneut abspeichern. <br/><br/>".nl2br(
                $phpstanResult
            )
        );
    } elseif (false !== stripos($phpstanResult, "polyfill-php8") && false !== stripos($phpstanResult, "does not exist")) {
        echo rex_view::warning(
            "Der REDAXO Core wurde aktualisiert. Bitte das rexstan AddOn re-installieren. <br/><br/>".nl2br($phpstanResult)
        );
    } else {
        echo rex_view::error(
            '<h4>PHPSTAN: Fehler</h4>'
            .nl2br($phpstanResult)
        );
    }

    echo rex_view::info('Die Web UI funktionert nicht auf allen Systemen, siehe README.');

    return;
}

if (
    !is_array($phpstanResult)
    || !is_array($phpstanResult['files'])
    || (array_key_exists('errors', $phpstanResult) && count($phpstanResult['errors']) > 0)
) {
    // print general php errors, like out of memory...
    if (count($phpstanResult['errors']) > 0) {
        $msg = '<h4>PHPSTAN: Laufzeit-Fehler</h4><ul>';
        foreach ($phpstanResult['errors'] as $error) {
            $msg .= '<li>'.nl2br($error).'<br /></li>';
        }
        $msg .= '</li>';
        echo rex_view::error($msg);
    } else {
        echo rex_view::warning('No phpstan result');
    }
} else {
    $totalErrors = $phpstanResult['totals']['file_errors'];

    $baselineButton = '';
    $baselineInfo = '';
    if (RexStanUserConfig::isBaselineEnabled()) {
        if (!$regenerateBaseline) {
            $baselineButton .= ' <a href="'. rex_url::backendPage('rexstan/analysis', ['regenerate-baseline' => 1]) .'" class="btn btn-danger">Alle Probleme ignorieren</a>';
        }

        $baselineCount = RexStan::getBaselineErrorsCount();
        if ($baselineCount > 0) {
            $baselineInfo = '<br/><i>'. $baselineCount .' Probleme wurden mittels Baseline ignoriert</i>';
        }
    }

    if (0 === $totalErrors) {
        $level = RexStanUserConfig::getLevel();
        $emoji = RexResultsRenderer::getResultEmoji($level);

        echo '<span class="rexstan-achievement">'.$emoji .'</span>';
        echo rex_view::success('Gratulation, es wurden keine Fehler in Level '. $level .' gefunden.'. $baselineInfo);

        if (9 === $level) {
            echo RexResultsRenderer::getLevel9Jseffect();
        } else {
            echo '<p>In den <a href="'. rex_url::backendPage('rexstan/settings') .'">Einstellungen</a>, solltest du jetzt das nächste Level anvisieren.</p>';
        }
        echo RexStanSettings::outputSettings();
        return;
    }

    if ($regenerateBaseline && $totalErrors > 0) {
        echo rex_view::error('Nicht alle Fehler konnten ignoriert werden. <b>Empfehlung:</b> Die verbliebenen kritischen Fehler analysieren und beheben.');
    }

    echo rex_view::warning(
        'Level-<strong>'.RexStanUserConfig::getLevel().'</strong>-Analyse: <strong>'. $totalErrors .'</strong> Probleme gefunden in <strong>'. count($phpstanResult['files']) .'</strong> Dateien.'. $baselineButton. $baselineInfo
    );

    foreach ($phpstanResult['files'] as $file => $fileResult) {
        $linkFile = preg_replace('/\s\(in context.*?$/', '', $file);
        if ($linkFile === null) {
            throw new \PHPStan\ShouldNotHappenException();
        }

        echo RexResultsRenderer::renderFileBlock($linkFile, $fileResult['messages']);
    }
}

