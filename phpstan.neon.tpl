# rexstan auto generated file - do not edit

includes:
    - %REXSTAN_USERCONFIG%

parameters:
    ### parameters we expect from user-config.neon
    # level: 5
    # paths:
    #    - ../mblock/

    excludePaths:
        - */vendor/*

    # don't report not found ignores
    reportUnmatchedIgnoredErrors: false

    ignoreErrors:
        # ignore errors when analyzing rex modules/templates, caused by rex-vars
        - '#Constant REX_[A-Z_]+ not found\.#'
        - '#^Variable \$this might not be defined\.#'

    # autoload core/core-addon symbols which are not autoloadable
    scanDirectories:
        - ../../core/functions/
        - ../structure/functions/
        - ../metainfo/functions/
        - ../mediapool/functions/

    # disabled for now, as we are running into function not found errors?
    # phpVersion: 70300 # PHP 7.3
    treatPhpDocTypesAsCertain: false

    bootstrapFiles:
        - phpstan-bootstrap.php

    # https://phpstan.org/config-reference#universal-object-crates
    universalObjectCratesClasses:
        - rex_fragment

services:
    -
        class: redaxo\phpstan\RexClassDynamicReturnTypeExtension
        tags:
            - phpstan.broker.dynamicStaticMethodReturnTypeExtension

    -
        class: redaxo\phpstan\RexFunctionsDynamicReturnTypeExtension
        tags:
            - phpstan.broker.dynamicFunctionReturnTypeExtension
