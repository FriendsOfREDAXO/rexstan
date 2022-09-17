<?php

$result = RexStan::analyzeBaseline();

if ($result === null) {
    throw new \Exception('Could not analyze baseline');
}

echo '<pre>';
    echo 'Errors Insgesamt: '. $result['Overall-Errors'] ."\n";
    echo 'Cognitive-Complexity Insgesamt: '. $result['Classes-Cognitive-Complexity']."\n";
    echo 'Deprecations Insgesamt: '. $result['Deprecations']."\n";
    echo 'Invalide PHPDocs Insgesamt: '.$result['Invalid-Phpdocs']."\n";
    echo 'Unknown-Types Insgesamt: '. $result['Unknown-Types']."\n";
    echo 'Anonymous-Variables Insgesamt: ' .$result['Anonymous-Variables']."\n";
echo '</pre>';

echo '<p>Die Zusammenfassung ist abhängig von den in den Einstellungen definierten PHPStan-Extensions</p>';
