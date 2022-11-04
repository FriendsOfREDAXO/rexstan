<?php
    echo '
	<div id="REX_VALUE[2]"></div>';

    if (!isset($counter)) {
        $counter = 0;
    }
    ++$counter;

    // Daten in ein Array schreiben, nur für das Frontend
    if (!rex::isBackend()) {
        $items = [];
        $items = ['anchor' => 'REX_VALUE[2]', 'title' => 'REX_VALUE[1]'];
        rex::getProperty('anchors', new ArrayIterator())->append($items);
    // Im Backend wird der Inhalt als Info für den Redakteur angezeigt
    } else {
        echo '<h1 style="background: #000; padding: 20px 40px; color: #fff;">REX_VALUE[1]</h1>';
    }
?>
REX_VALUE[10]
REX_LINK[1]
