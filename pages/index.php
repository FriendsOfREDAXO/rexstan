<?php

/** @var rex_addon $this */

$logoPath = $this->getAssetsUrl('rexstan.svg');

// in the future this should instead use the native redaxo core loader api
// https://github.com/redaxo/redaxo/pull/5664

echo '
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
      document.querySelectorAll(".use-loader").forEach(function(el) {
        el.addEventListener("click", function () {
            document.querySelector("#rex-js-ajax-loader").classList.add("rex-visible");
        });
      });
    }, false);
</script>
';

echo rex_view::title('<span class="rexstan-logo"><img src="'.$logoPath.'" width="200" height="90" ></span>');

rex_be_controller::includeCurrentPageSubPath();
