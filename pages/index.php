<?php

/** @var rex_addon $this */

$logoPath = $this->getAssetsUrl('rexstan.svg');

echo rex_view::title('<span class="rexstan-logo"><img src="'.$logoPath.'" width="200" height="90" ></span>');

rex_be_controller::includeCurrentPageSubPath();
