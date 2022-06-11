<?php

/** @var rex_addon $this */

$phpstanResult = RexStan::runFromWeb();

if (is_string($phpstanResult)) {
    echo '<span class="rexstan-error">'.nl2br(rex_escape($phpstanResult)).'</span>';
    return;
}

if (!is_array($phpstanResult) || !is_array($phpstanResult['files'])) {
    echo 'No phpstan result';
} else {
    echo '<p><strong>'.$phpstanResult['totals']['file_errors'] .'</strong> Probleme gefunden in <strong>'. count($phpstanResult['files']) .'</strong> Dateien</p>';

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
