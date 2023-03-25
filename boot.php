<?php

use rexstan\RexStan;

$addon = rex_addon::get('rexstan');

if (rex::isBackend() && is_object(rex::getUser())) {
    rex_extension::register('OUTPUT_FILTER', function(\rex_extension_point $ep) use ($addon) {
        $svg = \rex_file::get($addon->getAssetsPath('rexstan_dino.svg'));

        $ep->setSubject(str_replace(
            '<i class="rexstan-navigation-icon"></i>',
            '<span class="rexstan-navigation-icon" style="display: inline-block; width: 20px; height: 20px; margin-left: -28px; margin-right: 3px; vertical-align: top;">'.$svg.'</span>',
            $ep->getSubject()
        ));
    });

    rex_view::addCssFile($addon->getAssetsUrl('rexstan.css'));
    if ('rexstan' === rex_be_controller::getCurrentPagePart(1)) {
        rex_view::addJsFile($addon->getAssetsUrl('confetti.min.js'));
    }
}

rex_extension::register('PACKAGE_CACHE_DELETED', static function (rex_extension_point $ep) {
    RexStan::clearResultCache();
});
