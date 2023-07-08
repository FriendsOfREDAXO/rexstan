<?php

/** @var rex_addon $this */

$logoPath = $this->getAssetsUrl('rexstan.svg');

// in the future this should instead use the native redaxo core loader api
// https://github.com/redaxo/redaxo/pull/5664

$nonce = method_exists('rex_response', 'getNonce') ? ' nonce="'.rex_response::getNonce().'"' : '';
echo '
<script type="text/javascript"'.$nonce.'>
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

if ('settings' !== rex_be_controller::getCurrentPagePart(2)) {
    $url = '<small><a href="https://github.com/sponsors/staabm">Support <strong>rexstan</strong> with your sponsoring 💕</a></small>';
    $scripttime = rex::getProperty('timer')->getFormattedDelta(rex_timer::SEC);

    echo '<div class="text-center"><p>';
    echo $url . ' - <small>' . rex_i18n::msg('footer_scripttime', $scripttime) . '</small>';
    echo ' <small><a href="#top"><i class="rex-icon rex-icon-top"></i></a></small>';
    echo '</p></div>';
}
