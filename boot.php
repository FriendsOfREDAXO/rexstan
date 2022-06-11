<?php

if (rex::isBackend() && is_object(rex::getUser())) {
    rex_view::addCssFile($this->getAssetsUrl('rexstan.css'));
}
