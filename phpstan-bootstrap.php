<?php

use staabm\PHPStanDba\QueryReflection\RuntimeConfiguration;
use staabm\PHPStanDba\QueryReflection\MysqliQueryReflector;
use staabm\PHPStanDba\QueryReflection\QueryReflection;

unset($REX);
$REX['REDAXO'] = false;

// not sure yet, why running the command from PHPStorm needs a different HTDOCS_PATH
if (getenv('REXSTAN_PATHFIX')) {
    $REX['HTDOCS_PATH'] = '../';
} else {
    $REX['HTDOCS_PATH'] = './';
}

$REX['BACKEND_FOLDER'] = 'redaxo';
$REX['LOAD_PAGE'] = false;

require __DIR__.'/../../core/boot.php';


// phpstan-dba bootstrapping

$configFile = rex_path::coreData('config.yml');
$config = rex_file::getConfig($configFile);

$db = ($config['db'][1] ?? []) + ['host' => '', 'login' => '', 'password' => '', 'name' => ''];

$mysqli = new mysqli(
    $db['host'],
    $db['login'],
    $db['password'],
    $db['name']
);

$config = new RuntimeConfiguration();
// $config->debugMode(true);
// $config->stringifyTypes(true);
// $config->analyzeQueryPlans(true);

QueryReflection::setupReflector(
    new MysqliQueryReflector($mysqli),
    $config
);
