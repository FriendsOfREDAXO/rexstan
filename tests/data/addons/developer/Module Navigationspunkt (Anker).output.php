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

<?php foreach (explode(',', 'REX_LINKLIST[id=1]') as $article_id): ?>
    <a href="<?=rex_getUrl($article_id);?>">zum Artikel mit der ID <?=$article_id;?></a>
<?php endforeach;?>
<?php foreach (explode(',', 'REX_LINKLIST[id=10]') as $article_id): ?>
    <a href="<?=rex_getUrl($article_id);?>">zum Artikel mit der ID <?=$article_id;?></a>
<?php endforeach;?>

REX_CLANG_ID
REX_CTYPE_ID

<?php
    echo '
<video class="bgvid" preload="auto" loop="loop" autoplay="false" muted="muted" volume="0">
    <source src="'.rex_url::base('media/REX_MEDIA[1]').'" type="video/mp4">Video not supported</video>';
?>
<?php
echo '
<video class="bgvid" preload="auto" loop="loop" autoplay="false" muted="muted" volume="0">
    <source src="'.rex_url::base('media/REX_MEDIA[2]').'" type="video/mp4">Video not supported</video>';

$cols = "REX_VALUE[1]";
if ($cols == "") {
    echo $cols;
}
if ("" == $cols) {
    echo $cols;
}

if ($cols === "") {
    echo $cols;
}
if ("" === $cols) {
    echo $cols;
}
