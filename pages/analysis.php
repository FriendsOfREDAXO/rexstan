<?php

/** @var rex_addon $this */

$phpstanResult = RexStan::runFromWeb();

if (is_string($phpstanResult)) {
    echo rex_view::error(nl2br($phpstanResult));

    echo '<p>Die Web UI funktionert nicht auf allen Systemen, siehe README.</p>';

    return;
}

if (
    !is_array($phpstanResult)
    || !is_array($phpstanResult['files'])
    || (array_key_exists('errors', $phpstanResult) && count($phpstanResult['errors']) > 0)
) {
    // print general php errors, like out of memory...
    if (count($phpstanResult['errors']) > 0) {
        echo '<p>phpstan errors</p>';

        foreach($phpstanResult['errors'] as $error) {
            echo '<p>'. nl2br($error) .'</p>';
        }
    } else {
        echo '<p>No phpstan result</p>';
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
        }

        echo '<span class="rexstan-achievement">'.$emoji .'</span>';
        echo rex_view::success('Gratulation, es wurden keine Fehler in Level '. $level .' gefunden.');

        if (9 === $level) {
            echo '<script>
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
        } else {
            echo '<p>In den <a href="'. rex_url::backendPage('rexstan/settings') .'">Einstellungen</a>, solltest du jetzt das nächste Level anvisieren.</p>';
        }
        return;
    }

    echo '<p><strong>'. $totalErrors .'</strong> Probleme gefunden in <strong>'. count($phpstanResult['files']) .'</strong> Dateien</p>';

    echo '<table class="table table-hover">
               <thead>
                <tr>
                <th>Error</th>
                <th>Datei</th>
</tr>
</thead>   ';

    echo '<tbody>';
    $basePath = rex_path::src('addons/');

    foreach ($phpstanResult['files'] as $file => $fileResult) {
        $shortFile = str_replace($basePath, '', $file);

        echo '<tr class="rexstan-error-file">';
        echo '<td colspan="2"><span>'.rex_escape(dirname($shortFile)).DIRECTORY_SEPARATOR.'</span>'.rex_escape(basename($shortFile)).'</td>';
        echo '</tr>';

        foreach ($fileResult['messages'] as $message) {
            $error = rex_escape($message['message']);

            $url = rex_editor::factory()->getUrl($file, $message['line']);
            if ($url) {
                $error = '<a href="'. $url .'">'. rex_escape($message['message']) .'</a>';
            }

            echo '<tr class="rexstan-error-message">';
            echo '<td>'.$error.'</td>';
            echo '<td>'. rex_escape(basename($shortFile)).':'.$message['line'] .'</td>';
            echo '</tr>';
        }
    }
    echo '</tbody>';
    echo '</table>';
}
