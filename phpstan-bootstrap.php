<?php

use staabm\PHPStanDba\QueryReflection\LazyQueryReflector;
use staabm\PHPStanDba\QueryReflection\PdoMysqlQueryReflector;
use staabm\PHPStanDba\QueryReflection\QueryReflection;
use staabm\PHPStanDba\QueryReflection\RuntimeConfiguration;

unset($REX);
$REX['REDAXO'] = true;

if (is_dir(__DIR__.'/../../../var/')) {
    // yakamara directoy layout
    $REX['HTDOCS_PATH'] = realpath(__DIR__.'/../../../');

    if (is_file(__DIR__ . '/../../../src/path_provider.php'))
    {
        require __DIR__ . '/../../../src/path_provider.php';
        $REX['PATH_PROVIDER'] = new app_path_provider();
    } elseif (is_file(__DIR__ . '/../../../src/AppPathProvider.php'))
    {
        require __DIR__ . '/../../../src/AppPathProvider.php';
        $REX['PATH_PROVIDER'] = new AppPathProvider();
    } else {
        throw new LogicException();
    }
} else {
    // default redaxo5 directory layout
    $REX['HTDOCS_PATH'] = realpath(__DIR__.'/../../../../');
}
$REX['BACKEND_FOLDER'] = 'redaxo';
$REX['LOAD_PAGE'] = false;

require __DIR__.'/../../core/boot.php';
// boot addons to make rexstan aware of e.g. class registrations at boot time
include_once rex_path::core('packages.php');

// phpstan-dba bootstrapping
$reflectorFactory = static function (): PdoMysqlQueryReflector {
    $configFile = rex_path::coreData('config.yml');
    $config = rex_file::getConfig($configFile);

    $db = ($config['db'][1] ?? []) + ['host' => '', 'login' => '', 'password' => '', 'name' => ''];

    $pdo = new PDO(
        sprintf('mysql:dbname=%s;host=%s', $db['name'], $db['host']),
        $db['login'],
        $db['password'],
    );

    return new PdoMysqlQueryReflector($pdo);
};

$config = new RuntimeConfiguration();
// $config->debugMode(true);
// $config->stringifyTypes(true);
// $config->analyzeQueryPlans(true);
$config->utilizeSqlAst(true);

QueryReflection::setupReflector(
    new LazyQueryReflector($reflectorFactory),
    $config
);

// on a git checkout require the root composer autoloader
// without also including static files (to prevent cannot re-declare function errors)
// to prevent errors like "Class XY extends unknown class PHPUnit\Framework\TestCase"
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
