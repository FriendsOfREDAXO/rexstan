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
        -
            message: '#Constant REX_[A-Z_]+ not found\.#'
            path: *data/addons/developer/*
        -
            message: '#.* will always evaluate to (true|false).#'
            path: *data/addons/developer/*
        -
            message: '#.* is always (true|false).#'
            path: *data/addons/developer/*
        -
            message: '#^Variable \$this might not be defined\.#'
            path: *data/addons/developer/*
        -
            message: '#^Variable \$this might not be defined\.#'
            path: */fragments/*

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

    -
        class: redaxo\phpstan\RexSqlDynamicReturnTypeExtension
        tags:
            - phpstan.broker.dynamicMethodReturnTypeExtension

    # redaxos setQuery etc. APIs support both, prepared and unprepared statements..
    # therefore we need to register them twice, to also cover cases which don't pass parameters.
    -
        class: staabm\PHPStanDba\Rules\SyntaxErrorInPreparedStatementMethodRule
        tags: [phpstan.rules.rule]
        arguments:
            classMethods:
                - 'rex_sql::setQuery'
                - 'rex_sql::setDBQuery'
                - 'rex_sql::getArray'
                - 'rex_sql::getDBArray'

    -
        class: staabm\PHPStanDba\Rules\SyntaxErrorInQueryMethodRule
        tags: [phpstan.rules.rule]
        arguments:
            classMethods:
                - 'rex_sql::setQuery#0'
                - 'rex_sql::setDBQuery#0'
                - 'rex_sql::getArray#0'
                - 'rex_sql::getDBArray#0'
                - 'rex_sql::prepareQuery#0'
                - 'rex_sql::getQueryType#0'
