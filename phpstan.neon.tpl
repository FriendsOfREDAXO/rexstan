# Todo: make config configurable

includes:
    - vendor/phpstan/phpstan-strict-rules/rules.neon
    - vendor/phpstan/phpstan-deprecation-rules/rules.neon
    - vendor/phpstan/phpstan-symfony/extension.neon
    - vendor/phpstan/phpstan-symfony/rules.neon
    - %REXSTAN_USERCONFIG%

parameters:
    ### parameters we expect from user-config.neon
    # level: 5
    # paths:
    #    - ../mblock/

    phpVersion: 70300 # PHP 7.3
    treatPhpDocTypesAsCertain: false
    bootstrapFiles:
        - ../../../../vendor/autoload.php
    scanDirectories:
        # make sure phpstan knows all core/core-addons classes
        - ../../core/
        - ../backup/
        - ../be_style/
        - ../cronjob/
        - ../debug/
        - ../install/
        - ../media_manager/
        - ../mediapool/
        - ../metainfo/
        - ../phpmailer/
        - ../structure/
        - ../users/
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
