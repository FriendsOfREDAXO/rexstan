<?php

use rexstan\RexStan;
use staabm\PHPStanBaselineAnalysis\ResultPrinter;

$result = RexStan::analyzeSummaryBaseline();
$settingsUrl = rex_url::backendPage('rexstan/settings');

if ($result === null) {
    throw new Exception('Could not analyze baseline');
}

$contentErrors = '
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col" class="rex-table-width-6">Fehlerklasse</th>
                <th scope="col">Σ Anzahl</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th scope="row">Overall-Errors</th>
                <td data-title="Overall-Errors">'.$result[ResultPrinter::KEY_OVERALL_ERRORS].'</td>
            </tr>
            <tr>
                <th scope="row">Cognitive-Complexity</th>
                <td data-title="Cognitive-Complexity">'.$result[ResultPrinter::KEY_CLASSES_COMPLEXITY].'</td>
            </tr>
            <tr>
                <th scope="row">Deprecations</th>
                <td data-title="Deprecations">'.$result[ResultPrinter::KEY_DEPRECATIONS].'</td>
            </tr>
            <tr>
                <th scope="row">Unused Elements</th>
                <td data-title="Unused Elements">'.$result[ResultPrinter::KEY_UNUSED_SYMBOLS].'</td>
            </tr>
            <tr>
                <th scope="row">Invalide PHPDocs</th>
                <td data-title="Invalide PHPDocs">'.$result[ResultPrinter::KEY_INVALID_PHPDOCS].'</td>
            </tr>
            <tr>
                <th scope="row">Unknown-Types</th>
                <td data-title="Unknown-Types">'.$result[ResultPrinter::KEY_UNKNOWN_TYPES].'</td>
            </tr>
            <tr>
                <th scope="row">Anonymous-Variables</th>
                <td data-title="Anonymous-Variables">'.$result[ResultPrinter::KEY_ANONYMOUS_VARIABLES].'</td>
            </tr>
            <tr>
                <th scope="row">Unused-Symbols</th>
                <td data-title="Unused-Symbols">'.$result[ResultPrinter::KEY_UNUSED_SYMBOLS].'</td>
            </tr>
        </tbody>
    </table>';

$contentCoverage = '
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col" class="rex-table-width-6">Native Type Coverage</th>
                <th scope="col">Fortschritt</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th scope="row">Class Property Types</th>
                <td data-title="Class Property Types">'.$result[ResultPrinter::KEY_PROPERTY_TYPE_COVERAGE].' %</td>
            </tr>
            <tr>
                <th scope="row">Parameter Types</th>
                <td data-title="Parameter Types">'.$result[ResultPrinter::KEY_PARAM_TYPE_COVERAGE].' %</td>
            </tr>
            <tr>
                <th scope="row">Return Types</th>
                <td data-title="Return Types">'.$result[ResultPrinter::KEY_RETURN_TYPE_COVERAGE].' %</td>
            </tr>
        </tbody>
    </table>';

$rexstanGraph = '<div class="rexstan-graph">'.rex_file::get(rex_addon::get('rexstan')->getDataPath().'/baseline-graph.html').'</div>';

$nonce = method_exists(rex_response::class, 'getNonce') ? ' nonce="'.rex_response::getNonce().'"' : '';
$rexstanGraph = str_replace(
    '<script>',
    '<script'.$nonce.'>',
    $rexstanGraph
);

$fragment = new rex_fragment();
$fragment->setVar('title', 'Graph');
$fragment->setVar('content', $rexstanGraph, false);
echo $fragment->parse('core/page/section.php');

$echo = [];

$fragment = new rex_fragment();
$fragment->setVar('title', 'Fehler');
$fragment->setVar('content', $contentErrors, false);
$echo[] = $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('title', 'Coverage');
$fragment->setVar('content', $contentCoverage, false);
$echo[] = $fragment->parse('core/page/section.php');

$fragment = new rex_fragment();
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/grid.php');

echo rex_view::info('<p>Die Zusammenfassung ist abhängig von den in den <a href="'.$settingsUrl.'>">Einstellungen</a> definierten PHPStan-Extensions</p>');
