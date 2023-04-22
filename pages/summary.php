<?php

use rexstan\RexStan;
use rexstan\RexStanSettings;
use staabm\PHPStanBaselineAnalysis\ResultPrinter;

$result = RexStan::analyzeSummaryBaseline();
$settingsUrl = rex_url::backendPage('rexstan/settings');

if ($result === null) {
    throw new \Exception('Could not analyze baseline');
}

?>
<div style="width: 80%; background-color: rgb(32, 43, 53)">
    <?php echo rex_file::get(rex_addon::get('rexstan')->getDataPath().'/baseline-graph.html'); ?>
</div>
<table class="table table-striped table-hover" style="width: auto;">
    <tbody>
        <tr>
            <th class="info" style="width: 200px">Fehlerklasse</th>
            <td>Overall-Errors</td>
            <td>Cognitive-Complexity</td>
            <td>Deprecations</td>
            <td>Invalide PHPDocs</td>
            <td>Unknown-Types</td>
            <td>Anonymous-Variables</td>
        </tr>
        <tr>
            <th class="info">Σ Anzahl</th>
            <td><?= $result[ResultPrinter::KEY_OVERALL_ERRORS] ?></td>
            <td><?= $result[ResultPrinter::KEY_CLASSES_COMPLEXITY] ?></td>
            <td><?= $result[ResultPrinter::KEY_DEPRECATIONS] ?></td>
            <td><?= $result[ResultPrinter::KEY_INVALID_PHPDOCS] ?></td>
            <td><?= $result[ResultPrinter::KEY_UNKNOWN_TYPES] ?></td>
            <td><?= $result[ResultPrinter::KEY_ANONYMOUS_VARIABLES] ?></td>
        </tr>
    </tbody>
</table>

<table class="table table-striped table-hover" style="width: auto;">
    <tbody>
    <tr>
        <th class="info" style="width: 200px">Native Type Coverage</th>
        <td>Class Property Types</td>
        <td>Parameter Types</td>
        <td>Return Types</td>
    </tr>
    <tr>
        <th class="info">Fortschritt</th>
        <td><?= $result[ResultPrinter::KEY_PROPERTY_TYPE_COVERAGE] ?> %</td>
        <td><?= $result[ResultPrinter::KEY_PARAM_TYPE_COVERAGE] ?> %</td>
        <td><?= $result[ResultPrinter::KEY_RETURN_TYPE_COVERAGE] ?> %</td>
    </tr>
    </tbody>
</table>

<p>Die Zusammenfassung ist abhängig von den in den <a href="<?= $settingsUrl ?>">Einstellungen</a> definierten PHPStan-Extensions</p>

<?= RexStanSettings::outputSettings() ?>
