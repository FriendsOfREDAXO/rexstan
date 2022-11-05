<?php

chdir(__DIR__.'/../');

@shell_exec('php vendor/bin/phpstan clear-result-cache');

$output = trim((string) shell_exec('php vendor/bin/phpstan analyze tests/ --error-format=raw'));
$output = preg_replace('/\s*$/', '', $output);
$expected = trim((string) file_get_contents(__DIR__.'/expected.out'));

function relativePath(string $path): string
{
    $projectRoot = realpath(__DIR__.'/../');

    $projectRoot = str_replace('\\', '/', $projectRoot);
    $path = str_replace('\\', '/', $path);

    return str_replace($projectRoot, '', $path);
}

$output = relativePath($output);
$expected = relativePath($expected);

if ($output != $expected) {
    echo "ERROR, output does not match\n\n";

    /*
    echo "OUTPUT:\n";
    var_dump($output);

    echo "EXPECTED:\n";
    var_dump($expected);
    */

    $out = tempnam('/tmp', 'rexstan_');
    $exp = tempnam('/tmp', 'rexstan_');

    file_put_contents($out, $output);
    file_put_contents($exp, $expected);

    try {
        passthru('git diff --color '.$exp.' '.$out);
    } finally {
        @unlink($out);
        @unlink($exp);
    }

    exit(1);
}

echo "all good\n";
exit(0);
