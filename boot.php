<?php

if (rex::isBackend() && is_object(rex::getUser()) && 'rexstan' === rex_be_controller::getCurrentPagePart(1) ) {
    rex_view::addCssFile($this->getAssetsUrl('rexstan.css'));
    rex_view::addJsFile($this->getAssetsUrl('confetti.min.js'));
}
