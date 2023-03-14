<?php

namespace rexstan;

use PHPStan\ShouldNotHappenException;
use rex_editor;
use rex_fragment;
use rex_path;

final class RexResultsRenderer {
    static public function getLevel9Jseffect(): string {
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
    static public function renderFileBlock(string $file, array $messages): string
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
     * @param list<array{message: string, line: string, tip?: string}>  $messages
     */
    static private function renderFileErrors(string $file, array $messages): string {
        $content = '<ul class="list-group">';
        foreach ($messages as $message) {
            if (!is_numeric($message['line'])) {
                throw new ShouldNotHappenException();
            }
            $content .= '<li class="list-group-item rexstan-message">';
            if ($message['line'] < 0) {
                $content .= '<span class="rexstan-linenumber"></span>';
            } else {
                $content .= '<span class="rexstan-linenumber">' .sprintf('%5d', $message['line']).':</span>';
            }
            $error = rex_escape($message['message']);
            $url = rex_editor::factory()->getUrl($file, $message['line']);
            if ($url !== null) {
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

}
