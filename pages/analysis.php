<?php

/** @var rex_addon $this */

$phpstanResult = RexStan::runFromWeb();

if (is_string($phpstanResult)) {
    echo rex_view::error(nl2br($phpstanResult));

    echo '<p>Die Web UI erfordert funktionert nicht auf allen Systemen, siehe README.</p>';

    return;
}

if (!is_array($phpstanResult) || !is_array($phpstanResult['files'])) {
    echo '<p>No phpstan result</p>';
} else {
    $totalErrors = $phpstanResult['totals']['file_errors'];

    if ($totalErrors === 0) {
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
            case 9:
                $emoji = '🥇';
            break;
        }

        echo '<span class="rexstan-achievement">'.$emoji .'</span>';
        echo rex_view::success('Gratulation, es wurden keine Fehler gefunden.');

        if ($level !== 9) {
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
