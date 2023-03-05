<?php declare(strict_types = 1);

use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    // is your PHP version different from the one you refactor to? [default: your PHP version], uses PHP_VERSION_ID format
    $rectorConfig->phpVersion(PhpVersion::PHP_74);

    // Path to PHPStan with extensions, that PHPStan in Rector uses to determine types
    //$rectorConfig->phpstanConfig(__DIR__ . '/build/PhpStan/phpstan.neon');

    // paths to refactor; solid alternative to CLI arguments
    $rectorConfig->paths([
        __DIR__ . '/sources',
        __DIR__ . '/tests',
    ]);

    // define, what sets of rules will be applied
    // tip: use "SetList" class to autocomplete sets
    //$rectorConfig->sets([
    //    SetList::CODE_QUALITY
    //]);

    // register single rule
    $rectorConfig->rule(TypedPropertyRector::class);
};
