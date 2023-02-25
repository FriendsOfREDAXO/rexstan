<?php

/** @var rex_addon $this */

use rexstan\RexStan;
use rexstan\RexStanTip;
use rexstan\RexStanUserConfig;

if (rex_get('regenerate-baseline', 'bool')) {
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

    if (0 === $totalErrors) {
        $level = RexStanUserConfig::getLevel();

        $emoji = '';
        switch ($level) {
            case 0:
                $emoji = '❤️️';
                break;
            case 1:
                $emoji = '✌️';
                break;
            case 2:
                $emoji = '💪';
                break;
            case 3:
                $emoji = '🧙';
                break;
            case 4:
                $emoji = '🏎️';
                break;
            case 5:
                $emoji = '🚀';
                break;
            case 6:
                $emoji = '🥉';
                break;
            case 7:
                $emoji = '🥈';
                break;
            case 8:
                $emoji = '🥇';
                break;
            case 9:
                $emoji = '🏆';
                break;
        }

        echo '<span class="rexstan-achievement">'.$emoji .'</span>';
        echo rex_view::success('Gratulation, es wurden keine Fehler in Level '. $level .' gefunden.');

        if (9 === $level) {
            echo rexstan_level9_jseffect();
        } else {
            echo '<p>In den <a href="'. rex_url::backendPage('rexstan/settings') .'">Einstellungen</a>, solltest du jetzt das nächste Level anvisieren.</p>';
        }
        return;
    }

    $baselineButton = '';
    if (RexStanUserConfig::isBaselineEnabled()) {
        $baselineButton .= ' <a href="'. rex_url::backendPage('rexstan/analysis', ['regenerate-baseline' => 1]) .'" class="btn btn-danger">Alle Probleme ignorieren</a>';
    }

    echo rex_view::warning('Level-<strong>'.RexStanUserConfig::getLevel().'</strong>-Analyse: <strong>'. $totalErrors .'</strong> Probleme gefunden in <strong>'. count($phpstanResult['files']) .'</strong> Dateien.'. $baselineButton);

    foreach ($phpstanResult['files'] as $file => $fileResult) {
        $linkFile = preg_replace('/\s\(in context.*?$/', '', $file);

        echo rexstan_renderFileBlock($linkFile, $fileResult['messages']);
    }
}

function rexstan_level9_jseffect() {
    return
        '<script>
        var duration = 10 * 1000;
        var animationEnd = Date.now() + duration;
        var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

        function randomInRange(min, max) {
          return Math.random() * (max - min) + min;
        }

        var interval = setInterval(function() {
          var timeLeft = animationEnd - Date.now();

          if (timeLeft <= 0) {
            return clearInterval(interval);
          }

          var particleCount = 50 * (timeLeft / duration);
          // since particles fall down, start a bit higher than random
          confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
          confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
        }, 250);
        </script>
    ';
}

/**
 * @param list<array{message: string, line: string, tip?: string}>  $messages
 */
function rexstan_renderFileBlock(string $file, array $messages): string
{
    $basePath = rex_path::src('addons/');

    $content = rexstan_renderFileErrors($file, $messages);

    $shortFile = str_replace($basePath, '', $file);
    $title = '<i class="rexstan-open fa fa-folder-o"></i>'.
        '<i class="rexstan-closed fa fa-folder-open-o"></i> '.
        '<span class="text-muted">'.rex_escape(dirname($shortFile)).DIRECTORY_SEPARATOR.'</span>'
        .rex_escape(basename($shortFile)).
        ' <span class="badge">'.count($messages).'</span>';

    $section = new rex_fragment();
    $section->setVar('sectionAttributes', ['class' => 'rexstan'], false);
    $section->setVar('title', $title, false);
    $section->setVar('collapse', true);
    $section->setVar('content', $content, false);
    return $section->parse('core/page/section.php');
}

/**
 * @param list<array{message: string, line: string, tip?: string}>  $messages
 */
function rexstan_renderFileErrors(string $file, array $messages): string {
    $content = '<ul class="list-group">';
    foreach ($messages as $message) {
        $content .= '<li class="list-group-item rexstan-message">';
        $content .= '<span class="rexstan-linenumber">' .sprintf('%5d', $message['line']).':</span>';
        $error = rex_escape($message['message']);
        $url = rex_editor::factory()->getUrl($file, $message['line']);
        if ($url) {
            $error = '<a href="'. $url .'">'. rex_escape($message['message']) .'</a>';
        }

        $phpstanTip = null;
        if (array_key_exists('tip', $message)) {
            $phpstanTip = $message['tip'];
        }

        $rexstanTip = RexStanTip::renderTip($message['message'], $phpstanTip);
        if (null !== $rexstanTip) {
            $error .= '<br /><span class="rexstan-tip" title="Tipp">💡 '. $rexstanTip .'</span>';
        }

        $content .= $error;
        $content .= '</li>';
    }
    $content .= '</ul>';

    return $content;
}
