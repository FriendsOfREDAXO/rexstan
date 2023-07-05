<?php

namespace rexstan;

use rex_editor;
use rex_fragment;
use rex_path;

use function array_key_exists;
use function count;
use function dirname;

final class RexResultsRenderer
{
    public static function getResultEmoji(int $level): string
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
        }
        return $emoji;
    }

    public static function getLevel9Jseffect(): string
    {
        $nonce = method_exists('\rex_response', 'getNonce') ? ' nonce="'.\rex_response::getNonce().'"' : '';
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
     * @param list<array{message: string, line: int, tip?: string}>  $messages
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
     * @param list<array{message: string, line: int, tip?: string, identifier?: string}>  $messages
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
     * @param array{message: string, line: int, tip?: string, identifier?: string}  $message
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
