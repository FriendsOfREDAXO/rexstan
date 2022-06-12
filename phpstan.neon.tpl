includes:
    - %REXSTAN_USERCONFIG%

parameters:
    ### parameters we expect from user-config.neon
    # level: 5
    # paths:
    #    - ../mblock/

    phpVersion: 70300 # PHP 7.3
    treatPhpDocTypesAsCertain: false

    bootstrapFiles:
        - phpstan-bootstrap.php

    # https://phpstan.org/config-reference#universal-object-crates
    universalObjectCratesClasses:
        - rex_fragment
    ignoreErrors:
        - '#Variable \$this might not be defined.#'

services:
    -
        class: redaxo\phpstan\RexClassDynamicReturnTypeExtension
        tags:
            - phpstan.broker.dynamicStaticMethodReturnTypeExtension

    -
        class: redaxo\phpstan\RexFunctionsDynamicReturnTypeExtension
        tags:
            - phpstan.broker.dynamicFunctionReturnTypeExtension
