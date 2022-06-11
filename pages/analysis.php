<?php

/** @var rex_addon $this */

$cmd = 'php '.__DIR__.'/../vendor/bin/phpstan analyse -c '.__DIR__.'/../phpstan.neon.dist --error-format=json';

$json = shell_exec($cmd);
$phpstanResult = json_decode($json, true);

if (!is_array($phpstanResult) || !is_array($phpstanResult['files'])) {
    echo 'No phpstan result';
} else {
    echo '<table class="table table-hover">
               <thead>
                <tr>
                <th class="rex-table-icon"></th>
                <th>Error</th>
                <th>Datei</th>
</tr>
</thead>   ';

    echo '<tbody>';
    $basePath = rex_path::src('addons/');

    foreach ($phpstanResult['files'] as $file => $fileResult) {
        $shortFile = str_replace($basePath, '', $file);

        echo '<tr class="rexstan-error-file">';
        echo '<td></td>';
        echo '<td colspan="2"><span>'.rex_escape(dirname($shortFile)).DIRECTORY_SEPARATOR.'</span>'.rex_escape(basename($shortFile)).'</td>';
        echo '</tr>';

        foreach ($fileResult['messages'] as $message) {
            $error = rex_escape($message['message']);

            $url = rex_editor::factory()->getUrl($file, $message['line']);
            if ($url) {
                $error = '<a href="'. $url .'">'. rex_escape($error) .'</a>';
            }



            echo '<tr class="rexstan-error">';
            echo '<td></td>';
            echo '<td>'.$error.'</td>';
            echo '<td>'. rex_escape(basename($shortFile)).':'.$message['line'] .'</td>';
            echo '</tr>';
        }
    }
    echo '</tbody>';
    echo '</table>';
}
