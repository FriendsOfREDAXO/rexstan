<?php

$result = RexStan::analyzeBaseline();
$settingsUrl = rex_url::backendPage('rexstan/settings');

if (null === $result) {
    throw new \Exception('Could not analyze baseline');
}

?>
<table class="table table-striped table-hover" style="width:auto;">
    <thead>
        <tr class="info">
            <th>Fehlerklasse</th>
            <th>Σ Anzahl</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Errors</td>
            <td><?= $result[ResultPrinter::KEY_OVERALL_ERRORS] ?></td>
        </tr>
        <tr>
            <td>Cognitive-Complexity</td>
            <td><?= $result[ResultPrinter::KEY_CLASSES_COMPLEXITY] ?></td>
        </tr>
        <tr>
            <td>Deprecations</td>
            <td><?= $result[ResultPrinter::KEY_DEPRECATIONS] ?></td>
        </tr>
        <tr>
            <td>Invalide PHPDocs</td>
            <td><?= $result[ResultPrinter::KEY_INVALID_PHPDOCS] ?></td>
        </tr>
        <tr>
            <td>Unknown-Types</td>
            <td><?= $result[ResultPrinter::KEY_UNKNOWN_TYPES] ?></td>
        </tr>
        <tr>
            <td>Anonymous-Variables</td>
            <td><?= $result[ResultPrinter::KEY_ANONYMOUS_VARIABLES] ?></td>
        </tr>
    </tbody>
</table>
<p>Die Zusammenfassung ist abhängig von den in den <a href="<?= $settingsUrl ?>">Einstellungen</a> definierten PHPStan-Extensions</p>
