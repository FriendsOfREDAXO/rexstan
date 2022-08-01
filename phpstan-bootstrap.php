<?php

use staabm\PHPStanDba\QueryReflection\PdoMysqlQueryReflector;
use staabm\PHPStanDba\QueryReflection\QueryReflection;
use staabm\PHPStanDba\QueryReflection\RuntimeConfiguration;

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

// on a git checkout require the root composer autoloader
// without also including static files (to prevent cannot re-declare function errors)
$basePath = rex_path::base();
if (is_file($basePath . '/vendor/autoload.php')) {
    call_user_func(function () use ($basePath) {
        $loader = new \Composer\Autoload\ClassLoader();

        foreach (require $basePath . '/vendor/composer/autoload_namespaces.php' as $namespace => $path) {
            $loader->set($namespace, $path);
        }

        foreach (require $basePath . '/vendor/composer/autoload_psr4.php' as $namespace => $path) {
            $loader->setPsr4($namespace, $path);
        }

        $classMap = require $basePath . '/vendor/composer/autoload_classmap.php';

        if ($classMap) {
            $loader->addClassMap($classMap);
        }

        $loader->register(true);
    });
}
