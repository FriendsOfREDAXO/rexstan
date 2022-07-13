<?php

use staabm\PHPStanDba\QueryReflection\PdoMysqlQueryReflector;
use staabm\PHPStanDba\QueryReflection\RuntimeConfiguration;
use staabm\PHPStanDba\QueryReflection\QueryReflection;

unset($REX);
$REX['REDAXO'] = false;

$REX['HTDOCS_PATH'] = realpath(__DIR__.'/../../../../');
$REX['BACKEND_FOLDER'] = 'redaxo';
$REX['LOAD_PAGE'] = false;

require __DIR__.'/../../core/boot.php';


// phpstan-dba bootstrapping

$configFile = rex_path::coreData('config.yml');
$config = rex_file::getConfig($configFile);

$db = ($config['db'][1] ?? []) + ['host' => '', 'login' => '', 'password' => '', 'name' => ''];

$pdo = new PDO(
    sprintf('mysql:dbname=%s;host=%s', $db['name'], $db['host']),
    $db['login'],
    $db['password'],
);

$config = new RuntimeConfiguration();
// $config->debugMode(true);
// $config->stringifyTypes(true);
// $config->analyzeQueryPlans(true);

QueryReflection::setupReflector(
    new PdoMysqlQueryReflector($pdo),
    $config
);
