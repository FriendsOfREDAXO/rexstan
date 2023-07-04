<?php

use rexstan\RexStan;

$addon = rex_addon::get('rexstan');

if (rex::isBackend() && is_object(rex::getUser())) {
    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) use ($addon) {
        $svg = \rex_file::get($addon->getAssetsPath('rexstan-dino.min.svg'));

        $ep->setSubject(str_replace(
            '<i class="rexstan-navigation-icon"></i>',
            '<i class="rexstan-navigation-icon">'.$svg.'</i>',
            $ep->getSubject()
        ));
    });

    rex_view::addCssFile($addon->getAssetsUrl('rexstan.css'));
    if (rex_be_controller::getCurrentPagePart(1) === 'rexstan') {
        rex_view::addJsFile($addon->getAssetsUrl('confetti.min.js'));
    }
}

rex_extension::register('PACKAGE_CACHE_DELETED', static function (rex_extension_point $ep) {
    RexStan::clearResultCache();
});
