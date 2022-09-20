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
            <td><?= $result['Overall-Errors'] ?></td>
        </tr>
        <tr>
            <td>Cognitive-Complexity</td>
            <td><?= $result['Classes-Cognitive-Complexity'] ?></td>
        </tr>
        <tr>
            <td>Deprecations</td>
            <td><?= $result['Deprecations'] ?></td>
        </tr>
        <tr>
            <td>Invalide PHPDocs</td>
            <td><?= $result['Invalid-Phpdocs'] ?></td>
        </tr>
        <tr>
            <td>Unknown-Types</td>
            <td><?= $result['Unknown-Types'] ?></td>
        </tr>
        <tr>
            <td>Anonymous-Variables</td>
            <td><?= $result['Anonymous-Variables'] ?></td>
        </tr>
    </tbody>
</table>
<p>Die Zusammenfassung ist abhängig von den in den <a href="<?= $settingsUrl ?>">Einstellungen</a> definierten PHPStan-Extensions</p>
