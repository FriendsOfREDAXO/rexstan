<?php

namespace FriendsOfRedaxo\RexStan;

use rex_editor;
use rex_fragment;
use rex_path;
use rex_response;
use rex_url;
use rex_view;

use function array_key_exists;
use function count;
use function dirname;

/**
 * @phpstan-type PhpstanMessage array{message: string, line: int, tip?: string, identifier?: string}
 * @phpstan-type PhpstanFileResult array{messages: list<PhpstanMessage>}
 *
 * "files"/"errors" are intentionally left as mixed here rather than fully
 * typed: they come from PHPStan's own external --error-format=json output,
 * which is not a versioned public contract, so the is_array() checks against
 * them in renderAnalysisBody() are a real defensive guard against a future
 * PHPStan version changing that shape - not dead code.
 *
 * @phpstan-type PhpstanRunResult array{totals: array{file_errors: int}, files: mixed, errors?: mixed}
 */
final class RexResultsRenderer
{
    /**
     * Renders the full analysis result body - the same markup pages/analysis.php
     * used to produce synchronously, now as a reusable string so it can also be
     * returned by Api\AnalysisApi once a background run (see
     * RexStan::startBackgroundWebAnalysis()) has finished, for the polling JS to
     * inject into the page without a full reload.
     *
     * @param PhpstanRunResult|string $phpstanResult
     */
    public static function renderAnalysisBody($phpstanResult, bool $regenerateBaseline = false): string
    {
        if (is_string($phpstanResult)) {
            return self::renderStringError($phpstanResult);
        }

        $hasPhpstanErrors =
            array_key_exists('errors', $phpstanResult)
            && is_array($phpstanResult['errors'])
            && $phpstanResult['errors'] !== []
        ;

        if (!is_array($phpstanResult['files']) || $hasPhpstanErrors) {
            return self::renderRuntimeError($hasPhpstanErrors ? $phpstanResult['errors'] : null);
        }

        /** @var array<string, PhpstanFileResult> $files */
        $files = $phpstanResult['files'];
        $totalErrors = $phpstanResult['totals']['file_errors'];

        [$baselineButton, $baselineInfo, $baselineCount] = self::buildBaselineHints($regenerateBaseline);

        if (0 === $totalErrors) {
            return self::renderCongratulations($baselineCount);
        }

        return self::renderFileList($files, $totalErrors, $regenerateBaseline, $baselineButton, $baselineInfo);
    }

    private static function renderStringError(string $phpstanResult): string
    {
        ob_start();

        $settingsUrl = rex_url::backendPage('rexstan/settings');

        // we moved settings files into config/.
        if (stripos($phpstanResult, "neon' is missing or is not readable.") !== false) {
            echo rex_view::warning(
                "Das Einstellungsformat hat sich geändert. Bitte die <a href='".$settingsUrl."'>Einstellungen öffnen</a> und erneut abspeichern. <br/><br/>".nl2br(
                    $phpstanResult
                )
            );
        } elseif (stripos($phpstanResult, 'polyfill-php8') !== false && stripos($phpstanResult, 'does not exist') !== false) {
            echo rex_view::warning(
                'Der REDAXO Core wurde aktualisiert. Bitte das rexstan AddOn re-installieren. <br/><br/>'.nl2br($phpstanResult)
            );
        } else {
            echo rex_view::error(
                '<h4>PHPSTAN: Fehler</h4>'
                .nl2br($phpstanResult)
            );
        }

        echo rex_view::info('Die Web UI funktionert nicht auf allen Systemen, siehe README.');

        return (string) ob_get_clean();
    }

    /**
     * @param mixed $errors null if this is a generic "no result at all" case
     *                      rather than PHPStan reporting its own runtime errors
     */
    private static function renderRuntimeError($errors): string
    {
        ob_start();

        // print general php errors, like out of memory...
        if (null !== $errors && is_array($errors)) {
            $msg = '<h4>PHPSTAN: Laufzeit-Fehler</h4><ul>';
            foreach ($errors as $error) {
                if (!is_string($error)) {
                    continue;
                }
                $msg .= '<li>'.nl2br($error).'<br /></li>';
            }
            $msg .= '</li>';
            echo rex_view::error($msg);
        } else {
            echo rex_view::warning('No phpstan result');
        }

        return (string) ob_get_clean();
    }

    /**
     * @return array{0: string, 1: string, 2: int} [baselineButton, baselineInfo, baselineCount]
     */
    private static function buildBaselineHints(bool $regenerateBaseline): array
    {
        $baselineButton = '';
        $baselineInfo = '';
        $baselineCount = 0;

        if (RexStanUserConfig::isBaselineEnabled()) {
            if (!$regenerateBaseline) {
                $baselineButton = ' <a href="'. rex_url::backendPage('rexstan/analysis', ['regenerate-baseline' => 1]) .'" class="btn btn-danger">Alle Probleme ignorieren</a>';
            }

            $baselineCount = RexStan::getBaselineErrorsCount();
            if ($baselineCount > 0) {
                $baselineInfo = '<br/><i>'. $baselineCount .' Probleme wurden mittels Baseline ignoriert</i>';
            }
        }

        return [$baselineButton, $baselineInfo, $baselineCount];
    }

