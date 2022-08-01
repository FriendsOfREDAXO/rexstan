<?php

chdir(__DIR__.'/../');

@shell_exec('php vendor/bin/phpstan clear-result-cache > /dev/null 2>&1');

$output = trim((string) shell_exec('php vendor/bin/phpstan analyze tests/* --error-format=raw'));
$output = preg_replace('/\s*$/', '', $output);
$expected = trim((string) file_get_contents(__DIR__.'/expected.out'));

if ($output != $expected) {
    echo "ERROR, output does not match\n\n";

    echo "OUTPUT:\n";
    var_dump($output);

    echo "EXPECTED:\n";
    var_dump($expected);

    exit(1);
}

echo "all good\n";
exit(0);
