<?php

// @see https://github.com/shipmonk-rnd/composer-dependency-analyser/

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return new Configuration()
    ->ignoreErrorsOnExtension('ext-filter', [ErrorType::SHADOW_DEPENDENCY])
    // polyfill provides global functions (str_contains etc.), so no symbol usage is detected
    ->ignoreErrorsOnPackage('symfony/polyfill-php80', [ErrorType::UNUSED_DEPENDENCY]);
