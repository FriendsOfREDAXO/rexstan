<?php

use rexstan\RexStan;

$addon = rex_addon::get('rexstan');

if (rex::isBackend() && is_object(rex::getUser()) && 'rexstan' === rex_be_controller::getCurrentPagePart(1)) {
    rex_view::addCssFile($addon->getAssetsUrl('rexstan.css'));
    rex_view::addJsFile($addon->getAssetsUrl('confetti.min.js'));
}

rex_extension::register('PACKAGE_CACHE_DELETED', static function (rex_extension_point $ep) {
    RexStan::clearResultCache();
});