    private static function renderCongratulations(int $baselineCount): string
    {
        ob_start();

        $level = RexStanUserConfig::getLevel();
        $emoji = self::getResultEmoji($level);

        echo '<span class="rexstan-achievement">'.$emoji .'</span>';
        echo rex_view::success('Gratulation, es wurden keine Fehler in Level '. $level .' gefunden.');

        if (10 === $level) {
            echo self::getLevel10Jseffect();
        } else {
            echo '<p>';

            echo 'In den <a href="'. rex_url::backendPage('rexstan/settings') .'">Einstellungen</a>, solltest du jetzt das nächste Level anvisieren.';
            if (RexStanUserConfig::isBaselineEnabled() && $baselineCount > 0) {
                $baselineFile = RexStanSettings::getAnalysisBaselinePath();
                $url = rex_editor::factory()->getUrl($baselineFile, 0);

                $baselineHint = 'Baseline '. $baselineCount .' Probleme ignoriert werden';
                if ($url !== null) {
                    $baselineHint = '<a href="'. $url .'">'. $baselineHint .'</a>';
                }

                echo '<br />Da mittels '. $baselineHint .', solltest Du alternativ versuchen diese zu reduzieren.';
            }

            echo '</p>';
        }
        echo RexStanSettings::outputSettings();

        return (string) ob_get_clean();
    }

    /**
     * @param array<string, PhpstanFileResult> $files
     */
    private static function renderFileList(array $files, int $totalErrors, bool $regenerateBaseline, string $baselineButton, string $baselineInfo): string
    {
        ob_start();

        if ($regenerateBaseline && $totalErrors > 0) {
            echo rex_view::error('Nicht alle Fehler konnten ignoriert werden. <b>Empfehlung:</b> Die verbliebenen kritischen Fehler analysieren und beheben.');
        }

        echo rex_view::warning(
            'Level-<strong>'.RexStanUserConfig::getLevel().'</strong>-Analyse: <strong>'. $totalErrors .'</strong> Probleme gefunden in <strong>'. count($files) .'</strong> Dateien.'. $baselineButton. $baselineInfo
        );

        echo RexStanCategorizer::renderSummaryTable($files);

        foreach ($files as $file => $fileResult) {
            $linkFile = preg_replace('/\s\(in context.*?$/', '', $file);
            if (null === $linkFile) {
                throw new \PHPStan\ShouldNotHappenException();
            }

            echo self::renderFileBlock($linkFile, $fileResult['messages']);
        }

        return (string) ob_get_clean();
    }

    private static function getResultEmoji(int $level): string
    {
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
            case 10:
                $emoji = '👑';
                break;
        }
        return $emoji;
    }

    private static function getLevel10Jseffect(): string
    {
        $nonce = ' nonce="'.rex_response::getNonce().'"';
        return
            '<script'.$nonce.'>
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
            </script>';
    }

    /**
     * @param list<PhpstanMessage> $messages
     */
    public static function renderFileBlock(string $file, array $messages): string
    {
        $basePath = rex_path::src('addons/');

        $content = self::renderFileErrors($file, $messages);

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
     * @param list<PhpstanMessage> $messages
     */
    private static function renderFileErrors(string $file, array $messages): string
    {
        $content = '<ul class="list-group">';
        foreach ($messages as $message) {
            $content .= '<li class="list-group-item rexstan-message">';
            if ($message['line'] <= 0) {
                $content .= '<span class="rexstan-linenumber"></span>';
            } else {
                $content .= '<span class="rexstan-linenumber">' .sprintf('%5d', $message['line']).':</span>';
            }

            $content .= self::renderErrorMessage($file, $message);
            $content .= '</li>';
        }
        $content .= '</ul>';

        return $content;
    }

    /**
     * @param PhpstanMessage $message
     */
    private static function renderErrorMessage(string $file, array $message): string
    {
        $error = rex_escape($message['message']);
        if (self::isUnmatchedBaselineError($message['message'])) {
            $baselineFile = RexStanSettings::getAnalysisBaselinePath();
            $url = rex_editor::factory()->getUrl($baselineFile, 0);

            if ($url !== null) {
                $error = '<a href="'. $url .'">Baseline:</a> '. rex_escape($message['message']);
            }
        } else {
            $url = rex_editor::factory()->getUrl($file, $message['line']);
            if ($url !== null) {
                $error = '<a href="'. $url .'">'. rex_escape($message['message']) .'</a>';
            }
        }

        if (array_key_exists('identifier', $message)) {
            $error .= '<br /><span title="error identifier"> 🏷️ '. rex_escape($message['identifier']) .'</span>';
        }

        $phpstanTip = null;
        if (array_key_exists('tip', $message)) {
            $phpstanTip = $message['tip'];
        }

        $rexstanTip = RexStanTip::renderTip($message['message'], $phpstanTip);
        if ($rexstanTip !== null) {
            $error .= '<br /><span class="rexstan-tip" title="Tipp">💡 '. $rexstanTip .'</span>';
        }
        return $error;
    }

    private static function isUnmatchedBaselineError(string $message): bool
    {
        return str_contains($message, 'was not matched in reported errors.');
    }
}
